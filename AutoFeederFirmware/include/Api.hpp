#ifndef API
#define API

#include <WiFi.h>
#include <HTTPClient.h>
#include <AsyncTCP.h>
#include "Config.h"
#include "ApiHttpResponse.hpp"

typedef enum {
  BOOT     = 1,
  DISPENSE    = 2,
  RECONNECT  = 3,
  COVER_IS_OPEN  = 4,
  LOW_FOOD  = 5,
  MANUAL_FEEDING  = 6,
} ApiAnnounceStates;

class Api
{
private:
    WiFiClient client;
    HTTPClient http;
    String baseURL = API_ENDPOINT;
    ApiHttpResponse _post(String path, String payload);
    ApiHttpResponse _get(String path);
    void Announce(ApiAnnounceStates status);
public:
    Api();
    ~Api();
    void AnnounceBoot();
    void AnnounceDispense();
    void AnnounceManualDispense();
    void AnnounceReconnect();
    void AnnounceCoverOpen();
    void AnnounceLowFood();
    ApiHttpResponse GetSchedule();
};

#endif /* API */
