#include <Wire.h>
#include <Adafruit_GFX.h>
#include <Adafruit_SSD1306.h>
#include <WiFi.h>
#include <WebServer.h>
#include <WebSocketsServer.h>

#define SCREEN_WIDTH 128 // OLED display width, in pixels
#define SCREEN_HEIGHT 64 // OLED display height, in pixels
#define OLED_RESET    -1 // Reset pin # (or -1 if sharing Arduino reset pin)
Adafruit_SSD1306 display(SCREEN_WIDTH, SCREEN_HEIGHT, &Wire, OLED_RESET);

const char *ssid = "IDH_QMS_by_EDIC_UOK";
const char* password = "12345678";      
String serverIPAddress = "192.168.137.72";
WebSocketsServer webSocket = WebSocketsServer(81);

// RGB LED Pins
const int LED_PIN_RED = 32;
const int LED_PIN_GREEN = 33;
const int LED_PIN_BLUE = 26;

const int buttonPin1 = 12;  // GPIO pin for button 1
const int buttonPin2 = 13;  // GPIO pin for button 2

int buttonState1 = 0;       // Variable for reading the pushbutton status
int buttonState2 = 0;
int lastButtonState1 = HIGH;
int lastButtonState2 = HIGH;

unsigned long lastDebounceTime1 = 0;  // the last time the output pin was toggled
unsigned long lastDebounceTime2 = 0;  // the last time the output pin was toggled
unsigned long debounceDelay = 50;    // the debounce time; increase if the output flickers

void setup() {
  // Initialize buttons
  pinMode(buttonPin1, INPUT_PULLUP);
  pinMode(buttonPin2, INPUT_PULLUP);

  // Initialize serial communication
  Serial.begin(115200);

  // Initialize OLED display with I2C address 0x3C
  if(!display.begin(SSD1306_SWITCHCAPVCC, 0x3C)) {
    Serial.println(F("SSD1306 allocation failed"));
    for(;;);
  }

  // Clear the buffer
  display.clearDisplay();
  display.setTextSize(1);
  display.setTextColor(SSD1306_WHITE);
  
  // Display initial message
  display.setCursor(0, 10);
  display.print("Hello");
  display.display();
  delay(2000); // Display "Hello" for 2 seconds

  // Clear the buffer and show the next prompt
  display.clearDisplay();
  display.setCursor(0, 10);
  display.print("Press a button");
  display.display();

  // Set up WiFi as access point
  WiFi.softAP(ssid, password);
  IPAddress IP = WiFi.softAPIP();
  Serial.print("AP IP address: ");
  Serial.println(IP);

  // WebSocket server setup
  webSocket.begin();
  webSocket.onEvent(webSocketEvent);
}

void loop() {
  webSocket.loop();

  // Read the state of the pushbuttons
  int reading1 = digitalRead(buttonPin1);
  int reading2 = digitalRead(buttonPin2);

  // Check for button 1 state change
  if (reading1 != lastButtonState1) {
    lastDebounceTime1 = millis();
  }
  
  // Check for button 2 state change
  if (reading2 != lastButtonState2) {
    lastDebounceTime2 = millis();
  }

  // Debounce button 1
  if ((millis() - lastDebounceTime1) > debounceDelay) {
    if (reading1 != buttonState1) {
      buttonState1 = reading1;

      // Check if button 1 is pressed
      if (buttonState1 == LOW) {
        display.clearDisplay();
        display.setCursor(0, 10);
        display.print("Calling next patient");
        display.display();
        webSocket.broadcastTXT("{\"action\": \"next_queue\"}");
      }
    }
  }

  // Debounce button 2
  if ((millis() - lastDebounceTime2) > debounceDelay) {
    if (reading2 != buttonState2) {
      buttonState2 = reading2;

      // Check if button 2 is pressed
      if (buttonState2 == LOW) {
        display.clearDisplay();
        display.setCursor(0, 10);
        display.print("Notifying again");
        display.display();
        webSocket.broadcastTXT("{\"action\": \"notify\"}");
      }
    }
  }

  // If no buttons are pressed
  if (buttonState1 == HIGH && buttonState2 == HIGH) {
    display.clearDisplay();
    display.setCursor(0, 10);
    display.print("Press a button");
    display.display();
  }

  // If both buttons are pressed
  if (buttonState1 == LOW && buttonState2 == LOW) {
    display.clearDisplay();
    display.setCursor(0, 10);
    display.print("Both buttons pressed");
    display.display();
  }

  // Update the last button states
  lastButtonState1 = reading1;
  lastButtonState2 = reading2;
}

void webSocketEvent(uint8_t num, WStype_t type, uint8_t * payload, size_t length) {
  switch (type) {
    case WStype_DISCONNECTED:
      Serial.println("WebSocket Disconnected");
      break;
    case WStype_CONNECTED:
      Serial.println("WebSocket Connected");
      break;
    case WStype_TEXT:
      Serial.printf("WebSocket Text: %s\n", payload);
      break;
    case WStype_BIN:
      Serial.println("WebSocket Binary");
      break;
  }
}
