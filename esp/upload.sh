#!/usr/bin/bash

arduino-cli upload -p /dev/ttyACM0 --fqbn esp8266:esp8266:nodemcuv2 .
