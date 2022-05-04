#include <Arduino.h>
#ifdef EXTERNAL_RTC
#include "RTClib.h"
#else
#include "I2C_BM8563.h"
#endif

#include <WiFi.h>
#include <HTTPClient.h>
#include <AsyncTCP.h>
#include <ESPAsyncWebServer.h>
#include <AsyncElegantOTA.h>

#include "Config.h"
#include "PinDefination.h"
#include "StateDefination.h"
#include "Dispenser.hpp"
#include "Schedule.hpp"
#include "Api.hpp"
#include "ApiHttpResponse.hpp"

// wifi stuff
const char *ssid = WIFI_SSID;
const char *password = WIFI_PASSWORD;
unsigned long wifiCheckpreviousMillis = 0;
unsigned long wifiCheckinterval = 30000;
AsyncWebServer server(80);
bool isWifiReconnecting = false;
bool isWifiConnected = false;
int wifiConnectTimeout = 60000;

// rtc stuff
#ifdef EXTERNAL_RTC
RTC_DS3231 rtc;
#else
I2C_BM8563 rtc(I2C_BM8563_DEFAULT_ADDRESS, Wire1);
#endif

const char *ntpServer = "time.cloudflare.com";
long gmtOffset_sec = 8 * 3600;
int daylightOffset_sec = 0;

// main loop timer
int halfSecondDelay = 500;
unsigned long timeNow = 0;
unsigned long halfSecondDelaypreviousMillis = 0;

// feeder stuff
Dispenser *dispenser;
Schedule *schedule;
Api *api;
bool manualDispense = false;
int COVER_OPEN_LED_STATE = LED_OFF;
int WIFI_DEVICE_READY_LED_STATE = LED_ON;
bool identify = false;
int identifyBlinkCount = 5;
bool isCoverOpen = false;
bool coverOpenAnnounced = false;

// food low stuff
hw_timer_t * timer = NULL;
bool isFoodLow = false;
unsigned long foodLowThresholdDuration = 1000*30;
unsigned long foodLowLastDetected = 0;
bool isFoodReallyLow = false;
volatile bool isFoodIROn = 0;
portMUX_TYPE mux = portMUX_INITIALIZER_UNLOCKED;
bool canCheckFoodLevel = false;

void syncRTCDateTime();
void setupPins();
void setupTimer();
void checkFoodLevel();
void startServer();

