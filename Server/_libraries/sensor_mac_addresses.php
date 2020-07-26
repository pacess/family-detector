<?php
//----------------------------------------------------------------------------------------
//  Database Table Interface
//----------------------------------------------------------------------------------------
//  Platform: Linux + Apache + PHP
//  Written by Pacess HO
//  Copyright Pacess Studio, 2019.  All rights reserved.
//----------------------------------------------------------------------------------------

//========================================================================================
final class sensor_mac_addresses  {

	//----------------------------------------------------------------------------------------
	private $tableName = "sensor_mac_addresses";

	//----------------------------------------------------------------------------------------
	//  Class variables
	private $sqlDatabase;

	//----------------------------------------------------------------------------------------
	//  Constructor
	function __construct()  {
		$database = databaseConnection::getInstance();
		$this->sqlDatabase = $database->sqlDatabase;
	}

	//----------------------------------------------------------------------------------------
	//  Destructor
	function __destruct()  {
	}

	//========================================================================================
	//  Main method
	//----------------------------------------------------------------------------------------
	public function getRecord($dictionary)  {

		$select = "*";
		if (isset($dictionary["select"]))  {$select = addslashes($dictionary["select"]);}

		$where = "";
		$group = "";
		$limit = "";
		$valueArray = array();
		$parametersType = "";
		$order = " ORDER BY created_at DESC";
		$query = "SELECT ".$select." FROM `".$this->tableName."` WHERE".
			" deleted_at IS NULL";
		if ($dictionary !== null)  {

			foreach ($dictionary as $key => $value)  {
				switch ($key)  {

					//  Commands
					case "select":  break;
					case "limit":  $limit = " LIMIT ".addslashes($value);  break;
					case "group":  $group = " GROUP BY ".addslashes($value);  break;
					case "order":  $order = " ORDER BY ".addslashes($value);  break;
					case "where":  $where = " AND (".($value).")";  break;

					case "createFromDate":  $query .= " AND created_at>='".addslashes($value)."'";  break;
					case "createToDate":  $query .= " AND created_at<='".addslashes($value)."'";  break;
					case "modifyFromDate":  $query .= " AND updated_at>='".addslashes($value)."'";  break;
					case "modifyToDate":  $query .= " AND updated_at<='".addslashes($value)."'";  break;

					//  Fields
					default:  {
						if ($value != null)  {
							$query .= " AND $key='".addslashes($value)."'";
						}
					}  break;
				}
			}
		}
		$query .= $where.$group.$order.$limit;

		//  Make query
// 		error_log("Query: $query...");
		$resultArray = $this->sqlDatabase->query($query);

		//  Fetch values
		$dataArray = array();
		if ($resultArray !== false)  {
			while ($rowArray = $resultArray->fetch_assoc())  {
				array_push($dataArray, $rowArray);
			}
		}
		$returnArray = array("query"=>$query, "dataArray"=>$dataArray);
		return $returnArray;
	}

	//----------------------------------------------------------------------------------------
	public function addRecord($dictionary)  {
		if (empty($dictionary))  {return null;}

		$node = $dictionary["node"];
		$macAddress = $dictionary["mac_address"];

		if (empty($node))  {return null;}
		if (empty($macAddress))  {return null;}

		//  Prevent SQL injection
		$node = addslashes($node);
		$macAddress = addslashes($macAddress);

		//----------------------------------------------------------------------------------------
		$query = "INSERT INTO `".$this->tableName."` SET".
			" created_at=NOW()".
			", updated_at=NOW()".
			", alived_at=NOW()".
			", node='$node'".
			", mac_address='$macAddress'";

		foreach ($dictionary as $key => $value)  {
			switch ($key)  {
				case "id":
				case "created_at":
				case "updated_at":
				case "deleted_at":
				case "alived_at":
				case "node":
				case "mac_address":
					break;

				default:
					$query .= ", $key='".addslashes($value)."'";
					break;
			}
		}

		$resultArray = $this->sqlDatabase->query($query);
		$affectedRows = $this->sqlDatabase->affected_rows;
		$lastID = mysqli_insert_id($this->sqlDatabase);

		//  affected_rows:
		//  1 = Insert a new record
		//  2 = Update an existing record
		$returnDictionary = array("query"=>$query, "affectedRows"=>$affectedRows, "lastID"=>$lastID, "action"=>"insert");
		return $returnDictionary;
	}

	//----------------------------------------------------------------------------------------
	public function updateOrCreateRecord($dictionary)  {
		if (empty($dictionary))  {return null;}

		$node = $dictionary["node"];
		$macAddress = $dictionary["mac_address"];

		if (empty($node))  {return null;}
		if (empty($macAddress))  {return null;}

		//  Prevent SQL injection
		$node = addslashes($node);
		$macAddress = addslashes($macAddress);

		//----------------------------------------------------------------------------------------
		$timestamp = date("Y-m-d H:i:00", strtotime("-10 minutes"));
		$query = "UPDATE `".$this->tableName."` SET".
			" updated_at=NOW()".
			", alived_at=NOW()".
			", node='$node'";

		foreach ($dictionary as $key => $value)  {
			switch ($key)  {
				case "id":
				case "created_at":
				case "updated_at":
				case "deleted_at":
				case "alived_at":
				case "node":
				case "mac_address":
					break;

				default:
					$query .= ", $key='".addslashes($value)."'";
					break;
			}
		}

		$query .= " WHERE mac_address='$macAddress'".
			" AND alived_at>='$timestamp'";

		$resultArray = $this->sqlDatabase->query($query);
		$affectedRows = $this->sqlDatabase->affected_rows;
		if ($affectedRows >= 1)  {

			//  affected_rows:
			//  1 = Insert a new record
			//  2 = Update an existing record
			$returnDictionary = array("query"=>$query, "affectedRows"=>$affectedRows, "action"=>"update");
			return $returnDictionary;
		}

		//  Cannot update, create one
		$returnDictionary = $this->addRecord($dictionary);
		return $returnDictionary;
	}
}

?>