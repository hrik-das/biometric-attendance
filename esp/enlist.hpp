#ifndef _ENLIST_HPP
#define _ENLIST_HPP 1

#include <Adafruit_SSD1306.h>
#include <Adafruit_Fingerprint.h>
#include <Ticker.h>

#include "visuals.hpp"
#include "bitmap.h"

extern Adafruit_SSD1306 SSD1306;

/// @brief Takes an image of the finger pressed on surface. Converts image to
/// feature template. Waits for ~6 seconds to take a fingerprint image.
/// @param slot `[IN]` Slot to place feature template. Put one template in slot
/// `1` and another in slot `2` for verification to create model.
/// @param R307 `[IN]` Object denoting the R307 fingerprint sensor.
/// @return `FINGERPRINT_OK` if user was sucessfully added. Not `FINGERPRINT_OK`
/// otherwise. `FINGERPRINT_TIMEOUT` if function waited for 6 full seconds.
u8 process_image(const u8 slot, Adafruit_Fingerprint &R307)
{
    Serial.print(F("enlist.hpp:process_image:slot - ")); Serial.println(slot);
    u8 temp = ~FINGERPRINT_OK;
    u8 time_left = 6;
    Ticker timer;

    timer.attach_ms(1000, [&time_left] { --time_left; });

    draw64x64Bitmap(SCANNING);

    while (temp) { /* != FINGERPRINT_OK */
        if (!time_left) {
            Serial.print(F("enlist.hpp:32:TIMEOUT:User took too long to enroll"));
            SSD1306.clearDisplay();
            SSD1306.setCursor(22, 16); SSD1306.print(F("TIMEOUT!"));
            SSD1306.display();
            
            return FINGERPRINT_TIMEOUT;
        }

        Serial.print(F("enlist.hpp:43:getImage:slot - ")); Serial.print(slot);

        temp = R307.getImage();

        switch (temp) {
            case FINGERPRINT_OK:
                Serial.println(F(" :FINGERPRINT_OK"));
                draw64x64Bitmap(FOUND);
                break;
            case FINGERPRINT_NOFINGER:
                Serial.println(F(" :FINGERPRINT_NOFINGER"));
                draw64x64Bitmap(SCANNING);
                break;
            case FINGERPRINT_PACKETRECIEVEERR:
                Serial.println(F(" :FINGERPRINT_PACKETRECIEVEERR"));
                showLocalErrorMsg();
                break;
            case FINGERPRINT_IMAGEFAIL:
                Serial.println(F(" :FINGERPRINT_IMAGEFAIL"));
                showLocalErrorMsg();
                break;
            default:
                Serial.println(F(" :<--UNDOCUMENTED-->"));
                showLocalErrorMsg();
                break;
        }
    } // while (temp != FINGERPRINT_OK)

    timer.detach();

    Serial.print(F("enlist.hpp:73:image2Tz:slot - ")); Serial.print(slot);

    switch (R307.image2Tz(slot)) {
        case FINGERPRINT_OK:
            Serial.println(F(" :FINGERPRINT_OK"));
            return FINGERPRINT_OK;
        case FINGERPRINT_IMAGEMESS:
            draw64x64Bitmap(NOT_FOUND);
            return ~FINGERPRINT_OK;
        case FINGERPRINT_PACKETRECIEVEERR:
            Serial.println(F(" :FINGERPRINT_PACKETRECIEVEERR"));
            showLocalErrorMsg();
            return ~FINGERPRINT_OK;
        case FINGERPRINT_FEATUREFAIL:
            Serial.println(F(" :FINGERPRINT_FEATUREFAIL"));
            showLocalErrorMsg();
            return ~FINGERPRINT_OK;
        case FINGERPRINT_INVALIDIMAGE:
            Serial.println(F(" :FINGERPRINT_INVALIDIMAGE"));
            showLocalErrorMsg();
            return ~FINGERPRINT_OK;
        default:
            Serial.println(F(" :<--UNDOCUMENTED-->"));
            showLocalErrorMsg();
            return ~FINGERPRINT_OK;
    }
} // u8 process_image(const u8 slot, Adafruit_Fingerprint &R307)

/// @brief Enroll a new user at library location `loc`.
/// @param loc `[IN]` Location where new user's fingerprint is to be stored.
/// @param R307 `[IN]` Object denoting the R307 fingerprint sensor.
/// @return `FINGERPRINT_OK` if user's fingerprint was sucessfully created and
/// stored. Not `FINGERPRINT_OK` otherwise.
u8 enlist(const u16 loc, Adafruit_Fingerprint &R307)
{
    Serial.println(F("enlist.hpp:enlist"));
    // SLOT 1
    if (process_image(1, R307) /* != FINGERPRINT_OK */ )
        return ~FINGERPRINT_OK;

    SSD1306.clearDisplay();
    SSD1306.setCursor(28, 10); SSD1306.print(F("REMOVE"));
    SSD1306.setCursor(28, 38); SSD1306.print(F("FINGER"));
    SSD1306.display();
    Serial.println("REMOVE FINGER");
    // display "REMOVE FINGER" message for 2 seconds
    delay(2000);
    
    // Ensure that finger is taken off the sensor before taking temp for SLOT 2
    while (R307.getImage() != FINGERPRINT_NOFINGER);
    // finger removed, erase "REMOVE FINGER" message
    SSD1306.clearDisplay();

    // SLOT 2
    if (process_image(2, R307) /* != FINGERPRINT_OK */ )
        return ~FINGERPRINT_OK;

    Serial.print(F("enlist.hpp:130:createModel:"));

    switch (R307.createModel()) {
        case FINGERPRINT_OK:
            Serial.println(F("FINGERPRINT_OK"));
            break;
        case FINGERPRINT_PACKETRECIEVEERR:
            Serial.println(F("FINGERPRINT_PACKETRECIEVEERR"));
            showLocalErrorMsg();
            return ~FINGERPRINT_OK;
        case FINGERPRINT_ENROLLMISMATCH:
            Serial.println(F("FINGERPRINT_ENROLLMISMATCH"));
            showLocalErrorMsg();
            return ~FINGERPRINT_OK;
        default:
            Serial.println(F("<--UNDOCUMENTED-->"));
            showLocalErrorMsg();
            return ~FINGERPRINT_OK;
    }
    
    Serial.print(F("enlist.hpp:150:storeModel:loc - ")); Serial.print(loc);

    switch (R307.storeModel(loc)) {
        case FINGERPRINT_OK:
            Serial.println(F(" :FINGERPRINT_OK"));
            draw64x64Bitmap(FOUND);
            return FINGERPRINT_OK;
        case FINGERPRINT_BADLOCATION:
            Serial.println(F(" :FINGERPRINT_BADLOCATION"));
            showLocalErrorMsg();
            return ~FINGERPRINT_OK;
        case FINGERPRINT_FLASHERR:
            Serial.println(F(" :FINGERPRINT_FLASHERR"));
            showLocalErrorMsg();
            return ~FINGERPRINT_OK;
        case FINGERPRINT_PACKETRECIEVEERR:
            Serial.println(F(" :FINGERPRINT_PACKETRECIEVEERR"));
            showLocalErrorMsg();
            return ~FINGERPRINT_OK;
        default:
            Serial.println(F(" :<--UNDOCUMENTED-->"));
            showLocalErrorMsg();
            return ~FINGERPRINT_OK;
    }
} // u8 enlist(const u16 loc, Adafruit_Fingerprint &sensor)

#endif // _ENLIST_HPP
