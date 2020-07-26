<?php
//----------------------------------------------------------------------------------------
//  Database Connection Module
//----------------------------------------------------------------------------------------
//  Platform: Linux + Apache + PHP
//  Written by Pacess HO
//  Copyright Pacess Studio, 2019-2020.  All rights reserved.
//----------------------------------------------------------------------------------------

//========================================================================================
final class databaseConnection  {

	//----------------------------------------------------------------------------------------
	//	Static variables
	private static $instance = null;

	//----------------------------------------------------------------------------------------
	//  Class variables
	public $sqlDatabase;

	//----------------------------------------------------------------------------------------
	//	Constructors & destructors
	//----------------------------------------------------------------------------------------
	private function __construct()  {

		$sqlHost = decodeCustomBase64(MYSQL_HOST);
		$sqlUser = decodeCustomBase64(MYSQL_USER);
		$sqlPassword = decodeCustomBase64(MYSQL_PASSWORD);
		$sqlDatabase = decodeCustomBase64(MYSQL_DATABASE);

		//  Open database
		$this->sqlDatabase = new mysqli($sqlHost, $sqlUser, $sqlPassword, $sqlDatabase);
		$this->sqlDatabase->autocommit(TRUE);

		//  This statment make UTF-8 characters could be show properly
		$this->sqlDatabase->query("SET NAMES utf8");
	}

	//----------------------------------------------------------------------------------------
	public function __destruct()  {
		//	Close database
		$this->sqlDatabase->close();
	}

	//----------------------------------------------------------------------------------------
	//	Static functions
	//----------------------------------------------------------------------------------------
	public static function getInstance()  {
		if (is_null(self::$instance))  {
			self::$instance = new databaseConnection();
		}
		return self::$instance;
	}
}
?>