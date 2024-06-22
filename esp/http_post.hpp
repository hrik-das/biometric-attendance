#ifndef _HTTP_POST_HPP
#define _HTTP_POST_HPP 1

#include <WiFiClient.h>
#include <ESP8266HTTPClient.h>
#include <Adafruit_SSD1306.h>

extern const String send_url;
extern Adafruit_SSD1306 SSD1306;

/// @brief In-house utility (boilerplate reduction) for sending a POST
/// request with the supplied data to the configured remote server. Prints
/// supplied data, return HTTP code, and response payload to Serial Monitor.
/// Sole purpose of this function is to reduce the no. of overall function
/// calls from the compiled binary.
/// @param postData The payload to send with the POST request.
/// @return The response payload.
String httpPOST(const String postData)
{
    WiFiClient wifi;
    HTTPClient http;

    http.begin(wifi, send_url);
    http.addHeader("Content-Type", "application/x-www-form-urlencoded");

    Serial.print(F("Request payload: ")); Serial.println(postData);

    int httpCode = http.POST(postData);
    String payload = http.getString();
    http.end();

    Serial.print(F("HTTP response status code: ")); Serial.println(httpCode);
    Serial.print(F("Response payload: ")); Serial.println(payload);

    if (httpCode != 200) {
        SSD1306.clearDisplay();
        SSD1306.setCursor(28, 10); SSD1306.print(F("REMOTE"));
        SSD1306.setCursor(34, 38); SSD1306.print(F("ERROR"));
        SSD1306.display();
        delay(1000);
    }

    return payload;
} // String httpPOST(const String postData)

#endif // _HTTP_POST_HPP
