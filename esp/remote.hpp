#ifndef _REMOTE_HPP
#define _REMOTE_HPP 1

#include <WiFiClient.h> 
#include <ESP8266HTTPClient.h>
#include <Adafruit_SSD1306.h>

#include "connection.hpp"

extern Adafruit_SSD1306 display;
extern const char* send_url;

enum class Action : uint8_t {
    Enlist,
    Delist,
    Update,
};

/// @brief Checks if the registered `Action` needs to be taken/run.
/// @param action `[IN]` The registered `Action` you're interested to check
/// for. Can be any one of `Action::Enlist`, `Action::Delist` or `Action::Update`.
/// @param loc `[OUT]` Will hold the location where user's fingerprint
/// is to be stored (`Action::Enlist`), deleted from (`Action::Delist`),
/// or updated at (`Action::Update`).
/// @return `true` if he registered `Action` needs to be taken/run,
/// `false` otherwise.
bool check_action(const Action action, uint16_t *loc)
{
    Serial.print(F("remote.hpp:check_action:"));

    String _action;
    switch (action) {
        case Action::Enlist: _action = "enlist";
        case Action::Delist: _action = "delist";
        case Action::Update: _action = "update";
    }
    
    Serial.println(_action);

    WiFiClient client;
    HTTPClient http;
    String postData = "check-" + _action + "=";

    verify_conn();

    http.begin(client, String(send_url));
    http.addHeader("Content-Type", "application/x-www-form-urlencoded");

    int httpCode = http.POST(postData);
    String payload = http.getString();
    http.end();

    Serial.print(F("Request payload:\n")); Serial.println(postData);
    Serial.print(F("HTTP return code: ")); Serial.println(httpCode);

    if (payload.isEmpty())
        // Server did not return anything, nothing to do.
        return false;
    
    // [sensor library : server database] `Fingerprint_ID` (Template
    // Location) mapping is [`N`: `N + 1`]
    *loc = payload.toInt() - 1;

    Serial.print(F("Response payload: [1 + Template Location]: "));
    Serial.println(payload);
    Serial.print(F("User to be ")); Serial.print(_action);
    Serial.print(F("ed at Location: ")); Serial.println(*loc);
    return true;
} // bool check_action(const Action _action, uint16_t *loc)

/// @brief Sends confirmation message regarding whether the requested `Action`
/// was successfully done at location `loc` or not. Call after:
/// `uint8_t enlist (const uint16_t &loc, Adafruit_Fingerprint &sensor)` (in 
/// case of `Action::Enlist`), `uint8_t delist (const uint16_t &loc,
/// Adafruit_Fingerprint &sensor)` (in case of `Action::Delist`), or `uint8_t
/// update (const uint16_t &loc, Adafruit_Fingerprint &sensor)` (in case of
/// `Action::Update`).
/// @param loc `[IN]` Location at which user's fingerprint was to be manipulated.
/// @param success `[IN]` Set to `true` for successful manipulation, `false` otherwise.
void confirm_action(const Action action, const uint16_t loc, bool success)
{
    Serial.print(F("remote_actions.cxx:confirm_action:"));

    String _action;
    switch (action) {
        case Action::Enlist: _action = "enlist";
        case Action::Delist: _action = "delist";
        case Action::Update: _action = "update";
    }

    Serial.println(_action);

    WiFiClient client;
    HTTPClient http;
    String postData = _action + "-";

    Serial.print(F("Sending ")); Serial.print(_action); Serial.print(F(" "));
    if (success) {
        postData += "ok";
        display.clearDisplay();
        display.setCursor(16, 24); display.print(_action);
        display.setCursor(84, 24); display.print(F("!"));
        display.display();
        delay(1000);
        Serial.print(F("SUCCESS"));
    } else {
        postData += "err";
        Serial.print(F("FAILURE"));
    }
    // [sensor library : server database] `Fingerprint_ID` (Template
    // Location) mapping is [`N`: `N + 1`]
    // ultimately sent index is `$(_action)-ok=`/`$(_action)-err=`
    postData += "=" + String(loc + 1);

    Serial.print(F(" message for Location: ")); Serial.println(loc);

    verify_conn();
    
    http.begin(client, String(send_url));
    http.addHeader("Content-Type", "application/x-www-form-urlencoded");

    int httpCode = http.POST(postData);
    String payload = http.getString();
    http.end();

    Serial.print(F("Request payload:\n")); Serial.println(postData);
    Serial.print(F("HTTP return code: ")); Serial.println(httpCode);
} // void confirm_action(const Action action, const uint16_t loc, bool success)

#endif // _REMOTE_HPP
