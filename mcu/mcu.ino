#include <SPI.h>
#include <Wire.h>
#include <WiFiClient.h>
#include <ESP8266WiFi.h>
#include <SoftwareSerial.h>
#include <ESP8266WebServer.h>
#include <ESP8266HTTPClient.h>

#include <Adafruit_GFX.h>
#include <Adafruit_SSD1306.h>
#include <Adafruit_Fingerprint.h>

#define FINGERPRINT_RX 0 // D3
#define FINGERPRINT_TX 2 // D4

#define SCREEN_WIDTH 128
#define SCREEN_HEIGHT 64
#define OLED_RESET 0

Adafruit_SSD1306 display(SCREEN_WIDTH, SCREEN_HEIGHT, &Wire, OLED_RESET);

SoftwareSerial mySerial(FINGERPRINT_RX, FINGERPRINT_TX);

Adafruit_Fingerprint finger = Adafruit_Fingerprint(&mySerial);

// WiFi name and password
const char *ssid = "oneplus";
const char *passphrase = "m3furyys";

String postData;
// IP or hostname of machine the server is running on
String server = "Godlike-PC";
// path to the resource on the server that will process icoming requests from the MCU
String path_to_resource = "PHP/biometric-attendance/getdata.php";
// Fully qualified URL where requests are sent to
String send_url = "http://" + server + "/" + path_to_resource;

int fingerprint_id = 0;

uint8_t id;

#include "bitmaps.h"

void setup() {

    Serial.begin(115200);
    if (!display.begin(SSD1306_SWITCHCAPVCC, 0x3C))
    { // Address 0x3D for 128x64
        Serial.println(F("SSD1306 allocation failed"));
        for (;;)
            ; // Don't proceed, loop forever
    }

    display.display(); delay(2000); display.clearDisplay();

    connect_to_wifi();
    
    finger.begin(57600);
    
    Serial.println("\n\nAdafruit finger detect test");

    if (finger.verifyPassword()) {
        Serial.println("Found fingerprint sensor!");
        display.clearDisplay(); display.drawBitmap(34, 0, FINGERPRINT_VALID_BITMAP, FINGERPRINT_MSG_EDGE, FINGERPRINT_MSG_EDGE, WHITE); display.display();
    } else {
        Serial.println("Did not find fingerprint sensor :(");
        display.clearDisplay(); display.drawBitmap(32, 0, FINGERPRINT_FAILED_BITMAP, FINGERPRINT_MSG_EDGE, FINGERPRINT_MSG_EDGE, WHITE); display.display();
        while (1) {
            delay(1);
        }
    }

    finger.getTemplateCount();
    
    Serial.print("Sensor contains: "); Serial.print(finger.templateCount); Serial.println(" templates");
    Serial.println("Waiting for valid finger...");
}

void loop() {

    if (WiFi.status() != WL_CONNECTED)
        connect_to_wifi();

    fingerprint_id = get_fingerprint_id();
    delay(50);

    // `fingerprint_id` used in this function
    display_fingerprint_id();
    
    check_to_add_id();
    
    check_to_delete_id();
}

int get_fingerprint_id() {

    uint8_t p = finger.getImage();

    switch (p) {
        case FINGERPRINT_OK:        break;      // Serial.println("Image taken");
        case FINGERPRINT_NOFINGER:  return 0;   // Serial.println("No finger detected");
        default:                    return -2;  // Serial.println("Unknown error");
    } // OK success!

    p = finger.image2Tz();
    
    switch (p) {
        case FINGERPRINT_OK:        break;      // Serial.println("Image converted");
        case FINGERPRINT_IMAGEMESS: return -1;  // Serial.println("Image too messy");
        default:                    return -2;  // Serial.println("Unknown error");
    } // OK converted!
    
    p = finger.fingerFastSearch();

    switch (p) {
        case FINGERPRINT_OK:        break;
        case FINGERPRINT_NOTFOUND:  return -1;
        default:                    return -2;
    } // OK found!

    return finger.fingerID;
}

