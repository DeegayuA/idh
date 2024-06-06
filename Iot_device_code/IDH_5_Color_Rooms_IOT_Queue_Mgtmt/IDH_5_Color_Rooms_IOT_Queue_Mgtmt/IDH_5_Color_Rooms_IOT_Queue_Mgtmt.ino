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

const char* ssid = "ESP32_Hotspot";      // Hotspot SSID
const char* password = "12345678";       // Hotspot password

WebSocketsServer webSocket = WebSocketsServer(82);

const int buttonPins[] = {13, 12, 14, 27, 26};  // GPIO pins for buttons (D13, D12, D14, D27, D26)
const int numButtons = 5;

int buttonStates[numButtons] = {HIGH};  // Array to store button states
int lastButtonStates[numButtons] = {HIGH};
unsigned long lastDebounceTimes[numButtons] = {0};  // Array to store last debounce times
unsigned long debounceDelay = 50;    // the debounce time; increase if the output flickers

unsigned long lastPressTime[numButtons] = {0}; // Array to store the time of the last button press
bool holdTriggered[numButtons] = {false}; // Array to store whether the hold action was triggered
bool doublePressed[numButtons] = {false}; // Array to store whether the double press action was triggered

bool pcConnected = false; // Flag to track PC connection status
bool pcHeartbeat = false; // Flag to track PC heartbeat

const unsigned long doublePressDuration = 1000; // Duration in milliseconds within which a double press is recognized

unsigned long lastPingSent = 0;
const unsigned long pingInterval = 2000; 

void setup() {
  // Initialize buttons
  for (int i = 0; i < numButtons; i++) {
    pinMode(buttonPins[i], INPUT_PULLUP);
  }

  // Initialize serial communication
  Serial.begin(115200);

  // Initialize OLED display
  if(!display.begin(SSD1306_SWITCHCAPVCC, 0x3C)) {
    Serial.println(F("SSD1306 allocation failed"));
    for(;;);
  }
  
  // Display "Welcome" message
  display.clearDisplay();
  display.setTextSize(1);
  display.setTextColor(SSD1306_WHITE);
  display.setCursor(0, 10);
  display.println("Welcome");
  display.display();
  delay(2000);

  // Display "Ready to Connect with PC" message
  display.clearDisplay();
  display.setCursor(0, 10);
  display.println("Ready to Connect");
  display.println("with PC");
  display.display();
  delay(2000);

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

  // Check PC connection status and update OLED display
  if (webSocket.connectedClients() > 0) {
    if (!pcConnected) {
      display.clearDisplay();
      display.setCursor(0, 10);
      display.println("PC Connected");
      display.display();
      pcConnected = true;
    }
  } else {
    if (pcConnected) {
      display.clearDisplay();
      display.setCursor(0, 10);
      display.println("PC Disconnected");
      display.display();
      pcConnected = false;
    }
  }

// if (millis() - webSocket.ping() > 3000) { // Replace pingAlive with ping (if available)
//   pcHeartbeat = false; // No heartbeat received
// } else {
//   pcHeartbeat = true; // Heartbeat received
// }


  // Update OLED display with PC heartbeat status
  display.clearDisplay();
  display.setCursor(0, 10);
  if (pcHeartbeat) {
    display.println("PC Heartbeat: OK");
  } else {
    display.println("PC Heartbeat: No");
  }
  display.display();

  // Read the state of the pushbuttons
  for (int i = 0; i < numButtons; i++) {
    int reading = digitalRead(buttonPins[i]);

    // Check for state change
    if (reading != lastButtonStates[i]) {
      lastDebounceTimes[i] = millis();
    }

    // Debounce button
    if ((millis() - lastDebounceTimes[i]) > debounceDelay) {
      if (reading != buttonStates[i]) {
        buttonStates[i] = reading;

        // Check if button is pressed
        if (buttonStates[i] == LOW) {
          // Calculate time since last press
          unsigned long timeSinceLastPress = millis() - lastPressTime[i];
          
          // Update last press time
          lastPressTime[i] = millis();
        

          // Check if the button was double pressed
          if (timeSinceLastPress < doublePressDuration) {
            doublePressed[i] = true;
          } else {
            doublePressed[i] = false;
          }
          
          // Display pressed button
          display.clearDisplay();
          display.setCursor(0, 10);
          display.print("Button ");
          display.print(i + 1);
         if (doublePressed[i]) {
            display.println(" Double Pressed");
          } else {
            display.println(" Pressed");
          }
          display.display();

          // Send WebSocket message to website with doctor room number
          if (!doublePressed[i]) {
            webSocket.broadcastTXT("{\"action\": \"next_queue\", \"room\": " + String(i + 1) + "}");
          }
          delay(1000); // Delay to show the message for 1 second
        }
      }
    }

    // Update last button state
    lastButtonStates[i] = reading;
  }
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
