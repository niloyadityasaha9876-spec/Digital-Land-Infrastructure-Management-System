<?php

$dsn = "mysql:host=localhost;dbname=land_management_db;";
$dbusername = "root";
$dbpass = "MysqlMrGhost2003@";

try{
    $pdo =  new PDO($dsn,$dbusername,$dbpass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);
    }
catch(PDOException $e){
echo "Connection failed: " . $e->getMessage();
}

?>
