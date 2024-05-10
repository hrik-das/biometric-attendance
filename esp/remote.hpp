#ifndef _REMOTE_HPP
#define _REMOTE_HPP 1

#include <Adafruit_SSD1306.h>

#include "http_post.hpp"
#include "connection.hpp"

extern Adafruit_SSD1306 SSD1306;

/// @brief Denotes any of the special operations enlist, delist, or update
/// that needs to be done for a user.
enum class Operation : u8 {
    /// @brief Denotes storing a fingerprint in the sensor library.
    Enlist,
    /// @brief Denotes deleting a fingerprint from the sensor library.
    Delist,
    /// @brief Denotes updating a fingerprint in the sensor library.
    Update,
};

/// @brief Checks if an `Operation` needs to be done.
/// @param op `[OUT]` Will hold the operation that needs to be done (either
/// `Operation::Enlist`, `Operation::Delist`, or `Operation::Update`).
/// @param loc `[OUT]` Will hold the location where user's fingerprint
/// is to be stored at (`Operation::Enlist`), deleted from (`Operation::Delist`),
/// or updated at (`Operation::Update`).
/// @param roll `[OUT]` Will hold the Roll No. of concerned user.
/// @return `true` if an `Operation` needs to be done, `false` otherwise.
bool check_EDU(Operation *op, u16 *loc, String *roll)
{
    Serial.println(F("remote.hpp:check_EDU"));

    String postData = "check-edu=";

    verify_conn();

    String payload = httpPOST(postData);

    if (payload.isEmpty())
        // Server did not return anything, nothing to do.
        return false;

    *op = [&payload] { switch (payload.substring(0, 1)[0]) {
        case 'E': return Operation::Enlist;
        case 'D': return Operation::Delist;
        case 'U': return Operation::Update;
        default: std::abort();
        }} ();

    u8 idx_roll = payload.indexOf('R');

    // [sensor library : server database] `Fingerprint_ID` (Template
    // Location) mapping is [`N`: `N + 1`]
    *loc = payload.substring(1, idx_roll).toInt() - 1;
    *roll = payload.substring(idx_roll + 1);

    Serial.print([&op] { switch (*op) {
        case Operation::Enlist: return "Enlist";
        case Operation::Delist: return "Delist";
        case Operation::Update: return "Update";
        default: std::abort();
    }} ());
    Serial.print(F(" fingerprint of Roll No. ")); Serial.print(*roll);
    Serial.print(F(" at Location: ")); Serial.println(*loc);
    
    return true;
} // bool check_EDU(Operation *op, u16 *loc, u16 *roll)

/// @brief Sends confirmation message regarding whether the requested `Operation`
/// for Roll No. `roll` was successfully done at location `loc` or not. Call after:
/// `u8 enlist (const u16 &loc, Adafruit_Fingerprint &sensor)` (in case of 
/// `Operation::Enlist`), `u8 delist (const u16 &loc, Adafruit_Fingerprint &sensor)`
/// (in case of `Operation::Delist`), or `u8 update (const u16 &loc, Adafruit_Fingerprint
/// &sensor)` (in case of `Operation::Update`).
/// @param op `[IN]` `Operation` whose confirmation status is to be sent
/// @param loc `[IN]` Location at which user's fingerprint was to be manipulated.
/// @param roll `[IN]` Roll No. of the user whose fingerprint was to be manipulated.
/// @param result `[IN]` Set to `FINGERPRINT_OK` for successful manipulation, NOT
/// `FINGERPRINT_OK` otherwise.
void confirm_EDU(const Operation op, const u16 loc, const String roll, const u8 result)
{
    Serial.print(F("remote.hpp:confirm_EDU:"));

    String operation = [&op] { switch (op) {
        case Operation::Enlist: return "ENLIST";
        case Operation::Delist: return "DELIST";
        case Operation::Update: return "UPDATE";
        default: std::abort();
        }} ();

    Serial.println(operation);

    String postData = "confirm-edu=";

    SSD1306.clearDisplay();
    SSD1306.setCursor(28, 2); SSD1306.print(operation);
    SSD1306.setCursor(22, 20);
    Serial.print(F("Sending ")); Serial.print(operation); Serial.print(F(" "));

    if (result) { /* != FINGERPRINT_OK */
        postData += "err";
        SSD1306.print(F("FAILURE")); Serial.print(F("FAILURE"));
    } else {
        postData += "ok";
        SSD1306.print(F("SUCCESS")); Serial.print(F("SUCCESS"));
    }
    
    SSD1306.setTextSize(3);
    SSD1306.setCursor((128 - 18 * (roll.length() + 1)) >> 1, 38);
    SSD1306.print(F("#")); SSD1306.print(roll);
    SSD1306.display();
    SSD1306.setTextSize(2);
    Serial.print(F(" message for Roll No. ")); Serial.print(roll);
    Serial.print(F(" at Location: ")); Serial.println(loc);

    verify_conn();
    
    (void) httpPOST(postData);

    delay(2000);
} // void confirm_EDU(const Operation op, const u16 loc, const String roll, const u8 result)

#endif // _REMOTE_HPP
