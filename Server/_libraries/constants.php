<?php
//----------------------------------------------------------------------------------------
//  Constants and Settings
//----------------------------------------------------------------------------------------
//  Platform: Linux + Apache + PHP
//  Written by Pacess HO
//  Copyright Pacess Studio, 2019-2020.  All rights reserved.
//----------------------------------------------------------------------------------------

$_serverHost = $_SERVER["HTTP_HOST"];
$_serverPath = $_serverHost.$_SERVER["PHP_SELF"];
$_isDevelopment = strpos($_serverPath, "127.0.0.1");

//----------------------------------------------------------------------------------------
//  Encryption related
define("DEFAULT_BASE64", "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/=");
define("CUSTOM_BASE64", "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/=");

//----------------------------------------------------------------------------------------
//  Server-driven settings
if ($_isDevelopment !== false)  {

	//  Development site settings
	define("MYSQL_HOST", "bG9jYWxob3N0");
	define("MYSQL_USER", "bG9jYWxob3N0");
	define("MYSQL_PASSWORD", "bG9jYWxob3N0");
	define("MYSQL_DATABASE", "bG9jYWxob3N0");

}  else  {

	//  Production server settings
	define("MYSQL_HOST", "bG9jYWxob3N0");
	define("MYSQL_USER", "bG9jYWxob3N0");
	define("MYSQL_PASSWORD", "bG9jYWxob3N0");
	define("MYSQL_DATABASE", "bG9jYWxob3N0");
}

?>