void display_fingerprint_id() {
    
    display.clearDisplay();

    switch (fingerprint_id) {
        case  0:    display.drawBitmap(32, 0, FINGERPRINT_START_BITMAP, FINGERPRINT_MSG_EDGE, FINGERPRINT_MSG_EDGE, WHITE);     display.display();  return;
        case -1:    display.drawBitmap(34, 0, FINGERPRINT_INVALID_BITMAP, FINGERPRINT_MSG_EDGE, FINGERPRINT_MSG_EDGE, WHITE);   display.display();  return;
        case -2:    display.drawBitmap(32, 0, FINGERPRINT_FAILED_BITMAP, FINGERPRINT_MSG_EDGE, FINGERPRINT_MSG_EDGE, WHITE);    display.display();  return;
        default:    if (fingerprint_id < 0) return;
                    display.drawBitmap(34, 0, FINGERPRINT_VALID_BITMAP, FINGERPRINT_MSG_EDGE, FINGERPRINT_MSG_EDGE, WHITE); display.display();
                    send_fingerprint_id(fingerprint_id);
    }
}

void send_fingerprint_id(int id) {

    WiFiClient client;
    HTTPClient http;
    
    postData = "fingerprint_id=" + String(id);
    
    http.begin(client, send_url);
    http.addHeader("Content-Type", "application/x-www-form-urlencoded");

    int httpCode = http.POST(postData); // Send the request
    String payload = http.getString();  // Get the response payload

    Serial.println(httpCode); // Print HTTP return code
    Serial.println(payload);  // Print response payload
    Serial.println(postData); // Post Data
    Serial.println(id);   // Print fingerprint ID

    display.clearDisplay(); display.setTextSize(2); display.setTextColor(WHITE);

    if (payload.substring(0, 5) == "login") {
        String user_name = payload.substring(5);
        display.setCursor(15, 0); display.print(F("Welcome")); display.setCursor(0, 20);
        display.print(user_name);   // Serial.println(user_name);

    } else if (payload.substring(0, 6) == "logout") {
        String user_name = payload.substring(6);
        display.setCursor(10, 0); display.print(F("Good Bye")); display.setCursor(0, 20);
        display.print(user_name);   // Serial.println(user_name);
    }

    display.display();
    delay(1000);

    postData = "";
    http.end();
}


//******************Check if there a Fingerprint ID to add******************
void check_to_add_id() {

    WiFiClient client;
    HTTPClient http;
    postData = "Get_Fingerid=get_id"; // Add the Fingerprint ID to the Post array in order to send it

    http.begin(client, send_url); // initiate HTTP request, put your Website URL or Your Computer IP
    http.addHeader("Content-Type", "application/x-www-form-urlencoded"); // Specify content-type header

    int httpCode = http.POST(postData); // Send the request
    String payload = http.getString();  // Get the response payload

    if (payload.substring(0, 6) == "add-id") {

        String add_id = payload.substring(6);
        Serial.println(add_id);
        id = add_id.toInt();
        get_fingerprint_enroll();
    }

    http.end();
}

