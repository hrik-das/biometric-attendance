#!/usr/bin/bash

# arduino-cli upload -p /dev/ttyACM0 --fqbn esp8266:esp8266:nodemcuv2 .
arduino-cli upload -p COM3 --fqbn esp8266:esp8266:nodemcuv2 .
