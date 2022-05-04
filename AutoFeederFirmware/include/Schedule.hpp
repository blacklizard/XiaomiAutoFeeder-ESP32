#ifndef SCHEDULE
#define SCHEDULE

#include <EEPROM.h>

#define EEPROM_SIZE 512
#define EEPROM_LOCATION_SLOT_EXIST 0
#define EEPROM_LOCATION_SLOT_SIZE 1
#define EEPROM_LOCATION_SLOT_START 5

class Schedule
{
private:
  struct __attribute((packed)) Slot
  {
    uint8_t minute;
    uint8_t hour;
    uint8_t unit;
    bool enabled;
  };
  Slot *slots = nullptr;
  int size;
  void ListAll();
  void Reset();
  void Insert(uint8_t minute, uint8_t hour, uint8_t unit, bool enabled);
  void Save();
  char *ParseSlot(char *slots);

public:
  Schedule();
  ~Schedule();
  void ParseRawScheduleAndInsert(String schedule);
  bool canDispense(uint8_t minute, uint8_t hour);
  uint8_t getDispenseUnit(uint8_t minute, uint8_t hour);
};

#endif /* SCHEDULE */
