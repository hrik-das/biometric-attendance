#ifndef _ADAFRUIT_FINGERPRINT_EXTENDED_HPP
#define _ADAFRUIT_FINGERPRINT_EXTENDED_HPP 1

#include <Adafruit_Fingerprint.h>

/// @brief Gets the command packet
#define BAS_GET_CMD_PACKET(...)                                                       \
  uint8_t data[] = {__VA_ARGS__};                                                     \
  Adafruit_Fingerprint_Packet packet(FINGERPRINT_COMMANDPACKET, sizeof(data), data);  \
  R307.writeStructuredPacket(packet);                                                 \
  if (R307.getStructuredPacket(&packet) != FINGERPRINT_OK)                            \
    return FINGERPRINT_PACKETRECIEVEERR;                                              \
  if (packet.type != FINGERPRINT_ACKPACKET)                                           \
    return FINGERPRINT_PACKETRECIEVEERR;

/// @brief Sends the command packet
#define BAS_SEND_CMD_PACKET(...)                                                      \
  BAS_GET_CMD_PACKET(__VA_ARGS__);                                                    \
  return packet.data[0];

/// @brief Ask the sensor to load a fingerprint model from flash into buffer `num`
/// @param num CharBuffer Number (1 or 2)
/// @param location The model location #
/// @return `FINGERPRINT_OK` on success, `FINGERPRINT_BADLOCATION` if the location
/// is invalid, `FINGERPRINT_PACKETRECIEVEERR` on communication error
uint8_t basLoadModel(Adafruit_Fingerprint& R307, uint8_t num, uint16_t location) {
  Serial.print(__FILE__); Serial.println(F(":basLoadModel"));
  BAS_SEND_CMD_PACKET(FINGERPRINT_LOAD, num, (uint8_t)(location >> 8),
                  (uint8_t)(location & 0xFF));
}

#endif
