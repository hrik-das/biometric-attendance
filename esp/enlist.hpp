#ifndef _ENLIST_HPP
#define _ENLIST_HPP 1

#include <Adafruit_SSD1306.h>
#include <Adafruit_Fingerprint.h>
#include <atomic>

#include "Ticker.h"
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
    Serial.print(__FILE__); Serial.print(F(":process_image:slot:")); Serial.println(slot);

    u8 temp = ~FINGERPRINT_OK;
    
    std::atomic<bool> timeout;
    timeout.store(false);
    
    uint_least32_t interval_ms = 6000;

    // Run lambda after 6000ms, stopping ticker at lambda exit
    Ticker ticker (
        [&timeout, &ticker] { timeout.store(true); ticker.stop(); },
        interval_ms);

    ticker.start();

    draw64x64Bitmap(SCANNING);

    while (temp) { /* != FINGERPRINT_OK */
        ticker.update();

        if (timeout.load()) {
            Serial.print(__FILE__); Serial.print(F(":")); Serial.print(__LINE__ - 1);
            Serial.println(F(":TIMEOUT"));
            SSD1306.clearDisplay();
            SSD1306.setCursor(22, 16); SSD1306.print(F("TIMEOUT!"));
            SSD1306.display();
            
            return FINGERPRINT_TIMEOUT;
        }

        Serial.print(__FILE__); Serial.print(F(":")); Serial.print(__LINE__ + 3);
        Serial.print(F(":getImage:slot:")); Serial.print(slot);

        temp = R307.getImage();

        switch (temp) {
            case FINGERPRINT_OK:
                Serial.println(F(":FINGERPRINT_OK"));
                draw64x64Bitmap(FOUND);
                break;
            case FINGERPRINT_NOFINGER:
                Serial.println(F(":FINGERPRINT_NOFINGER"));
                draw64x64Bitmap(SCANNING);
                break;
            case FINGERPRINT_PACKETRECIEVEERR:
                Serial.println(F(":FINGERPRINT_PACKETRECIEVEERR"));
                SSD1306LocalErrorMsg();
                break;
            case FINGERPRINT_IMAGEFAIL:
                Serial.println(F(":FINGERPRINT_IMAGEFAIL"));
                SSD1306LocalErrorMsg();
                break;
            default:
                Serial.println(F(":<--UNDOCUMENTED-->"));
                SSD1306LocalErrorMsg();
                break;
        }
    } // while (temp != FINGERPRINT_OK)

    Serial.print(__FILE__); Serial.print(F(":")); Serial.print(__LINE__ + 3);
    Serial.print(F(":image2Tz:slot:")); Serial.print(slot);

    switch (R307.image2Tz(slot)) {
        case FINGERPRINT_OK:
            Serial.println(F(":FINGERPRINT_OK"));
            return FINGERPRINT_OK;
        case FINGERPRINT_IMAGEMESS:
            draw64x64Bitmap(NOT_FOUND);
            return ~FINGERPRINT_OK;
        case FINGERPRINT_PACKETRECIEVEERR:
            Serial.println(F(":FINGERPRINT_PACKETRECIEVEERR"));
            SSD1306LocalErrorMsg();
            return ~FINGERPRINT_OK;
        case FINGERPRINT_FEATUREFAIL:
            Serial.println(F(":FINGERPRINT_FEATUREFAIL"));
            SSD1306FeatureFailMsg();
            return ~FINGERPRINT_OK;
        case FINGERPRINT_INVALIDIMAGE:
            Serial.println(F(":FINGERPRINT_INVALIDIMAGE"));
            SSD1306InvalidImageMsg();
            return ~FINGERPRINT_OK;
        default:
            Serial.println(F(":<--UNDOCUMENTED-->"));
            SSD1306LocalErrorMsg();
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
    Serial.print(__FILE__); Serial.println(F(":enlist"));

    // SLOT 1
    if (process_image(1, R307) /* != FINGERPRINT_OK */ )
        return ~FINGERPRINT_OK;

    if (!R307.getImage()) {
        SSD1306.clearDisplay();
        SSD1306.setCursor(28, 10); SSD1306.print(F("REMOVE"));
        SSD1306.setCursor(28, 38); SSD1306.print(F("FINGER"));
        SSD1306.display();
        Serial.print(__FILE__); Serial.print(F(":")); Serial.print(__LINE__ - 5);
        Serial.println(F(":getImage:0:FINGERPRINT_OK"));
        Serial.println("REMOVE FINGER");
        // display "REMOVE FINGER" message if finger is still pressed
    }    
    
    // Ensure that finger is taken off the sensor before taking temp for SLOT 2
    while (R307.getImage() != FINGERPRINT_NOFINGER);
    // finger removed, erase "REMOVE FINGER" message
    SSD1306.clearDisplay();
    SSD1306.display();

    // SLOT 2
    if (process_image(2, R307) /* != FINGERPRINT_OK */ )
        return ~FINGERPRINT_OK;

    Serial.print(__FILE__); Serial.print(F(":")); Serial.print(__LINE__ + 3);
    Serial.print(F(":createModel:"));

    switch (R307.createModel()) {
        case FINGERPRINT_OK:
            Serial.println(F("FINGERPRINT_OK"));
            break;
        case FINGERPRINT_PACKETRECIEVEERR:
            Serial.println(F("FINGERPRINT_PACKETRECIEVEERR"));
            SSD1306LocalErrorMsg();
            return ~FINGERPRINT_OK;
        case FINGERPRINT_ENROLLMISMATCH:
            Serial.println(F("FINGERPRINT_ENROLLMISMATCH"));
            SSD1306MismatchMsg();
            return ~FINGERPRINT_OK;
        default:
            Serial.println(F("<--UNDOCUMENTED-->"));
            SSD1306LocalErrorMsg();
            return ~FINGERPRINT_OK;
    }
    
    Serial.print(__FILE__); Serial.print(F(":")); Serial.print(__LINE__ + 3);
    Serial.print(F(":storeModel:loc:")); Serial.print(loc);

    switch (R307.storeModel(loc)) {
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
} // u8 enlist(const u16 loc, Adafruit_Fingerprint &sensor)

#endif // _ENLIST_HPP