void setup()
{
  // setup all the input and output pins
  setupPins();  
  Serial.begin(9600);
  delay(10);

  schedule = new Schedule;

  WiFi.begin(ssid, password);
  Serial.print("Connecting to WiFi..");
  unsigned long start = millis();
  while (WiFi.status() != WL_CONNECTED && (millis() - start) < wifiConnectTimeout)
  {
    digitalWrite(PIN_OUTPUT_LED_WIFI_DEVICE_READY, LED_ON);
    Serial.print(".");
    delay(300);
    digitalWrite(PIN_OUTPUT_LED_WIFI_DEVICE_READY, LED_OFF);
    delay(300);
  }

  if(WiFi.status() == WL_CONNECTED) {
    isWifiConnected = true;
    Serial.println("\nConnected to the WiFi network");
    Serial.print("Device IP: ");
    Serial.println(WiFi.localIP());
    digitalWrite(PIN_OUTPUT_LED_WIFI_DEVICE_READY, LED_ON);
    // Set ntp time to local
    configTime(gmtOffset_sec, daylightOffset_sec, ntpServer);
  } else {
    Serial.println("Wifi connect timeout");
    isWifiReconnecting = true;
    isWifiConnected = false;
  }

  // Init RTC

  #ifdef EXTERNAL_RTC
  if (! rtc.begin()) {
    while (1) delay(10);
  }
  #else
  Wire1.begin(BM8563_I2C_SDA, BM8563_I2C_SCL);
  rtc.begin();
  #endif
  syncRTCDateTime();

  dispenser = new Dispenser;
  dispenser->ResetFeederPosition(true);
  dispenser->ResetDoorPosition();
  api = new Api;

  if(isWifiConnected) {
    api->AnnounceBoot();
    ApiHttpResponse response = api->GetSchedule();
    if(response.success()) {
      schedule->ParseRawScheduleAndInsert(response.getBody());
    }
  } else {
    Serial.println("Wifi not connected, wont be updating schedule");
  }

  server.on("^/schedule$", HTTP_POST, [](AsyncWebServerRequest *request){
    char * message = const_cast<char*>(request->getParam("slots")->value().c_str());
    schedule->ParseRawScheduleAndInsert(message);
    request->send(200, "application/json", "{\"success\": true}");
  });

  server.on("^/dispense$", HTTP_POST, [](AsyncWebServerRequest *request){
    manualDispense = true;
    request->send(200, "application/json", "{\"success\": true}");
  });

  server.on("^/identify$", HTTP_POST, [](AsyncWebServerRequest *request){
    identify = true;
    request->send(200, "application/json", "{\"success\": true}");
  });

  server.on("^/time$", HTTP_GET, [](AsyncWebServerRequest *request){
    uint8_t second = 0;
    uint8_t minute = 0;
    uint8_t hour = 0;
    #ifdef EXTERNAL_RTC
    DateTime now = rtc.now();
    minute = now.minute();
    hour = now.hour();
    second = now.second();
    #else
    I2C_BM8563_TimeTypeDef timeStruct;
    rtc.getTime(&timeStruct);
    minute = timeStruct.minutes;
    hour = timeStruct.hours;
    second = timeStruct.seconds;
    #endif
    request->send(200, "text/plain", String(hour)+":"+String(minute)+":"+String(second));
    
  });

  server.onNotFound([](AsyncWebServerRequest *request){ 
    request->send(404, "text/plain", "Not found");
  });

  AsyncElegantOTA.begin(&server);
  
  if(isWifiConnected) {
    startServer();
  }

  setupTimer();
}


