#ifndef _UPDATE_HPP
#define _UPDATE_HPP 1

#include <Adafruit_Fingerprint.h>

#include "Adafuit_Fingerprint_Extended.hpp"
#include "enlist.hpp"
#include "delist.hpp"

extern String roll;

enum class CopyType : u8 {
    Duplicate,
    Retrieve,
};

extern uint16_t lastPageID;

u8 copyModel(CopyType copyType, const u16 loc, Adafruit_Fingerprint &R307)
{
    Serial.print(__FILE__); Serial.print(F(":copyModel:CopyType:"));

    uint16_t source, dest;

    switch (copyType) {
        case CopyType::Duplicate: source = loc; dest = lastPageID; Serial.println("Duplicate"); break;
        case CopyType::Retrieve: source = lastPageID; dest = loc; Serial.println("Retrieve"); break;
    }

    for (const uint8_t& CharBuffer: {1, 2}) {
        Serial.print(__FILE__); Serial.print(F(":")); Serial.print(__LINE__ + 3);
        Serial.print(F(":basLoadModel:CharBuffer:")); Serial.print(CharBuffer);

        switch (basLoadModel(R307, CharBuffer, source)) {
            case FINGERPRINT_OK:
                Serial.println(F(":FINGERPRINT_OK"));
                break;
            case FINGERPRINT_BADLOCATION:
                Serial.println(F(":FINGERPRINT_BADLOCATION"));
                SSD1306LocalErrorMsg();
                return ~FINGERPRINT_OK;
            case FINGERPRINT_PACKETRECIEVEERR:
                Serial.println(F(":FINGERPRINT_PACKETRECIEVEERR"));
                SSD1306LocalErrorMsg();
                return ~FINGERPRINT_OK;
            default:
                Serial.println(F(":<--UNDOCUMENTED-->"));
                SSD1306LocalErrorMsg();
                return ~FINGERPRINT_OK;
        }
    }

    Serial.print(__FILE__); Serial.print(F(":")); Serial.print(__LINE__ + 3);
    Serial.print(F(":storeModel:loc:")); Serial.print(dest);

    switch (R307.storeModel(dest)) {
        case FINGERPRINT_OK:
            Serial.println(F(":FINGERPRINT_OK"));
            draw64x64Bitmap(FOUND);
            return FINGERPRINT_OK;
        case FINGERPRINT_BADLOCATION:
            Serial.println(F(":FINGERPRINT_BADLOCATION"));
            SSD1306LocalErrorMsg();
            return ~FINGERPRINT_OK;
        case FINGERPRINT_FLASHERR:
            Serial.println(F(":FINGERPRINT_FLASHERR"));
            SSD1306LocalErrorMsg();
            return ~FINGERPRINT_OK;
        case FINGERPRINT_PACKETRECIEVEERR:
            Serial.println(F(":FINGERPRINT_PACKETRECIEVEERR"));
            SSD1306LocalErrorMsg();
            return ~FINGERPRINT_OK;
        default:
            Serial.println(F(":<--UNDOCUMENTED-->"));
            SSD1306LocalErrorMsg();
            return ~FINGERPRINT_OK;
    }
} // u8 copyModel(const u16 loc, Adafruit_Fingerprint &R307)

/// @brief Update the user at library location `loc`.
/// @param loc `[IN]` Location where the user's fingerprint is to be updated.
/// @param R307 `[IN]` Object denoting the R307 fingerprint sensor.
/// @return `FINGERPRINT_OK` if user's fingerprint was sucessfully updated. Not
/// `FINGERPRINT_OK` otherwise. 
u8 update(const u16 loc, Adafruit_Fingerprint &R307)
{
    Serial.print(__FILE__); Serial.println(F(":update"));

    if (copyModel(CopyType::Duplicate, loc, R307)) /* != FINGERPRINT_OK */
        return ~FINGERPRINT_OK;

    if (delist(loc, R307)) /* != FINGERPRINT_OK */
        return ~FINGERPRINT_OK;

    if (enlist(loc, R307)) { /* != FINGERPRINT_OK */
        if (copyModel(CopyType::Retrieve, loc, R307)) { /* != FINGERPRINT_OK */
            SSD1306.clearDisplay();
            SSD1306.setCursor(10, 6); SSD1306.print(F("RE-ENLIST"));
            SSD1306.setTextSize(3);
            SSD1306.setCursor((128 - 18 * (roll.length() + 1)) >> 1, 38);
            SSD1306.print(F("#")); SSD1306.print(roll);
            SSD1306.setTextSize(2);
            SSD1306.display();
            return ~FINGERPRINT_OK;
        }

        // if (delist(lastPageID, R307)) /* != FINGERPRINT_OK */
        //     return ~FINGERPRINT_OK;

        (void) delist(lastPageID, R307);

        return ~FINGERPRINT_OK;
    }

    if (delist(lastPageID, R307)) /* != FINGERPRINT_OK */
        return ~FINGERPRINT_OK;

    return FINGERPRINT_OK;
} // u8 update(const u16 loc, Adafruit_Fingerprint &R307)

#endif // _UPDATE_HPP
