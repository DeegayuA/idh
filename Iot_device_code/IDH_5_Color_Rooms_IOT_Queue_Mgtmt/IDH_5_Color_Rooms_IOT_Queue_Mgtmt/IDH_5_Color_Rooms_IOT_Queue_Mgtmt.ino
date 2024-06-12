#include <Wire.h>
#include <Adafruit_GFX.h>
#include <Adafruit_SSD1306.h>
#include <WiFi.h>
#include <HTTPClient.h>
#include <WebSocketsServer.h>
#include <ArduinoJson.h>
#include "AiEsp32RotaryEncoder.h"
#include <Fonts/FreeSans12pt7b.h>

#define SCREEN_WIDTH 128
#define SCREEN_HEIGHT 64
#define OLED_RESET -1
Adafruit_SSD1306 display(SCREEN_WIDTH, SCREEN_HEIGHT, &Wire, OLED_RESET);

const char *ssid = "IDH_QMS_by_EDIC_UOK";
const char *password = "12345678";
const char *debugSsid = "Malitha";       // Debug WiFi SSID
const char *debugPassword = "00000000";  // Debug WiFi Password
String serverIPAddress = "192.168.43.199";

WebSocketsServer webSocket = WebSocketsServer(82);

// Rotary Encoder Pins
#define ROTARY_ENCODER_A_PIN 13
#define ROTARY_ENCODER_B_PIN 12
#define ROTARY_ENCODER_BUTTON_PIN 14
#define ROTARY_ENCODER_VCC_PIN -1
#define ROTARY_ENCODER_STEPS 4

AiEsp32RotaryEncoder rotaryEncoder = AiEsp32RotaryEncoder(ROTARY_ENCODER_A_PIN, ROTARY_ENCODER_B_PIN, ROTARY_ENCODER_BUTTON_PIN, ROTARY_ENCODER_VCC_PIN, ROTARY_ENCODER_STEPS);

// RGB LED Pins
const int LED_PIN_RED = 32;
const int LED_PIN_GREEN = 33;
const int LED_PIN_BLUE = 26;

bool pcConnected = false;

const unsigned long longPressDuration = 500;
unsigned long lastPressTime = 0;

// Simulated database data
String doctorNames[10];
String doctorColors[10];
int numDoctors = 0;
int currentDoctorIndex = 0;

void rotary_onButtonClick() {
    static unsigned long lastTimePressed = 0; // Soft debouncing
    if (millis() - lastTimePressed < 500) {
        return;
    }
    lastTimePressed = millis();
    Serial.print("Button pressed ");
    Serial.print(millis());
    Serial.println(" milliseconds after restart");

    // Handle button press actions here
    handleButtonPress();
}

void rotary_loop() {
    if (rotaryEncoder.encoderChanged()) {
        Serial.print("Value: ");
        Serial.println(rotaryEncoder.readEncoder());
        handleRotaryEncoder();
    }
    if (rotaryEncoder.isEncoderButtonClicked()) {
        rotary_onButtonClick();
    }
}

void IRAM_ATTR readEncoderISR() {
    rotaryEncoder.readEncoder_ISR();
}