void loop()
{
  timeNow = millis();
  if ((timeNow - halfSecondDelaypreviousMillis >= halfSecondDelay))
  {
    uint8_t second = 0;
    uint8_t minute = 0;
    uint8_t hour = 0;

    #ifdef EXTERNAL_RTC
    DateTime now = rtc.now();
    minute = now.minute();
    hour = now.hour();
    second = now.second();
    #else
    I2C_BM8563_TimeTypeDef timeStruct;
    rtc.getTime(&timeStruct);
    minute = timeStruct.minutes;
    hour = timeStruct.hours;
    second = timeStruct.seconds;
    #endif

  // Serial.printf("%02d:%02d:%02d\n",
  //               hour,
  //               minute,
  //               second
  //              );
    if (second == 0)
    {
      if(schedule->canDispense(minute, hour)) {
        uint8_t unit = schedule->getDispenseUnit(minute, hour);
        dispenser->Dispense(unit);
        api->AnnounceDispense();
      } else {
        if(hour % 4 == 0 && minute == 5) {
          // sync clock every 4 hour
          syncRTCDateTime();
        }
      }
    } else {
      // manual dispense
      if(manualDispense) {
        dispenser->Dispense(1);
        manualDispense =  false;
        api->AnnounceManualDispense();
      }

      if (second > 1 && second < 55) {
        canCheckFoodLevel = true;
      } else {
        canCheckFoodLevel = false;
      }
    }

    isCoverOpen = digitalRead(PIN_INPUT_COVER_STATE) == COVER_OPEN;    
    if(!isCoverOpen && isFoodLow) {
      digitalWrite(PIN_OUTPUT_LED_LOW_LEVEL, LED_ON); 
    } else {
      digitalWrite(PIN_OUTPUT_LED_LOW_LEVEL, LED_OFF); 
    }

    // led state for cover open
    if(isCoverOpen) {
      if(COVER_OPEN_LED_STATE == LED_OFF) {
        COVER_OPEN_LED_STATE = LED_ON;
      } else {
        COVER_OPEN_LED_STATE = LED_OFF;
      }
    } else {
      COVER_OPEN_LED_STATE = LED_OFF;
    }

    // led state for wifi reconnecting
    if(isWifiReconnecting) {
      if(WIFI_DEVICE_READY_LED_STATE == LED_OFF) {
        WIFI_DEVICE_READY_LED_STATE = LED_ON; 
      } else {
        WIFI_DEVICE_READY_LED_STATE = LED_OFF; 
      }
    } else {
      WIFI_DEVICE_READY_LED_STATE = LED_ON; 
    }

    // identify device
    if(identify) {
      int currentBlinkCount = 0;
      while (currentBlinkCount < identifyBlinkCount) {
        digitalWrite(PIN_OUTPUT_LED_WIFI_DEVICE_READY, LED_ON);
        digitalWrite(PIN_OUTPUT_LED_COVER_OPEN, LED_ON);
        digitalWrite(PIN_OUTPUT_LED_DISPENSING, LED_ON);
        digitalWrite(PIN_OUTPUT_LED_LOW_LEVEL, LED_ON);
        delay(300);
        digitalWrite(PIN_OUTPUT_LED_WIFI_DEVICE_READY, LED_OFF);
        digitalWrite(PIN_OUTPUT_LED_COVER_OPEN, LED_OFF);
        digitalWrite(PIN_OUTPUT_LED_DISPENSING, LED_OFF);
        digitalWrite(PIN_OUTPUT_LED_LOW_LEVEL, LED_OFF);
        delay(300);
        currentBlinkCount = currentBlinkCount + 1;
      }
      identify =  false;
    }

    digitalWrite(PIN_OUTPUT_LED_COVER_OPEN, COVER_OPEN_LED_STATE); 
    digitalWrite(PIN_OUTPUT_LED_WIFI_DEVICE_READY, WIFI_DEVICE_READY_LED_STATE);

    halfSecondDelaypreviousMillis = timeNow;

  } else {
    if((timeNow - wifiCheckpreviousMillis >= wifiCheckinterval)) {
      if (WiFi.status() != WL_CONNECTED) {
        WiFi.disconnect();
        WiFi.reconnect();
        isWifiReconnecting = true;
      } else {
        WIFI_DEVICE_READY_LED_STATE = LED_ON;
        if(isWifiReconnecting) {
          startServer();
          isWifiReconnecting = false;
          api->AnnounceReconnect();
        }
      }
      wifiCheckpreviousMillis = timeNow;
    }
   
  }

  if(!manualDispense) {
    manualDispense = digitalRead(PIN_INPUT_MANUAL_FEEDER) == 0;
  }

   checkFoodLevel();
}

void syncRTCDateTime()
{
  struct tm timeInfo;
  if (getLocalTime(&timeInfo))
  {
    Serial.println("Sync clock");
    #ifdef EXTERNAL_RTC
      rtc.adjust(DateTime(
          timeInfo.tm_year + 1900, 
          timeInfo.tm_mon + 1, 
          timeInfo.tm_mday, 
          timeInfo.tm_hour, 
          timeInfo.tm_min, 
          timeInfo.tm_sec
        )
      );
    #else
    // Set RTC time
    I2C_BM8563_TimeTypeDef timeStruct;
    timeStruct.hours = timeInfo.tm_hour;
    timeStruct.minutes = timeInfo.tm_min;
    timeStruct.seconds = timeInfo.tm_sec;
    rtc.setTime(&timeStruct);
    // Set RTC Date
    I2C_BM8563_DateTypeDef dateStruct;
    dateStruct.weekDay = timeInfo.tm_wday;
    dateStruct.month = timeInfo.tm_mon + 1;
    dateStruct.date = timeInfo.tm_mday;
    dateStruct.year = timeInfo.tm_year + 1900;
    rtc.setDate(&dateStruct);
    #endif
  } else {
     Serial.println("Not syncing clock");
  }
}

