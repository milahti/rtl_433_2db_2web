<?php
    $dbhost="localhost";
    $dblogin="www-data";
    $dbpwd="www-data";
    $dbname="rtl433db";

    //check if we received min/max as input
    if (isset($_POST["limit"])) {
	$limit = $_POST["limit"];
    } else {
	$limit = "min";
    }
    //check if we received sensors as input
    if (isset($_POST["sensors"])) {
	$sensors = $_POST["sensors"];
    } else {
	$sensors = "1";
    }
    //check if we received date_limit as input
    if (isset($_POST["date_limit"])) {
	$sensors = $_POST["date_limit"];
    } else {
	$date_limit = "30";
    }
    //check if we received type as input
    if (isset($_POST["type"])) {
	$type = $_POST["type"];
    } else {
	$type = "temperature";
    }

    //create connection   
    $conn =  new mysqli($dbhost,$dblogin,$dbpwd, $dbname);
    //check connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    //echo "connected successfully";
    
    //convert row information dynamically to a table with column values for each sensor
    //return data from all dates in the table
    $SQLString = "SET @sql = NULL";
    $conn->query($SQLString);
    $SQLString = "SELECT
	  GROUP_CONCAT(DISTINCT
	    CONCAT(
	      '" . $limit . "(IF(`channel` = ', `channel`, '," . $type . ",NULL)) AS Sensor', `channel`)
	  ) INTO @sql
	FROM SensorData WHERE (channel in (" . $sensors . ") AND (DATE_SUB(CURDATE(),INTERVAL " . $date_limit . " DAY) <= timestamp))";
    $conn->query($SQLString);
    $SQLString ="SET @sql = CONCAT('SELECT  date(timestamp)as Date, ', @sql, ' 
                  FROM    SensorData
                  WHERE (channel in (" . $sensors . ") AND (DATE_SUB(CURDATE(),INTERVAL " . $date_limit . " DAY) <= timestamp)) GROUP BY Date')";
    $conn->query($SQLString);
    $SQLString = "PREPARE stmt FROM @sql";
    $conn->query($SQLString);
    $SQLString = "EXECUTE stmt";
		

    $result = $conn->query($SQLString);
    if  ($result->num_rows > 0) {
        
        //handle data of each row
        $i=0;
        while($row  = $result->fetch_assoc()) {
                
		if ($i==0) {
			//set header row
			$data[$i] = array_keys($row);
			$i=$i+1;	
		} 				
	        $data[$i] = array_values($row);
		//convert temperaturues from text to float, not for 0-row 
		$length = count($data[$i]);
		for ($j = 1; $j<$length; $j++) {
			//do not try for NULL values			
			if (isset($data[$i][$j])) {
				$data[$i][$j]=floatval($data[$i][$j]);
			}
		}
		
		$i=$i+1;
    	}
    $SQLString = "DEALLOCATE PREPARE stmt";
    $conn->query($SQLString);
   
    echo json_encode($data);
    } else {
	echo "0 results";
    }
    /* free result set */
    $result->free();
    $conn->close();
?>