void setup() {
    pinMode(LED_PIN_RED, OUTPUT);
    pinMode(LED_PIN_GREEN, OUTPUT);
    pinMode(LED_PIN_BLUE, OUTPUT);

    Serial.begin(115200);

    rotaryEncoder.begin();
    rotaryEncoder.setup(readEncoderISR);

    bool circleValues = false;
    rotaryEncoder.setBoundaries(0, 1000, circleValues);
    rotaryEncoder.setAcceleration(250);

    if (!display.begin(SSD1306_SWITCHCAPVCC, 0x3C)) {
        Serial.println(F("SSD1306 allocation failed"));
        for (;;);
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
    // serverIPAddress = WiFi.localIP().toString();
    fetchDoctorData();

    webSocket.begin();
    webSocket.onEvent(webSocketEvent);
}

void loop() {
    webSocket.loop();

    if (webSocket.connectedClients() > 0) {
        if (!pcConnected) {
            showFullScreenMessage("PC Connected");
            delay(2000);
            pcConnected = true;
            displayDoctorNames();
        }
    } else {
        if (pcConnected) {
            showFullScreenMessage("PC Disconnected");
            delay(2000);
            pcConnected = false;
        }
    }

    rotary_loop();  // Check rotary encoder state
}

void showFullScreenMessage(const char *message) {
    display.clearDisplay();
    display.setFont(&FreeSans12pt7b);
    display.setTextColor(SSD1306_WHITE);
    display.setCursor(0, 20);
    display.println(message);
    display.display();
}

void displayDoctorNames() {
    display.clearDisplay();
    display.setTextSize(1);
    for (int i = 0; i < numDoctors; i++) {
        if (i == currentDoctorIndex) {
            display.setTextColor(SSD1306_WHITE, SSD1306_BLACK);
        } else {
            display.setTextColor(SSD1306_WHITE);
        }
        display.setCursor(0, i * 10);
        display.println(doctorNames[i]);
    }
    display.display();
}

void handleRotaryEncoder() {
    int newPosition = rotaryEncoder.readEncoder();
    if (newPosition > currentDoctorIndex) {
        currentDoctorIndex = (currentDoctorIndex + 1) % numDoctors;
    } else if (newPosition < currentDoctorIndex) {
        currentDoctorIndex = (currentDoctorIndex - 1 + numDoctors) % numDoctors;
    }
    showFullScreenMessage(doctorNames[currentDoctorIndex].c_str());
    setColor(doctorColors[currentDoctorIndex].c_str());
    rotaryEncoder.reset();  // Reset the encoder value to prevent continuous scrolling
}

void handleButtonPress() {
    unsigned long currentTime = millis();
    unsigned long pressDuration = currentTime - lastPressTime;

    if (pressDuration >= longPressDuration) {
        // Long press
        blinkLED(3, 100);
        webSocket.broadcastTXT("{\"action\": \"next\", \"press\": \"long\", \"doctorRoomNumber\": " + String(currentDoctorIndex + 1) + "}");
    } else {
        // Short press
        blinkLED(1, 500);
        webSocket.broadcastTXT("{\"action\": \"notify\", \"press\": \"single\", \"doctorRoomNumber\": " + String(currentDoctorIndex + 1) + "}");
    }

    lastPressTime = currentTime;
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

void webSocketEvent(uint8_t num, WStype_t type, uint8_t *payload, size_t length) {
    switch (type) {
        case WStype_DISCONNECTED:
            Serial.println("WebSocket Disconnected");
            break;
        case WStype_CONNECTED:
            Serial.println("WebSocket Connected");
            break;
        case WStype_TEXT:
            Serial.printf("WebSocket Text: %s\n", payload);
            handleWebSocketMessage(payload);
            break;
        case WStype_BIN:
            Serial.println("WebSocket Binary");
            break;
    }
}

void handleWebSocketMessage(uint8_t *payload) {
    String message = String((char *)payload);
    DynamicJsonDocument doc(1024);
    DeserializationError error = deserializeJson(doc, message);

    if (error) {
        Serial.print(F("deserializeJson() failed: "));
        Serial.println(error.f_str());
        return;
    }

    const char *action = doc["action"];
    if (strcmp(action, "show_color") == 0) {
        const char *color = doc["color"];
        setColor(color);
    }
}

void fetchDoctorData() {
    if (WiFi.status() == WL_CONNECTED) {
        HTTPClient http;
        String url = "http://" + String(serverIPAddress) + "/idh/idh.github.io/doctorlist/doc_list.php";
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

            // Display the first doctor's name and set LED color
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

    struct Color {
        const char *name;
        int red;
        int green;
        int blue;
    };

    const Color colorTable[] = {
        { "red", 255, 0, 0 },
        { "green", 0, 255, 0 },
        { "blue", 0, 0, 255 },
        { "yellow", 255, 255, 0 },
        { "pink", 255, 192, 203 },
        { "orange", 255, 165, 0 },
        { "purple", 128, 0, 128 },
        { "cyan", 0, 255, 255 },
        { "teal", 0, 128, 128 },
        { "magenta", 255, 0, 255 },
        { "darkred", 139, 0, 0 },
        { "darkgreen", 0, 100, 0 },
        { "darkblue", 0, 0, 139 },
        { "darkorange", 255, 140, 0 },
        { "indigo", 75, 0, 130 },
        { "darkcyan", 0, 139, 139 },
        { "darkslategray", 47, 79, 79 },
        { "darkmagenta", 139, 0, 139 },
    };

    for (int i = 0; i < sizeof(colorTable) / sizeof(colorTable[0]); i++) {
        if (strcmp(color, colorTable[i].name) == 0) {
            red = colorTable[i].red;
            green = colorTable[i].green;
            blue = colorTable[i].blue;
            break;
        }
    }

    analogWrite(LED_PIN_RED, red);
    analogWrite(LED_PIN_GREEN, green);
    analogWrite(LED_PIN_BLUE, blue);
}
