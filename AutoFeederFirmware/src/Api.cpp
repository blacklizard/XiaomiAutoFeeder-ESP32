#include "Api.hpp"

Api::Api() {}
Api::~Api() {}

void Api::AnnounceBoot()
{
  Announce(BOOT);
}

void Api::AnnounceDispense()
{
  Announce(DISPENSE);
}

void Api::AnnounceReconnect()
{
  Announce(RECONNECT);
}

void Api::AnnounceCoverOpen()
{
  Announce(COVER_IS_OPEN);
}

void Api::AnnounceLowFood()
{
  Announce(LOW_FOOD);
}

void Api::AnnounceManualDispense()
{
  Announce(MANUAL_FEEDING);
}

void Api::Announce(ApiAnnounceStates status)
{
  String payload = "{\"ip\":\"" + WiFi.localIP().toString() + "\",\"mac\":\"" + WiFi.macAddress() + "\", \"status\": " + status + "}";
  _post("/announce", payload);
}

ApiHttpResponse Api::GetSchedule()
{
  return _get("/schedule/" + WiFi.macAddress());
}

ApiHttpResponse Api::_get(String path)
{
  
  http.begin(client, baseURL + path);
  int httpResponseCode = http.GET();
  ApiHttpResponse response(httpResponseCode, http.getString());
  Serial.print(response.getBody());
  http.end();
  return response;
}

ApiHttpResponse Api::_post(String path, String payload)
{
  http.begin(client, baseURL + path);
  http.addHeader("Content-Type", "application/json");
  int httpResponseCode = http.POST(payload);
  ApiHttpResponse response(httpResponseCode, http.getString());
  Serial.print(response.getBody());
  http.end();
  return response;
}