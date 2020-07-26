//----------------------------------------------------------------------------------------
//  MAC Address Detector for Family
//----------------------------------------------------------------------------------------
//  Written by Pacess HO
//  Copyright Pacess Studio, 2020.  All rights reserved.
//----------------------------------------------------------------------------------------

//  Fingerprints
//  https://www.grc.com/fingerprints.htm

#include <ESP8266WiFi.h>
#include <ESP8266HTTPClient.h>
#include <WiFiClient.h>
#include <Ticker.h>

#include "./esppl_functions.h"

//  How many MAC addresses can be stored
#define MAC_BUFFER_SIZE 127
#define SNIFFING_SECONDS 60

//----------------------------------------------------------------------------------------
//  Global variables
const char *_serverPath = "http://192.168.1.1/api/sensor/mac-address/";

const char *_ssid = "Your SSID";
const char *_password = "Your Password";

uint8 _currentIndex = 0;
uint8_t _detectedMACAddressArray[MAC_BUFFER_SIZE][ESPPL_MAC_LEN];
uint8_t _detectedSSIDArray[MAC_BUFFER_SIZE][ESPPL_SSID_LEN];
unsigned _detectedChannelArray[MAC_BUFFER_SIZE];

Ticker _ticker;
bool _sniffing;
uint8 _newMACCount;

//----------------------------------------------------------------------------------------
bool compareMAC(uint8_t *sourceMAC, uint8_t *targetMAC)  {
	for (int j=0; j<ESPPL_MAC_LEN; j++)  {
		if (sourceMAC[j] != targetMAC[j])  {return false;}
	}
	return true;
}

//----------------------------------------------------------------------------------------
//  Return:
//  0~63 = Index
//  64 = Not found
uint8_t findMACAtIndex(uint8_t *sourceMAC)  {
	for (int i=0; i<_currentIndex; i++)  {
		if (compareMAC(sourceMAC, _detectedMACAddressArray[i]))  {
			return i;
		}
	}
	return MAC_BUFFER_SIZE;
}

//----------------------------------------------------------------------------------------
void addMACAddressToIndex(uint8_t *sourceMAC, uint8_t index)  {
	if (index >= MAC_BUFFER_SIZE)  {return;}

	for (int j=0; j<ESPPL_MAC_LEN; j++)  {
		_detectedMACAddressArray[index][j] = sourceMAC[j];
	}
}

//----------------------------------------------------------------------------------------
void addSSIDToIndex(uint8_t *ssid, int length, uint8_t index)  {
	if (index >= MAC_BUFFER_SIZE)  {return;}

	for (int j=0; j<ESPPL_SSID_LEN-1; j++)  {
		_detectedSSIDArray[index][j] = 0;
	}

	for (int j=0; j<length; j++)  {
		_detectedSSIDArray[index][j] = ssid[j];
	}
}

//----------------------------------------------------------------------------------------
void macDidDetected(esppl_frame_info *info)  {
	if (_currentIndex >= MAC_BUFFER_SIZE)  {return;}

	if (findMACAtIndex(info->sourceaddr) == MAC_BUFFER_SIZE)  {

		addMACAddressToIndex(info->sourceaddr, _currentIndex);
		addSSIDToIndex(info->ssid, info->ssid_length, _currentIndex);
		_detectedChannelArray[_currentIndex] = info->channel;

		_currentIndex++;
		_newMACCount++;
	}

	if (findMACAtIndex(info->receiveraddr) == MAC_BUFFER_SIZE)  {

		addMACAddressToIndex(info->receiveraddr, _currentIndex);
		addSSIDToIndex(info->ssid, info->ssid_length, _currentIndex);
		_detectedChannelArray[_currentIndex] = info->channel;

		_currentIndex++;
		_newMACCount++;
	}
}