void startServer() {
  server.end();
  server.begin();
}

void setupPins()
{
  pinMode(PIN_OUTPUT_HALL_SENSOR, OUTPUT);
  pinMode(PIN_INPUT_HALL_SENSOR_DOOR, INPUT);
  pinMode(PIN_INPUT_HALL_SENSOR_FEED, INPUT);

  pinMode(PIN_OUTPUT_DOOR_OPEN, OUTPUT);
  pinMode(PIN_OUTPUT_DOOR_CLOSE, OUTPUT);

  pinMode(PIN_OUTPUT_FEED_OPEN, OUTPUT);

  pinMode(PIN_OUTPUT_COVER_IR, OUTPUT);
  pinMode(PIN_INPUT_COVER_STATE, INPUT_PULLUP);
  pinMode(PIN_INPUT_FOOD_LEVEL, INPUT);

  pinMode(PIN_INPUT_MANUAL_FEEDER, INPUT);

  pinMode(PIN_OUTPUT_LED_WIFI_DEVICE_READY, OUTPUT);
  pinMode(PIN_OUTPUT_LED_DISPENSING, OUTPUT);
  pinMode(PIN_OUTPUT_LED_COVER_OPEN, OUTPUT);
  pinMode(PIN_OUTPUT_LED_LOW_LEVEL, OUTPUT);
  pinMode(PIN_OUTPUT_LED_WIFI_DEVICE_READY, OUTPUT);

  digitalWrite(PIN_OUTPUT_COVER_IR, LED_ON);

  // seems leds are inverted, HIGH = off, LOW = on
  digitalWrite(PIN_OUTPUT_LED_WIFI_DEVICE_READY, LED_OFF);
  digitalWrite(PIN_OUTPUT_LED_DISPENSING, LED_OFF);
  digitalWrite(PIN_OUTPUT_LED_COVER_OPEN, LED_OFF);
  digitalWrite(PIN_OUTPUT_LED_LOW_LEVEL, LED_OFF);

}

void checkFoodLevel() {
  if(isFoodReallyLow && !isCoverOpen) {
    digitalWrite(PIN_OUTPUT_LED_LOW_LEVEL, LED_ON);
  } else {
    digitalWrite(PIN_OUTPUT_LED_LOW_LEVEL, LED_OFF);
  }
  if(isFoodIROn && canCheckFoodLevel) {
    unsigned long now = millis();
    long lowFoodScanTime = (millis() + 8);
    // Serial.println("---------------------------------------------------");
    while (lowFoodScanTime > millis()) {
      isFoodLow =  digitalRead(PIN_INPUT_FOOD_LEVEL) == LOW;
      // Serial.print("isFoodLow -> ");Serial.println(isFoodLow);
      if(isFoodLow) {
        isFoodReallyLow = true;
        foodLowLastDetected = millis();
      } else {
        if((foodLowLastDetected + foodLowThresholdDuration < millis())) {
          isFoodReallyLow = false;
        }
      }
    }
    digitalWrite(PIN_OUTPUT_COVER_IR, LED_OFF);
    portENTER_CRITICAL(&mux);
    isFoodIROn = false;
    portEXIT_CRITICAL(&mux);
  }
}

void IRAM_ATTR onTimerHandler() {
  digitalWrite(PIN_OUTPUT_COVER_IR, LED_ON);
  portENTER_CRITICAL(&mux);
  isFoodIROn = true;
  portEXIT_CRITICAL(&mux);
}

void setupTimer() {
  timer = timerBegin(0, 80, true); 
  timerAttachInterrupt(timer, &onTimerHandler, true); 
  timerAlarmWrite(timer, 1000*1000, true); 
  timerAlarmEnable(timer); 
}