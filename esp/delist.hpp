#ifndef _DELIST_HPP
#define _DELIST_HPP 1

#include <Adafruit_Fingerprint.h>

#include "visuals.hpp"

/// @brief Delete a user fingerprint at location `loc`.
/// @param loc `[IN]` Location where user's fingerprint is to be deleted.
/// @param R307 `[IN]` Object denoting the R307 fingerprint sensor. Supply or
/// make available in caller scope. Should have same name as param.
/// @return `FINGERPRINT_OK` if user was sucessfully deleted. Not `FINGERPRINT_OK`
/// otherwise.
u8 delist(const u16 loc, Adafruit_Fingerprint &R307)
{
    Serial.println(F("delist.hpp:delist"));

    u8 temp = R307.deleteModel(loc);
    Serial.print(F("deleteModel(")); Serial.print(loc);
    Serial.print(F("): ")); Serial.println(temp);

    switch (temp) {
        case FINGERPRINT_OK:
            Serial.print(F("Deleted from Location: ")); Serial.println(loc);
            return temp;
        case FINGERPRINT_PACKETRECIEVEERR:
            Serial.println(F("delist.hpp:18:FINGERPRINT_PACKETRECIEVEERR"));
            showLocalErrorMsg();
            return temp;
        case FINGERPRINT_BADLOCATION:
            Serial.println(F("delist.hpp:18:FINGERPRINT_BADLOCATION"));
            showLocalErrorMsg();
            return temp;
        case FINGERPRINT_FLASHERR:
            Serial.println(F("delist.hpp:18:FINGERPRINT_FLASHERR"));
            showLocalErrorMsg();
            return temp;
        default:
            Serial.println(F("[UNDOCUMENTED]"));
            showLocalErrorMsg();
            return temp;
    }
} // u8 delist(const u16 loc, Adafruit_Fingerprint &R307)

#endif // _DELIST_HPP
