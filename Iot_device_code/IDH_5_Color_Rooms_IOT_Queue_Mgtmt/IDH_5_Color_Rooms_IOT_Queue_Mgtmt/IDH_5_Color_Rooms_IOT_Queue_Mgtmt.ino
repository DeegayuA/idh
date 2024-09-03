#include "Arduino.h"
#include <Wire.h>
#include <Adafruit_GFX.h>
#include <Adafruit_SSD1306.h>
#include <WiFi.h>
#include <HTTPClient.h>
#include <WebSocketsServer.h>
#include <ArduinoJson.h>
#include <Fonts/FreeSans12pt7b.h>
#include <ESPmDNS.h>
#include <OneButton.h> // Include the OneButton library

#define SCREEN_WIDTH 128
#define SCREEN_HEIGHT 64
#define OLED_RESET -1
Adafruit_SSD1306 display(SCREEN_WIDTH, SCREEN_HEIGHT, &Wire, OLED_RESET);

const char *debugSsid = "EDIC 2";       // Debug WiFi SSID
const char *debugPassword = "00000000"; // Debug WiFi Password
String serverIPAddress = "192.168.137.224";
// String serverIPAddress = "172.16.21.27";

WebSocketsServer webSocket = WebSocketsServer(81);

// Button Pins
#define BUTTON_FORWARD_PIN 13
#define BUTTON_BACKWARD_PIN 12
#define BUTTON_PRESS_PIN 14

// RGB LED Pins
const int LED_PIN_RED = 32;
const int LED_PIN_GREEN = 33;
const int LED_PIN_BLUE = 26;

bool pcConnected = false;

String doctorNames[10];
String doctorColors[10];
int numDoctors = 0;
int currentDoctorIndex = 0;

// Initialize OneButton instances for each button
OneButton forwardButton(BUTTON_FORWARD_PIN, true);
OneButton backwardButton(BUTTON_BACKWARD_PIN, true);
OneButton pressButton(BUTTON_PRESS_PIN, true);

void setup() {
  pinMode(LED_PIN_RED, OUTPUT);
  pinMode(LED_PIN_GREEN, OUTPUT);
  pinMode(LED_PIN_BLUE, OUTPUT);

  Serial.begin(115200);

  if (!display.begin(SSD1306_SWITCHCAPVCC, 0x3C)) {
    Serial.println(F("SSD1306 allocation failed"));
    for (;;)
      ;
  }

  showFullScreenMessage("System Turning On");
  delay(2000);

  showFullScreenMessage("Ready to Connect");
  delay(2000);

  // Connect to the debug WiFi network
  WiFi.begin(debugSsid, debugPassword);
  Serial.print("Connecting to WiFi...");
  while (WiFi.status() != WL_CONNECTED) {
    delay(1000);
    Serial.print(".");
  }
  Serial.println();
  Serial.print("Connected to WiFi. IP address: ");
  Serial.println(WiFi.localIP());

  // Start mDNS service
  if (!MDNS.begin("IDHQ_by_EDIC")) {
    Serial.println("Error setting up MDNS responder!");
    while (1) {
      delay(1000);
    }
  }
  Serial.println("mDNS responder started");

  fetchDoctorData();

  webSocket.begin();
  webSocket.onEvent(webSocketEvent);

  // Attach the callback functions for single and double presses
  forwardButton.attachClick(handleForwardButton);
  backwardButton.attachClick(handleBackwardButton);
  pressButton.attachClick(handleSinglePress);
  pressButton.attachDoubleClick(handleDoublePress);
}

void loop() {
  webSocket.loop();

  forwardButton.tick(); // Handle the forward button events
  backwardButton.tick(); // Handle the backward button events
  pressButton.tick(); // Handle the press button events

  delay(10);
}

void handleForwardButton() {
  currentDoctorIndex = (currentDoctorIndex + 1) % numDoctors;
  Serial.print("Incremented Doctor Index: ");
  Serial.println(currentDoctorIndex);
  showFullScreenMessage(doctorNames[currentDoctorIndex].c_str());
  setColor(doctorColors[currentDoctorIndex].c_str());
}

void handleBackwardButton() {
  currentDoctorIndex = (currentDoctorIndex - 1 + numDoctors) % numDoctors;
  Serial.print("Decremented Doctor Index: ");
  Serial.println(currentDoctorIndex);
  showFullScreenMessage(doctorNames[currentDoctorIndex].c_str());
  setColor(doctorColors[currentDoctorIndex].c_str());
}

