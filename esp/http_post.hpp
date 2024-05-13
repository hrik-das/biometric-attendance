#ifndef _HTTP_POST_HPP
#define _HTTP_POST_HPP 1

#include <WiFiClient.h>
#include <ESP8266HTTPClient.h>

extern const String send_url;

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

    return payload;
} // String httpPOST(const String postData)

#endif // _HTTP_POST_HPP
