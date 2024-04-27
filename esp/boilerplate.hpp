#ifndef _BOILERPLATE_HPP
#define _BOILERPLATE_HPP 1

#include <Adafruit_SSD1306.h>

extern Adafruit_SSD1306 display;

/// @brief Displays `LOCAL ERROR` message on the OLED for `2` seconds.
void showLocalErrorMsg()
{
    display.clearDisplay();
    display.setCursor(34, 10); display.print(F("LOCAL"));
    display.setCursor(34, 38); display.print(F("ERROR"));
    display.display();
    delay(2000);
}

/// @brief Draw a `PROGMEM`-resident 64x64 horizontally centred (at
/// the position `(32, 0)`) 1-bit image, using the `WHITE` color for
/// `1` second.
/// @param bitmap Byte array with monochrome bitmap
void draw64x64Bitmap(const uint8_t bitmap[])
{
    display.clearDisplay();
    display.drawBitmap(33, 0, bitmap, 64, 64, WHITE);
    display.display();
    delay(1000);
}

#endif // _BOILERPLATE_HPP
