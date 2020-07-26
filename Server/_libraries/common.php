<?php
//----------------------------------------------------------------------------------------
//  Common Functions
//----------------------------------------------------------------------------------------
//  Platform: Linux + Apache + PHP
//  Written by Pacess HO
//  Copyright Pacess Studio, 2019-2020.  All rights reserved.
//----------------------------------------------------------------------------------------

//----------------------------------------------------------------------------------------
function encodeCustomBase64($raw)  {
	$base64 = base64_encode($raw);
	$customBase64 = strtr($base64, DEFAULT_BASE64, CUSTOM_BASE64);
	return $customBase64;
}

//----------------------------------------------------------------------------------------
function decodeCustomBase64($customBase64)  {
	$base64 = strtr($customBase64, CUSTOM_BASE64, DEFAULT_BASE64);
	$raw = base64_decode($base64);
	return $raw;
}

//----------------------------------------------------------------------------------------
function encodeCaesar($raw)  {
	global $_caesarKey;

    $hex = '';
	$j = 0;    
    for ($i=0; $i<strlen($raw); $i++)  {
    
    	if ($j >= strlen($_caesarKey))  {$j = 0;}
    	
    	$asciiValue = ord($raw[$i]);
		$asciiKey = ord($_caesarKey[$j]);
		$asciiValue ^= $asciiKey;

		if ($asciiValue < 16)  {$hex .= "0";}

        $hex .= dechex($asciiValue);
        $j++;
    }
    return $hex;
}

//----------------------------------------------------------------------------------------
function decodeCaesar($hex)  {
	global $_caesarKey;

	$raw = '';
	$j = 0;
	for ($i=0; $i<strlen($hex)-1; $i+=2)  {

		if ($j >= strlen($_caesarKey))  {$j = 0;}
		$asciiValue = hexdec($hex[$i].$hex[$i+1]);
		$asciiKey = ord($_caesarKey[$j]);
		$asciiValue ^= $asciiKey;
		
		$raw .= chr($asciiValue);
		$j++;
	}
	return $raw;
}

//----------------------------------------------------------------------------------------
//  Using the FILE_APPEND flag to append the content to the end of the file
//  and the LOCK_EX flag to prevent anyone else writing to the file at the same time
function addFileLog($file, $text)  {
	$today = date("Ymd");
	$timeStamp = date("Y-m-d H:i:s");
	$filePath = "./__logs__/".$today."_".$file;
	file_put_contents($filePath, "\n".$timeStamp."\n".$text, FILE_APPEND | LOCK_EX);
}

?>