//----------------------------------------------------------------------------------------
String getMACString(int index)  {
	if (index >= MAC_BUFFER_SIZE)  {return "";}

	char hexValue[3];
	String address = "";
	String separator = "";	
	for (int k=0; k<ESPPL_MAC_LEN; k++)  {

		uint8_t value = _detectedMACAddressArray[index][k];
		sprintf(hexValue, "%02X", value);

		address += separator+String(hexValue);
		separator = ":";
	}
	return address;
}

//----------------------------------------------------------------------------------------
String getSSIDString(int index)  {
	if (index >= MAC_BUFFER_SIZE)  {return "";}

	char *ssid = (char *)_detectedSSIDArray[index];
	String output = String(ssid);
	return output;
}

//----------------------------------------------------------------------------------------
void stopSniffing()  {
	_sniffing = false;
}

//----------------------------------------------------------------------------------------
void setup()  {

	//  Wait 1 second
	delay(1000);

	//  Connect serial port
	Serial.begin(115200);

	//  Wait 3 seconds
	delay(3000);
}

//----------------------------------------------------------------------------------------
void loop() {

	//  Initialize ESPPL library and sets the ESP8266 in promiscuous mode
	Serial.print("Switch to promiscuous mode...");
	esppl_init(macDidDetected);
	Serial.println("OK");

	//  Reset buffer
	_currentIndex = 0;
	for (int i=0; i<MAC_BUFFER_SIZE; i++)  {
		_detectedMACAddressArray[i][0] = 0;
	}

	//  Detect for 60 seconds
	_ticker.attach(SNIFFING_SECONDS, stopSniffing);
	_sniffing = true;

	//----------------------------------------------------------------------------------------
	//  Detect MAC addresses
	Serial.print("Start sniffing.");
	esppl_sniffing_start();
	while (_sniffing == true)  {

		//  Loop all WiFi channels
		_newMACCount = 0;
		for (int i=ESPPL_CHANNEL_MIN; i<=ESPPL_CHANNEL_MAX; i++)  {

			esppl_set_channel(i);
			while (esppl_process_frames())  {

				//  Do nothing
			}
		}

		if (_newMACCount == 0)  {Serial.print(".");}
		else if (_newMACCount <= 9)  {Serial.print(_newMACCount);}
		else  {Serial.print("@");}
	}
	_ticker.detach();

	esppl_sniffing_stop();

	//  Above stop command only set flag, so need to handle by myself
	wifi_station_disconnect();
	wifi_set_opmode(STATION_MODE);
	wifi_promiscuous_enable(false);

	Serial.println("Done");

	//  Wait 3 seconds before connecting WiFi
	delay(3000);

	//----------------------------------------------------------------------------------------
	//  Switch to station mode
	Serial.print("Switch to station mode.");
	WiFi.begin(_ssid, _password);

	while (WiFi.status() != WL_CONNECTED)  {
		delay(500);
		Serial.print(".");
	}
	Serial.print(WiFi.localIP());
	Serial.println("...Done");

	//----------------------------------------------------------------------------------------
	//  Send those detected MAC addresses to server
	Serial.print("Sending data.");

	HTTPClient http;
	http.begin(_serverPath);
	http.addHeader("Content-Type", "application/x-www-form-urlencoded");
	Serial.print(".");

	//  Data to send with HTTP POST
	String separator = "";
	String requestData = "n=esp8266-1&m=";
	for (int i=0; i<_currentIndex; i++)  {

		String macAddress = getMACString(i);
		String ssid = String(getSSIDString(i));
		String channel = String(_detectedChannelArray[i]);

		requestData += separator+macAddress+"|"+channel+"|"+ssid;
		separator = ",";
	}
	Serial.print(".");

	//  Send HTTP POST request
	int responseCode = http.POST(requestData);
	if (responseCode >= 0)  {
		Serial.print(responseCode);
	}  else  {
		Serial.print(http.errorToString(responseCode).c_str());
	}

	//  Free resources
	http.end();
	WiFi.disconnect();
	Serial.println("...Done\n");
}
