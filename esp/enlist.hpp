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
            Serial.print(F("enlist.hpp:32:FINGERPRINT_TIMEOUT:\
User took too long to enroll"));
            SSD1306.clearDisplay();
            SSD1306.setCursor(22, 16); SSD1306.print(F("TIMEOUT!"));
            SSD1306.display();
            return FINGERPRINT_TIMEOUT;
        }

        temp = R307.getImage();
        Serial.print(F("getImage(): ")); Serial.println(temp);

        switch (temp) {
            case FINGERPRINT_OK:
                Serial.print(F("Image taken. Slot: ")); Serial.println(slot);
                // give the user an affirmative message that their
                // fingerprint was scanned successfully
                draw64x64Bitmap(FOUND);
                break;
            case FINGERPRINT_NOFINGER:
                draw64x64Bitmap(SCANNING);
                break;
            case FINGERPRINT_PACKETRECIEVEERR:
                Serial.println(F("enlist.hpp:41:FINGERPRINT_PACKETRECIEVEERR"));
                showLocalErrorMsg();
                break;
            case FINGERPRINT_IMAGEFAIL:
                Serial.println(F("enlist.hpp:41:FINGERPRINT_IMAGEFAIL"));
                showLocalErrorMsg();
                break;
        }
    } // while (temp != FINGERPRINT_OK)

    timer.detach();

    temp = R307.image2Tz(slot);
    Serial.print(F("image2Tz(")); Serial.print(slot);
    Serial.print(F("): ")); Serial.println(temp);

    switch (temp) {
        case FINGERPRINT_OK:
            return temp;
        case FINGERPRINT_IMAGEMESS:
            draw64x64Bitmap(NOT_FOUND);
            return temp;
        case FINGERPRINT_PACKETRECIEVEERR:
            Serial.println(F("enlist.hpp:67:FINGERPRINT_PACKETRECIEVEERR"));
            showLocalErrorMsg();
            return temp;
        case FINGERPRINT_FEATUREFAIL:
            Serial.println(F("enlist.hpp:67:FINGERPRINT_FEATUREFAIL"));
            showLocalErrorMsg();
            return temp;
        case FINGERPRINT_INVALIDIMAGE:
            Serial.println(F("enlist.hpp:67:FINGERPRINT_INVALIDIMAGE"));
            showLocalErrorMsg();
            return temp;
        default:
            Serial.println(F("[UNDOCUMENTED]"));
            showLocalErrorMsg();
            return temp;
    }
} // u8 process_image(const u8 slot, Adafruit_Fingerprint &R307)

/// @brief Enroll a new user at library location `loc`.
/// @param loc `[IN]` Location where new user's fingerprint is to be stored.
/// @param R307 `[IN]` Object denoting the R307 fingerprint sensor.
/// @return `FINGERPRINT_OK` if user's fingerprint was sucessfully created and
/// stored. Not `FINGERPRINT_OK` otherwise. `FINGERPRINT_TIMEOUT` if user took
/// too much time in the process.
u8 enlist(const u16 loc, Adafruit_Fingerprint &R307)
{
    Serial.println(F("enlist.hpp:enlist"));
    // SLOT 1
    if (u8 result = process_image(1, R307) /* != FINGERPRINT_OK */ )
        return result;

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
    if (u8 result = process_image(2, R307) /* != FINGERPRINT_OK */ )
        return result;

    Serial.println("Creating model.");
    u8 temp = R307.createModel();
    Serial.print(F("createModel(): ")); Serial.println(temp);

    switch (temp) {
        case FINGERPRINT_OK:
            Serial.println("Model created!");
            break;
        case FINGERPRINT_PACKETRECIEVEERR:
            Serial.println(F("enlist.hpp:127:FINGERPRINT_PACKETRECIEVEERR"));
            showLocalErrorMsg();
            return temp;
        case FINGERPRINT_ENROLLMISMATCH:
            Serial.println(F("enlist.hpp:127:FINGERPRINT_ENROLLMISMATCH"));
            showLocalErrorMsg();
            return temp;
        default:
            Serial.println(F("[UNDOCUMENTED]"));
            showLocalErrorMsg();
            return temp;
    }
    
    Serial.println("Storing model.");

    temp = R307.storeModel(loc);
    Serial.print(F("storeModel(")); Serial.print(loc);
    Serial.print(F("): ")); Serial.println(temp);

    switch (temp) {
        case FINGERPRINT_OK:
            Serial.print("Stored model at Location: "); Serial.println(loc);
            draw64x64Bitmap(FOUND);
            return temp;
        case FINGERPRINT_BADLOCATION:
            Serial.println(F("enlist.hpp:150:FINGERPRINT_BADLOCATION"));
            showLocalErrorMsg();
            return temp;
        case FINGERPRINT_FLASHERR:
            Serial.println(F("enlist.hpp:150:FINGERPRINT_FLASHERR"));
            showLocalErrorMsg();
            return temp;
        case FINGERPRINT_PACKETRECIEVEERR:
            Serial.println(F("enlist.hpp:150:FINGERPRINT_PACKETRECIEVEERR"));
            showLocalErrorMsg();
            return temp;
        default:
            Serial.println(F("[UNDOCUMENTED]"));
            showLocalErrorMsg();
            return temp;
    }
} // u8 enlist(const u16 loc, Adafruit_Fingerprint &sensor)

#endif // _ENLIST_HPP
