#include <Arduino.h>
#include "Schedule.hpp"

Schedule::Schedule()
{
  EEPROM.begin(EEPROM_SIZE);
  int scheduleSize = 0;
  int slotExist = EEPROM.read(EEPROM_LOCATION_SLOT_EXIST);
  if (slotExist == 100)
  {
    scheduleSize = EEPROM.read(EEPROM_LOCATION_SLOT_SIZE);
    this->size = scheduleSize;
    slots = new Slot[this->size];
    int slotStartAt = EEPROM_LOCATION_SLOT_START;
    for (int i = 0; i < size; i++)
    {
      EEPROM.get(slotStartAt, slots[i]);
      slotStartAt = slotStartAt + sizeof(slots[i]);
    }
    ListAll();
  }
  else
  {
    Serial.println("Slots does not exist");
  }
}

Schedule::~Schedule()
{
  delete[] slots;
}

bool Schedule::canDispense(uint8_t minute, uint8_t hour)
{
  for (size_t i = 0; i < size; i++)
  {
    if(slots[i].enabled && slots[i].hour == hour && slots[i].minute == minute) {
      return true;
    }
  }
  return false;
}

uint8_t Schedule::getDispenseUnit(uint8_t minute, uint8_t hour)
{
  for (size_t i = 0; i < size; i++)
  {
    if(slots[i].enabled && slots[i].hour == hour && slots[i].minute == minute) {
      return slots[i].unit;
    }
  }
  return 0;
}

void Schedule::ParseRawScheduleAndInsert(String schedule)
{
  char *currentSchedule = const_cast<char *>(schedule.c_str());
  Reset();
  if (schedule.indexOf("|") > 0)
  {
    char *slot;
    while ((slot = strtok_r(currentSchedule, "|", &currentSchedule)))
    {
      char *setting = ParseSlot(slot);
      Insert(setting[0], setting[1], setting[2], setting[3]);
      free(setting);
    }
  }
  else
  {
    if (schedule.length() > 0)
    {
      char *setting = ParseSlot(currentSchedule);
      Insert(setting[0], setting[1], setting[2], setting[3]);
      free(setting);
    }
  }
  Save();
  ListAll();
}

void Schedule::Insert(uint8_t minute, uint8_t hour, uint8_t unit, bool enabled)
{
  if (size == 0)
  {
    size = 1;
    delete[] slots;
    slots = new Slot[size];
  }
  else
  {
    Slot *newSlots = new Slot[size + 1];
    for (int i = 0; i < size; i++)
    {
      newSlots[i] = slots[i];
    }
    delete[] slots;
    size = size + 1;
    slots = newSlots;
  }
  slots[size - 1].minute = minute;
  slots[size - 1].hour = hour;
  slots[size - 1].enabled = enabled;
  slots[size - 1].unit = unit;
}

char *Schedule::ParseSlot(char *slots)
{
  char *item;
  uint8_t i = 0;
  char *setting = (char *)malloc(4);
  while ((item = strtok_r(slots, ":", &slots)))
  {
    setting[i] = atoi(item);
    i = i + 1;
  }
  return setting;
}

void Schedule::ListAll()
{
  for (int i = 0; i < size; i++)
  {
    Serial.println("----------------------------------");
    Serial.print("Minute: ");
    Serial.println(slots[i].minute);
    Serial.print("Hour: ");
    Serial.println(slots[i].hour);
    Serial.print("Unit: ");
    Serial.println(slots[i].unit);
    Serial.print("Enabled: ");
    Serial.println(slots[i].enabled);
  }
}

void Schedule::Reset()
{
  this->size = 0;
  slots = new Slot[this->size];
  Save();
}

void Schedule::Save()
{
  if (size > 0)
  {
    EEPROM.write(EEPROM_LOCATION_SLOT_EXIST, 100);
    EEPROM.write(EEPROM_LOCATION_SLOT_SIZE, size);
    int slotStartAt = EEPROM_LOCATION_SLOT_START;
    for (int i = 0; i < size; i++)
    {
      EEPROM.put(slotStartAt, slots[i]);
      slotStartAt = slotStartAt + sizeof(slots[i]);
    }
  }
  else
  {
    EEPROM.write(EEPROM_LOCATION_SLOT_EXIST, 0);
    EEPROM.write(EEPROM_LOCATION_SLOT_SIZE, 0);
  }
  EEPROM.commit();
}
