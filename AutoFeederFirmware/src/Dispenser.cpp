#include "Dispenser.hpp"

Dispenser::Dispenser()
{
  ActivateSensor();
  int SENSOR_DOOR = digitalRead(PIN_INPUT_HALL_SENSOR_DOOR);
  if (SENSOR_DOOR == DOOR_OPENED)
  {
    isDoorOpen = true;
  }
  else
  {
    isDoorOpen = false;
  }

  if (isDoorOpen)
  {
    CloseDoor();
  }
  DeactivateSensor();
}
Dispenser::~Dispenser(){}

void Dispenser::ActivateSensor(){
  digitalWrite(PIN_OUTPUT_HALL_SENSOR, HIGH);
}

void Dispenser::DeactivateSensor(){
  digitalWrite(PIN_OUTPUT_HALL_SENSOR, LOW);
}

void Dispenser::ResetFeederPosition(bool triggerSensprState) {
  if(triggerSensprState) {
    ActivateSensor();
  }
  
  if(digitalRead(PIN_INPUT_HALL_SENSOR_FEED) == 0) {
    digitalWrite(PIN_OUTPUT_FEED_OPEN, HIGH);
    // ledcWrite(PIN_OUTPUT_FEED_OPEN_PWN_CHANNEL, 4000);
    while(digitalRead(PIN_INPUT_HALL_SENSOR_FEED) == 0) {}
    digitalWrite(PIN_OUTPUT_FEED_OPEN, LOW);
    // ledcWrite(PIN_OUTPUT_FEED_OPEN_PWN_CHANNEL, 0);
  }

  if(triggerSensprState) {
    DeactivateSensor();
  }
}

void Dispenser::ResetDoorPosition() {
  ActivateSensor();
  CloseDoor();
  DeactivateSensor();
}

void Dispenser::Dispense(uint8_t unit){
  digitalWrite(PIN_OUTPUT_LED_COVER_OPEN, LED_OFF); 
  digitalWrite(PIN_OUTPUT_LED_DISPENSING, LED_ON);
  ActivateSensor();
  delay(10);
  OpenDoor();
  delay(100);
  for (size_t i = 0; i < unit; i++)
  {
    digitalWrite(PIN_OUTPUT_FEED_OPEN, HIGH);
    // ledcWrite(PIN_OUTPUT_FEED_OPEN_PWN_CHANNEL, 4000);
    while(digitalRead(PIN_INPUT_HALL_SENSOR_FEED) == 1) {}
    digitalWrite(PIN_OUTPUT_FEED_OPEN, LOW);
    // ledcWrite(PIN_OUTPUT_FEED_OPEN_PWN_CHANNEL, 0);
    delay(100);
    ResetFeederPosition(false);
    delay(500);
  }
  delay(2000);
  CloseDoor();
  DeactivateSensor();
  digitalWrite(PIN_OUTPUT_LED_DISPENSING, LED_OFF);
}

void Dispenser::CloseDoor()
{
  OpenDoor();
  int SENSOR_DOOR = digitalRead(PIN_INPUT_HALL_SENSOR_DOOR);
  while (SENSOR_DOOR == DOOR_OPENED && isDoorOpen)
  {
    digitalWrite(PIN_OUTPUT_DOOR_CLOSE, HIGH);
    SENSOR_DOOR = digitalRead(PIN_INPUT_HALL_SENSOR_DOOR);
    while(SENSOR_DOOR == DOOR_OPENED  && isDoorOpen) {
      delay(300);
      digitalWrite(PIN_OUTPUT_DOOR_CLOSE, LOW);
      isDoorOpen = false;
    }
  }
}

void Dispenser::OpenDoor()
{
  int SENSOR_DOOR = digitalRead(PIN_INPUT_HALL_SENSOR_DOOR);
  while (SENSOR_DOOR == DOOR_CLOSED && !isDoorOpen)
  {
    digitalWrite(PIN_OUTPUT_DOOR_OPEN, HIGH);
    SENSOR_DOOR = digitalRead(PIN_INPUT_HALL_SENSOR_DOOR);
    if (SENSOR_DOOR == DOOR_OPENED)
    {
      digitalWrite(PIN_OUTPUT_DOOR_OPEN, LOW);
      isDoorOpen = true;
      digitalWrite(PIN_OUTPUT_DOOR_CLOSE, HIGH);
      delay(7);
      digitalWrite(PIN_OUTPUT_DOOR_CLOSE, LOW);
    }
  }
}