#ifndef _UPDATE_HPP
#define _UPDATE_HPP 1

#include <Adafruit_SSD1306.h>
#include <Adafruit_Fingerprint.h>

#include "enlist.hpp"
#include "delist.hpp"

extern Adafruit_SSD1306 display;

/// @brief Update the user at library location `loc`.
/// @param loc `[IN]` Location where the user's fingerprint is to be updated.
/// @param sensor `[IN]` Object denoting a fingerprint sensor. Supply or
/// make available in caller scope. Should have same name as param.
/// @return `FINGERPRINT_OK` if user's fingerprint was sucessfully updated. Not
/// `FINGERPRINT_OK` otherwise. 
u8 update(const u16 loc, Adafruit_Fingerprint &sensor)
{
    Serial.println(F("update.hpp:update"));
    if (u8 result = delist(loc, sensor) != FINGERPRINT_OK)
        return result;
    
    if (u8 result = enlist(loc, sensor) != FINGERPRINT_OK)
        return result;

    return FINGERPRINT_OK;
} // u8 update(const u16 &loc, Adafruit_Fingerprint &sensor)

#endif // _UPDATE_HPP
