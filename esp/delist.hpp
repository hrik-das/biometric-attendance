#ifndef _DELIST_HPP
#define _DELIST_HPP 1

#include <Adafruit_SSD1306.h>
#include <Adafruit_Fingerprint.h>

#include "boilerplate.hpp"

extern Adafruit_SSD1306 display;

/// @brief Delete a user fingerprint at location `loc`.
/// @param loc `[IN]` Location where user's fingerprint is to be deleted.
/// @param sensor `[IN]` Object denoting a fingerprint sensor. Supply or
/// make available in caller scope. Should have same name as param.
/// @return `FINGERPRINT_OK` if user was sucessfully deleted. Not `FINGERPRINT_OK`
/// otherwise.
uint8_t delist(const uint16_t loc, Adafruit_Fingerprint &sensor)
{
    Serial.println(F("delist.hpp:delist"));
    switch (sensor.deleteModel(loc)) {
        case FINGERPRINT_OK:
            Serial.print(F("Deleted from Location: ")); Serial.println(loc);
            return FINGERPRINT_OK;
        case FINGERPRINT_PACKETRECIEVEERR:
            Serial.println(F("delist.hpp:20:FINGERPRINT_PACKETRECIEVEERR"));
            showLocalErrorMsg();
            return FINGERPRINT_PACKETRECIEVEERR;
        case FINGERPRINT_BADLOCATION:
            Serial.println(F("delist.hpp:20:FINGERPRINT_BADLOCATION"));
            showLocalErrorMsg();
            return FINGERPRINT_BADLOCATION;
        case FINGERPRINT_FLASHERR:
            Serial.println(F("delist.hpp:20:FINGERPRINT_FLASHERR"));
            showLocalErrorMsg();
            return FINGERPRINT_FLASHERR;
    }

    // to suppress `control reaches end of non-void function` warning
    // [`-Werror=return-type`]
    // will never run
    std::abort();
} // uint8_t delist(const uint16_t loc, Adafruit_Fingerprint &sensor)

#endif // _DELIST_HPP
