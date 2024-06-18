#ifndef _DELIST_HPP
#define _DELIST_HPP 1

#include <Adafruit_Fingerprint.h>

#include "visuals.hpp"

/// @brief Delete a user fingerprint at location `loc`.
/// @param loc `[IN]` Location where user's fingerprint is to be deleted.
/// @param R307 `[IN]` Object denoting the R307 fingerprint sensor.
/// @return `FINGERPRINT_OK` if user was sucessfully deleted. Not `FINGERPRINT_OK`
/// otherwise.
u8 delist(const u16 loc, Adafruit_Fingerprint &R307)
{
    Serial.print(__FILE__); Serial.println(F(":delist"));

    Serial.print(__FILE__); Serial.print(F(":")); Serial.print(__LINE__ + 3);
    Serial.print(F(":deleteModel:loc:")); Serial.print(loc);

    switch (R307.deleteModel(loc)) {
        case FINGERPRINT_OK:
            Serial.println(F(":FINGERPRINT_OK"));
            return FINGERPRINT_OK;
        case FINGERPRINT_PACKETRECIEVEERR:
            Serial.println(F(":FINGERPRINT_PACKETRECIEVEERR"));
            showLocalErrorMsg();
            return ~FINGERPRINT_OK;
        case FINGERPRINT_BADLOCATION:
            Serial.println(F(":FINGERPRINT_BADLOCATION"));
            showLocalErrorMsg();
            return ~FINGERPRINT_OK;
        case FINGERPRINT_FLASHERR:
            Serial.println(F(":FINGERPRINT_FLASHERR"));
            showLocalErrorMsg();
            return ~FINGERPRINT_OK;
        default:
            Serial.println(F(":<--UNDOCUMENTED-->"));
            showLocalErrorMsg();
            return ~FINGERPRINT_OK;
    }
} // u8 delist(const u16 loc, Adafruit_Fingerprint &R307)

#endif // _DELIST_HPP
