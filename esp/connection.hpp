#ifndef _CONNECTION_HPP
#define _CONNECTION_HPP 1

#include <ESP8266WiFi.h>
#include <Adafruit_SSD1306.h>

extern Adafruit_SSD1306 display;
extern const char *ssid;
extern const char *passphrase;

/// @brief Ensure WiFi connection. Does not return until ESP is
/// connected to WiFi. Reconnects if connection is lost.
void verify_conn()
{
    Serial.println(F("connection.hpp:verify_conn"));
    while (WiFi.status() != WL_CONNECTED) {
        // From the docs:
        // [By default, ESP will attempt to reconnect to Wi-Fi network whenever it is disconnected.]
        // (https://arduino-esp8266.readthedocs.io/en/latest/esp8266wifi/station-class.html#start-here)
        delay(500);
        Serial.print(".");
    }
} // void verify_conn()

/// @brief Connect to WiFi.
void join_wifi()
{
    Serial.println(F("connection.hpp:join_wifi"));
    // Make sure WiFi is OFF
    WiFi.mode(WIFI_OFF);
    delay(1000);
    // put ESP in WiFi Station Mode (simple access to WiFi)
    WiFi.mode(WIFI_STA);

    WiFi.begin(ssid, passphrase);

    verify_conn();

    Serial.print(F("Connected to ")); Serial.println(ssid);

    display.clearDisplay();
    display.setCursor(40, 10); display.print(F("WIFI"));
    display.setCursor(10, 38); display.print(F("CONNECTED"));
    display.display();

    Serial.print("ESP8266 IP: "); Serial.println(WiFi.localIP());
} // void join_wifi()

#endif // _CONNECTION_HPP