//******************Enroll a Finpgerprint ID*****************
// uint8_t get_fingerprint_enroll() {
void get_fingerprint_enroll() {
    // RUN 1
    if (process_image(1) != 0)
        return;

    display.clearDisplay(); display.setTextSize(2); display.setTextColor(WHITE); display.setCursor(0, 0);
    display.print(F("Remove")); display.setCursor(0, 20); display.print(F("finger"));
    display.display(); Serial.println("Remove finger");
    
    delay(2000);
    
    int p = 0;
    // Ensure that finger is taken off the sensor before RUN 2
    while (p != FINGERPRINT_NOFINGER)
        p = finger.getImage();

    Serial.print("ID "); Serial.println(id);

    // RUN 2
    if (process_image(2) != 0)
        return;

    Serial.print("Creating model for #"); Serial.println(id);

    p = finger.createModel();

    switch (p) {
        case FINGERPRINT_OK:                Serial.println("Model created!"); display.clearDisplay(); display.drawBitmap(34, 0, FINGERPRINT_VALID_BITMAP, FINGERPRINT_MSG_EDGE, FINGERPRINT_MSG_EDGE, WHITE); display.display(); break;
        case FINGERPRINT_PACKETRECIEVEERR:  Serial.println("Communication error");          return;
        case FINGERPRINT_ENROLLMISMATCH:    Serial.println("Fingerprints did not match");   return;
        default:                            Serial.println("Unknown error");
    }

    Serial.print("ID "); Serial.println(id);
    
    p = finger.storeModel(id);
    
    switch (p) {
        case FINGERPRINT_OK:                Serial.println("Stored!"); display.clearDisplay(); display.drawBitmap(34, 0, FINGERPRINT_VALID_BITMAP, FINGERPRINT_MSG_EDGE, FINGERPRINT_MSG_EDGE, WHITE); display.display();
                                            confirm_adding();                                   break;
        case FINGERPRINT_PACKETRECIEVEERR:  Serial.println("Communication error");              return;
        case FINGERPRINT_BADLOCATION:       Serial.println("Could not store in that location"); return;
        case FINGERPRINT_FLASHERR:          Serial.println("Error writing to flash");           return;
        default:                            Serial.println("Unknown error");
    }
}

uint8_t process_image(uint8_t run_count) {
    int p = -1;

    display.clearDisplay(); display.drawBitmap(34, 0, FINGERPRINT_SCAN_BITMAP, FINGERPRINT_MSG_EDGE, FINGERPRINT_MSG_EDGE, WHITE); display.display();

    while (p != FINGERPRINT_OK) {
        p = finger.getImage();

        switch (p) {
            case FINGERPRINT_OK:                Serial.println("Image taken"); display.clearDisplay(); display.drawBitmap(34, 0, FINGERPRINT_VALID_BITMAP, FINGERPRINT_MSG_EDGE, FINGERPRINT_MSG_EDGE, WHITE); display.display(); break;
            case FINGERPRINT_NOFINGER:          display.setTextSize(1); display.setTextColor(WHITE); display.setCursor(0, 0);
                                                display.print(F("scanning")); display.display(); break;
            case FINGERPRINT_PACKETRECIEVEERR:  Serial.println("Communication error"); display.clearDisplay(); display.drawBitmap(34, 0, FINGERPRINT_INVALID_BITMAP, FINGERPRINT_MSG_EDGE, FINGERPRINT_MSG_EDGE, WHITE); display.display(); break;
            case FINGERPRINT_IMAGEFAIL:         Serial.println("Imaging error"); break;
            default:                            Serial.println("Unknown error");
        }
    } // OK success!

    p = finger.image2Tz(run_count);

    switch (p) {
        case FINGERPRINT_OK:                Serial.println("Image converted"); display.clearDisplay(); display.drawBitmap(34, 0, FINGERPRINT_VALID_BITMAP, FINGERPRINT_MSG_EDGE, FINGERPRINT_MSG_EDGE, WHITE); display.display();
                                            return 0;
        case FINGERPRINT_IMAGEMESS:         Serial.println("Image too messy"); display.clearDisplay(); display.drawBitmap(34, 0, FINGERPRINT_INVALID_BITMAP, FINGERPRINT_MSG_EDGE, FINGERPRINT_MSG_EDGE, WHITE); display.display();
                                            return 1;
        case FINGERPRINT_PACKETRECIEVEERR:  Serial.println("Communication error");
                                            return 1;
        case FINGERPRINT_FEATUREFAIL:       Serial.println("Could not find fingerprint features");
                                            return 1;
        case FINGERPRINT_INVALIDIMAGE:      Serial.println("Could not find fingerprint features");
                                            return 1;
        default:                            Serial.println("Unknown error");
                                            return 1;
    }
}

