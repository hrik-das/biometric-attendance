#ifndef _ENLIST_HPP
#define _ENLIST_HPP 1

#include <Adafruit_SSD1306.h>
#include <Adafruit_Fingerprint.h>
#include <Ticker.h>

#include "boilerplate.hpp"
#include "bitmap.h"

extern Adafruit_SSD1306 display;

/// @brief Takes an image of the sensor pressed on surface. Converts image to
/// feature template. Waits for ~6 seconds to take a fingerprint image.
/// @param slot `[IN]` Slot to place feature template. Put one template in slot
/// `1` and another in slot `2` for verification to create model.
/// @param sensor `[IN]` Object denoting a fingerprint sensor. Supply or
/// make available in caller scope. Should have same name as param.
/// @return `FINGERPRINT_OK` if user was sucessfully added. Not `FINGERPRINT_OK`
/// otherwise. `FINGERPRINT_TIMEOUT` if function waited for 6 full seconds.
u8 process_image(const u8 slot, Adafruit_Fingerprint &sensor)
{
    Serial.print(F("enlist.hpp:process_image:slot - ")); Serial.println(slot);
    u8 image = ~FINGERPRINT_OK;
    u8 timeout = 6;
    Ticker timer;

    timer.attach_ms(1000, [&timeout] { --timeout; });

    draw64x64Bitmap(SCANNING);

    while (image != FINGERPRINT_OK) {
        if (!timeout) {
            Serial.print(F("enlist.hpp:33:FINGERPRINT_TIMEOUT:\
User took too long to enroll"));
            display.clearDisplay();
            display.setCursor(22, 16); display.print(F("TIMEOUT!"));
            display.display();
            return FINGERPRINT_TIMEOUT;
        }

        image = sensor.getImage();
        switch (image) {
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
                Serial.println(F("enlist.hpp:42:FINGERPRINT_PACKETRECIEVEERR"));
                showLocalErrorMsg();
                break;
            case FINGERPRINT_IMAGEFAIL:
                Serial.println(F("enlist.hpp:42:FINGERPRINT_IMAGEFAIL"));
                showLocalErrorMsg();
                break;
        }
    } // while (image != FINGERPRINT_OK)

    timer.detach();

    switch (sensor.image2Tz(slot)) {
        case FINGERPRINT_OK:
            return FINGERPRINT_OK;
        case FINGERPRINT_IMAGEMESS:
            draw64x64Bitmap(NOT_FOUND);
            return FINGERPRINT_IMAGEMESS;
        case FINGERPRINT_PACKETRECIEVEERR:
            Serial.println(F("enlist.hpp:66:FINGERPRINT_PACKETRECIEVEERR"));
            showLocalErrorMsg();
            return FINGERPRINT_PACKETRECIEVEERR;
        case FINGERPRINT_FEATUREFAIL:
            Serial.println(F("enlist.hpp:66:FINGERPRINT_FEATUREFAIL"));
            showLocalErrorMsg();
            return FINGERPRINT_FEATUREFAIL;
        case FINGERPRINT_INVALIDIMAGE:
            Serial.println(F("enlist.hpp:66:FINGERPRINT_INVALIDIMAGE"));
            showLocalErrorMsg();
            return FINGERPRINT_INVALIDIMAGE;
    }

    // to suppress `control reaches end of non-void
    // function` warning (`-Werror=return-type`)
    // will never run
    std::abort();
} // u8 process_image(const u8 slot, Adafruit_Fingerprint &sensor)

/// @brief Enroll a new user at library location `loc`.
/// @param loc `[IN]` Location where new user's fingerprint is to be stored.
/// @param sensor `[IN]` Object denoting a fingerprint sensor. Supply or
/// make available in caller scope. Should have same name as param.
/// @return `FINGERPRINT_OK` if user's fingerprint was sucessfully created and
/// stored. Not `FINGERPRINT_OK` otherwise. `FINGERPRINT_TIMEOUT` if user took
/// too much time in the process.
u8 enlist(const u16 loc, Adafruit_Fingerprint &sensor)
{
    Serial.println(F("enlist.hpp:enlist"));
    // SLOT 1
    if (u8 res = process_image(1, sensor) != FINGERPRINT_OK)
        return res;

    display.clearDisplay();
    display.setCursor(28, 10); display.print(F("REMOVE"));
    display.setCursor(28, 38); display.print(F("FINGER"));
    display.display();
    Serial.println("REMOVE FINGER");
    // display "REMOVE FINGER" message for 2 seconds
    delay(2000);
    
    // Ensure that finger is taken off the sensor before taking image for SLOT 2
    while (sensor.getImage() != FINGERPRINT_NOFINGER);
    // finger removed, erase "REMOVE FINGER" message
    display.clearDisplay();

    // SLOT 2
    if (u8 res = process_image(2, sensor) != FINGERPRINT_OK) {
        return res;
    }

    Serial.println("Creating model.");
    switch (sensor.createModel()) {
        case FINGERPRINT_OK:
            Serial.println("Model created!");
            break;
        case FINGERPRINT_PACKETRECIEVEERR:
            Serial.println(F("enlist.hpp:125:FINGERPRINT_PACKETRECIEVEERR"));
            showLocalErrorMsg();
            return FINGERPRINT_PACKETRECIEVEERR;
        case FINGERPRINT_ENROLLMISMATCH:
            Serial.println(F("enlist.hpp:125:FINGERPRINT_ENROLLMISMATCH"));
            showLocalErrorMsg();
            return FINGERPRINT_ENROLLMISMATCH;
    }
    
    Serial.println("Storing model.");
    switch (sensor.storeModel(loc)) {
        case FINGERPRINT_OK:
            Serial.print("Stored model at Location: "); Serial.println(loc);
            draw64x64Bitmap(FOUND);
            return FINGERPRINT_OK;
        case FINGERPRINT_BADLOCATION:
            Serial.println(F("enlist.hpp:140:FINGERPRINT_BADLOCATION"));
            showLocalErrorMsg();
            return FINGERPRINT_BADLOCATION;
        case FINGERPRINT_FLASHERR:
            Serial.println(F("enlist.hpp:140:FINGERPRINT_FLASHERR"));
            showLocalErrorMsg();
            return FINGERPRINT_FLASHERR;
        case FINGERPRINT_PACKETRECIEVEERR:
            Serial.println(F("enlist.hpp:140:FINGERPRINT_PACKETRECIEVEERR"));
            showLocalErrorMsg();
            return FINGERPRINT_PACKETRECIEVEERR;
    }

    // to suppress `control reaches end of non-void function` warning
    // (`-Werror=return-type`)
    // will never run
    std::abort();
} // u8 enlist(const u16 loc, Adafruit_Fingerprint &sensor)

#endif // _ENLIST_HPP
