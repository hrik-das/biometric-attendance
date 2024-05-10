#ifndef _UPDATE_HPP
#define _UPDATE_HPP 1

#include <Adafruit_Fingerprint.h>

#include "enlist.hpp"
#include "delist.hpp"

/// @brief Update the user at library location `loc`.
/// @param loc `[IN]` Location where the user's fingerprint is to be updated.
/// @param R307 `[IN]` Object denoting the R307 fingerprint sensor.
/// @return `FINGERPRINT_OK` if user's fingerprint was sucessfully updated. Not
/// `FINGERPRINT_OK` otherwise. 
u8 update(const u16 loc, Adafruit_Fingerprint &R307)
{
    Serial.println(F("update.hpp:update"));
    if (u8 result = delist(loc, R307) /* != FINGERPRINT_OK */ )
        return result;

    return enlist(loc, R307);
} // u8 update(const u16 &loc, Adafruit_Fingerprint &R307)

#endif // _UPDATE_HPP
