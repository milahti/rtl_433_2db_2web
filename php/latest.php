<?php
    $dbhost="localhost";
    $dblogin="www-data";
    $dbpwd="my_pwd";
    $dbname="rtl433db";

    //create connection   
    $conn =  new mysqli($dbhost,$dblogin,$dbpwd, $dbname);
    //check connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    //echo "connected successfully";
    
    //define sql statement
    $SQLString = "select * from (select * from SensorData order by timestamp desc limit 5000) as myNewTable group by myNewTable.channel order by myNewTable.channel";
		

    $result = $conn->query($SQLString);
    if  ($result->num_rows > 0) {
        //set heading
        //handle data of each row
        $i=0;
        while($row  = $result->fetch_assoc()) {
                
		if ($i==0) {
			$data[$i] = array_keys($row);
			$i=$i+1;	
		} 				
	        $data[$i] = array_values($row);
		
		$i=$i+1;
    	}
   
    echo json_encode($data);
    } else {
	echo "0 results";
    }
    /* free result set */
    $result->free();
    $conn->close();
?>
