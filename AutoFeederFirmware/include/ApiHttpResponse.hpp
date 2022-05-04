#ifndef APIHTTPRESPONSE
#define APIHTTPRESPONSE

#include <Arduino.h>

class ApiHttpResponse
{
private:
  int httpCode;
  String body;
public:
  ApiHttpResponse(int httpCode, String body):httpCode(httpCode), body(body){}
  ~ApiHttpResponse() {}
  
  String getBody() {
    return body;
  }

  bool success() {
    if(httpCode >= 200 && httpCode < 300) {
      return true;
    }
    return false;
  }
};

#endif /* APIHTTPRESPONSE */
