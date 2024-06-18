#ifndef _VISUALS_HPP
#define _VISUALS_HPP 1

#include <Adafruit_SSD1306.h>

extern Adafruit_SSD1306 SSD1306;

/// @brief Displays `LOCAL ERROR` message on the SSD1306 OLED for `2` seconds.
void showLocalErrorMsg()
{
    SSD1306.clearDisplay();
    SSD1306.setCursor(34, 10); SSD1306.print(F("LOCAL"));
    SSD1306.setCursor(34, 38); SSD1306.print(F("ERROR"));
    SSD1306.display();
    delay(2000);
} // void showLocalErrorMsg()

/// @brief Draw a `PROGMEM`-resident 64x64 horizontally centred (at the position
/// (32, 0)`) 1-bit image, using the `WHITE` color for `1` second.
/// @param bitmap Byte array with monochrome bitmap
void draw64x64Bitmap(const u8 bitmap[])
{
    SSD1306.clearDisplay();
    SSD1306.drawBitmap(33, 0, bitmap, 64, 64, WHITE);
    SSD1306.display();
    delay(1000);
} // void draw64x64Bitmap(const u8 bitmap[])

#endif // _VISUALS_HPP
