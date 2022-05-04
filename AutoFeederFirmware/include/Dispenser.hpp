#ifndef DISPENSER
#define DISPENSER

#include <Arduino.h>

#include "PinDefination.h"
#include "StateDefination.h"

class Dispenser
{
private:
  bool isDoorOpen = false;
  void CloseDoor();
  void OpenDoor();
public:
  Dispenser();
  ~Dispenser();
  void Dispense(uint8_t amount);
  void ResetFeederPosition(bool triggerSensprState);
  void ResetDoorPosition();
  void ActivateSensor();
  void DeactivateSensor();
};

#endif /* DISPENSER */