void handleSinglePress() {
  Serial.println("Single press detected");
  blinkLED(1, 500);
  webSocket.broadcastTXT("{\"action\": \"notify\", \"press\": \"single\", \"doctorRoomNumber\": " + String(currentDoctorIndex + 1) + "}");
}

void handleDoublePress() {
  Serial.println("Double press detected");
  blinkLED(2, 300);
  webSocket.broadcastTXT("{\"action\": \"next\", \"press\": \"double\", \"doctorRoomNumber\": " + String(currentDoctorIndex + 1) + "}");
}

void blinkLED(int times, int delayTime) {
  for (int i = 0; i < times; i++) {
    digitalWrite(LED_PIN_RED, HIGH);
    digitalWrite(LED_PIN_GREEN, HIGH);
    digitalWrite(LED_PIN_BLUE, HIGH);
    delay(delayTime);
    digitalWrite(LED_PIN_RED, LOW);
    digitalWrite(LED_PIN_GREEN, LOW);
    digitalWrite(LED_PIN_BLUE, LOW);
    delay(delayTime);
  }
}

void showFullScreenMessage(const char *message) {
  display.clearDisplay();
  display.setFont(&FreeSans12pt7b);
  display.setTextColor(SSD1306_WHITE);
  display.setCursor(0, 20);
  display.println(message);
  display.display();
}

void fetchDoctorData() {
  if (WiFi.status() == WL_CONNECTED) {
    HTTPClient http;
    String url = "http://" + String(serverIPAddress) + "/idh.github.io/doctorlist/doc_list.php";
    Serial.println(url);
    http.begin(url);
    Serial.println("HTTP GET request started");
    int httpResponseCode = http.GET();
    Serial.printf("HTTP Response code: %d\n", httpResponseCode);

    if (httpResponseCode == 200) {
      String payload = http.getString();
      Serial.println("HTTP GET request successful");
      Serial.println("Payload: " + payload);
      DynamicJsonDocument doc(2048);
      DeserializationError error = deserializeJson(doc, payload);

      if (error) {
        Serial.print(F("deserializeJson() failed: "));
        Serial.println(error.f_str());
        return;
      }

      numDoctors = doc["data"].size();
      for (int i = 0; i < numDoctors; i++) {
        doctorNames[i] = doc["data"][i]["name"].as<String>();
        doctorColors[i] = doc["data"][i]["color"].as<String>();
      }

      showFullScreenMessage("System Ready");
      delay(2000);

      currentDoctorIndex = 0;
      showFullScreenMessage(doctorNames[currentDoctorIndex].c_str());
      setColor(doctorColors[currentDoctorIndex].c_str());

    } else {
      Serial.printf("HTTP GET request failed: %d\n", httpResponseCode);
      showFullScreenMessage("No web connection");
    }

    http.end();
  } else {
    Serial.println("WiFi not connected");
  }
}

void setColor(const char *color) {
  int red = 255, green = 255, blue = 255;  // Default to white

  if (color[0] == '#' && strlen(color) == 7) {
    // Parse the hex color code
    unsigned long colorValue = strtoul(color + 1, NULL, 16);
    red = (colorValue >> 16) & 0xFF;
    green = (colorValue >> 8) & 0xFF;
    blue = colorValue & 0xFF;
  } else {
    Serial.println("Invalid color format");
    return; // Exit if the format is invalid
  }

  // Normalize and scale factors to enhance the color intensity
  float scale = 1.85; // Increase this value to make colors more vivid
  
  // Calculate scaled values
  int scaledRed = constrain((int)(red * scale), 255, 0);
  int scaledGreen = constrain((int)(green * scale), 255, 0);
  int scaledBlue = constrain((int)(blue * scale), 255, 0);
  
  // Invert the scaled values for common anode RGB LED
  int newRed = 255 - scaledRed;
  int newGreen = 255 - scaledGreen;
  int newBlue = 255 - scaledBlue;

  // Apply the color to the LED
  analogWrite(LED_PIN_RED, newRed);
  analogWrite(LED_PIN_GREEN, newGreen);
  analogWrite(LED_PIN_BLUE, newBlue);
}

void webSocketEvent(uint8_t num, WStype_t type, uint8_t *payload, size_t length) {
  switch (type) {
    case WStype_DISCONNECTED:
      Serial.println("WebSocket Disconnected");
      break;
    case WStype_CONNECTED:
      Serial.println("WebSocket Connected");
      break;
    case WStype_TEXT:
      Serial.printf("WebSocket received text: %s\n", payload);
      break;
  }
}
