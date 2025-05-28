<?php
$LineData = file_get_contents('php://input');
$jsonData = json_decode($LineData,true);  

$serverName = "10.12.12.206";
$userName ='nwlproduction';
$userPassword="Nwl!2563789!";
$dbName = "NWL_Detection";
$connectionInfo = array("Database"=>$dbName, "UID"=>$userName, "PWD"=>$userPassword, "MultipleActiveResultSets"=>true, "CharacterSet" => "UTF-8");
$conn = sqlsrv_connect($serverName, $connectionInfo);
if( $conn === false ){
    die( print_r( sqlsrv_errors(), true));
}

$userID = $jsonData["events"][0]["source"]["userId"];
$replyToken = $jsonData["events"][0]["replyToken"];
$text = $jsonData["events"][0]["message"]["text"];
$timestamp = $jsonData["events"][0]["timestamp"]; //Unix time

$params = array($userID);
$sql = " SELECT userID FROM TmstLineUserIdCustomer WHERE userID = ? ";
$stmt = sqlsrv_query($conn, $sql, $params);
$result = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);
$userIddb = $result['userID'];
if($stmt === false){
    die(print_r( sqlsrv_errors(), true));
}

if($userID == $userIddb){
    if($text == 'disable detection'){
        $params = array($userID);
        $sql = " DELETE FROM TmstLineUserIdCustomer WHERE userID = ? ";
        $stmt = sqlsrv_query($conn, $sql, $params);
        if($stmt === false){
        die(print_r( sqlsrv_errors(), true));  
        }
        $mylogfile = file_put_contents('useriddelete.txt', $userIddb .PHP_EOL, FILE_APPEND | LOCK_EX);
        fclose($mylogfile);
        echo 'Delete Success!';
        }
        echo 'User is already!';
}else{
    if($text == 'detection'){
        $params = array($userID, $replyToken, $timestamp);
        $sql = " INSERT INTO TmstLineUserIdCustomer (userID, replyToken, timestamp) VALUES (?, ?, ?) ";
        $stmt = sqlsrv_query($conn, $sql, $params);
        if( $stmt === false ) {
            die( print_r( sqlsrv_errors(), true));
        }
        $mylogfile = file_put_contents('useridinsert.txt', $userID .PHP_EOL, FILE_APPEND | LOCK_EX);
        fclose($mylogfile);
        echo 'Insert Success!';
    }
}

$mylogfile = file_put_contents('rawjson.txt', $LineData .PHP_EOL, FILE_APPEND | LOCK_EX);
fclose($mylogfile);

http_response_code(200);

