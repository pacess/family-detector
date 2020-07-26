<?php
//----------------------------------------------------------------------------------------
//  Submit MAC Addresses
//----------------------------------------------------------------------------------------
//  Written by Pacess HO
//  Copyright Pacess Studio, 2020.  All rights reserved.
//----------------------------------------------------------------------------------------

header("Access-Control-Allow-Origin: https://www.pacess.com");
header("Access-Control-Allow-Methods: POST");
 
//  * Order is important, Pragma must before Expires
header("Content-type: application/json");
header("Cache-Control: no-cache, must-revalidate, no-store, max-age=0");
header("Pragma: no-cache");
header("Expires: Tue, 10 Mar 1987 00:00:00 GMT");
 
date_default_timezone_set("Asia/Hong_Kong");
mb_internal_encoding("UTF-8");
ini_set("memory_limit", "-1");
set_time_limit(60*1);

//----------------------------------------------------------------------------------------
//  * Order is important
require("./_libraries/constants.php");
require("./_libraries/common.php");
require("./_libraries/databaseConnection.php");

require("./_libraries/sensor_mac_addresses.php");

//----------------------------------------------------------------------------------------
//  Global variables
$_responseDictionary = array();

//----------------------------------------------------------------------------------------
function exitWithError($errorCode, $debugInfo="")  {
	$errorDictionary = array(

		//  Parameter related
		"1" => "Parameter not found...",
		"2" => "Parameter not found...",
	);

	$message = $errorDictionary[$errorCode];
	if (strlen($message) == 0)  {

		$message = "Unexpected error...";
		$errorCode = 99;
	}

	$jsonDictionary = array(
		"timestamp" => intval(date("YmdHis")),
		"status" => -$errorCode,
		"message" => "### $message",
	);
	if ($debugInfo != "")  {$jsonDictionary["debugInfo"] = $debugInfo;}
	echo(json_encode($jsonDictionary));
	exit(-$errorCode);
}

//========================================================================================
//  Main program
// file_put_contents("debug.txt", json_encode($_POST)."\n", FILE_APPEND);

$node = $_POST["n"];
$macAddresses = $_POST["m"];

if (empty($node))  {exitWithError(1);}
if (empty($macAddresses))  {exitWithError(2);}

//----------------------------------------------------------------------------------------
$sensor_mac_addresses = new sensor_mac_addresses();

$macDataArray = explode(",", $macAddresses);
$count = count($macDataArray);
$_responseDictionary["count"] = $count;
foreach ($macDataArray as $macData)  {

	$array = explode("|", $macData);
	$size = count($array);

	$macAddress = $array[0];
	$channel = ($size > 1)?intval($array[1]):0;
	$ssid = ($size > 2)?$array[2]:null;

	if ($macAddress == "00:00:00:00:00:00")  {continue;}
	if ($macAddress == "FF:FF:FF:FF:FF:FF")  {continue;}

	$dictionary = array(
		"node"=>$node, "mac_address"=>$macAddress,
		"ssid"=>$ssid, "channel"=>$channel,
	);
	$resultDictionary = $sensor_mac_addresses->updateOrCreateRecord($dictionary);
}

unset($sensor_mac_addresses);  $sensor_mac_addresses = null;

//----------------------------------------------------------------------------------------
//  Output
$_responseDictionary["apiName"] = basename(__FILE__);
$_responseDictionary["timestamp"] = intval(date("YmdHis"));
$_responseDictionary["message"] = "Done";
echo(json_encode($_responseDictionary));

//----------------------------------------------------------------------------------------
//  Finally

?>