//******************Check if there a Fingerprint ID to add******************
void confirm_adding() {

    WiFiClient client;
    HTTPClient http;
    postData = "confirm_id=" + String(id); // Add the Fingerprint ID to the Post array in order to send it

    http.begin(client, send_url); // initiate HTTP request, put your Website URL or Your Computer IP
    http.addHeader("Content-Type", "application/x-www-form-urlencoded"); // Specify content-type header

    int httpCode = http.POST(postData); // Send the request
    String payload = http.getString();  // Get the response payload

    display.clearDisplay(); display.setTextSize(1.5); display.setTextColor(WHITE); display.setCursor(0, 0);
    
    display.print(payload);
    
    display.display(); delay(1000);

    Serial.println(payload);

    http.end();
}

//******************Check if there a Fingerprint ID to delete******************
void check_to_delete_id() {

    WiFiClient client;
    HTTPClient http;
    
    postData = "DeleteID=check"; // Add the Fingerprint ID to the Post array in order to send it

    http.begin(client, send_url);                                            // initiate HTTP request, put your Website URL or Your Computer IP
    http.addHeader("Content-Type", "application/x-www-form-urlencoded"); // Specify content-type header

    int httpCode = http.POST(postData); // Send the request
    String payload = http.getString();  // Get the response payload

    if (payload.substring(0, 6) == "del-id") {
        String del_id = payload.substring(6);
        Serial.println(del_id);
        delete_fingerprint(del_id.toInt());
    }

    http.end();
}

//******************Delete Finpgerprint ID*****************
// uint8_t delete_fingerprint(int id) {
void delete_fingerprint(int id) {
    int p = -1;

    display.clearDisplay(); display.setTextColor(WHITE); display.setCursor(0, 0); display.setTextSize(1);

    p = finger.deleteModel(id);

    switch (p) {
        case FINGERPRINT_OK:                display.print(F("Deleted!\n"));                             break; // Serial.println("Deleted!");
        case FINGERPRINT_PACKETRECIEVEERR:  display.print(F("Communication error!\n"));                 break; // Serial.println("Communication error");
        case FINGERPRINT_BADLOCATION:       display.print(F("Could not delete in that location!\n"));   break; // Serial.println("Could not delete in that location");
        case FINGERPRINT_FLASHERR:          display.print(F("Error writing to flash!\n"));              break; // Serial.println("Error writing to flash");
        default:                            display.print(F("Unknown error:\n"));                              // Serial.print("Unknown error: 0x"); Serial.println(p, HEX);
    }

    display.display();
}

void connect_to_wifi() {

    WiFi.mode(WIFI_OFF); // Prevents reconnection issue (taking too long to connect)
    delay(1000);
    WiFi.mode(WIFI_STA);

    Serial.print("Connecting to "); Serial.println(ssid);

    WiFi.begin(ssid, passphrase);

    display.clearDisplay(); display.setTextSize(1); display.setTextColor(WHITE); display.setCursor(0, 0);

    display.print(F("Connecting to \n"));
    
    display.setCursor(0, 50); display.setTextSize(2);

    display.print(ssid);
    display.drawBitmap(73, 10, WIFI_START_BITMAP, WIFI_START_WIDTH, WIFI_START_HEIGHT, WHITE); display.display();

    while (WiFi.status() != WL_CONNECTED) {
        delay(500);
        Serial.print(".");
    }
    
    Serial.println(""); Serial.println("Connected");

    display.clearDisplay(); display.setTextSize(2); display.setTextColor(WHITE); display.setCursor(8, 0);

    display.print(F("Connected \n"));

    display.drawBitmap(33, 15, WIFI_CONNECTED_BITMAP, WIFI_CONNECTED_WIDTH, WIFI_CONNECTED_HEIGHT, WHITE);
    
    display.display();

    Serial.print("IP address: "); Serial.println(WiFi.localIP()); // IP address assigned to your ESP
}
