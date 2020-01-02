<!DOCTYPE html>
<html>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css"
        integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js"></script>


    <link rel="stylesheet" href="background.css">

<body>
<?php

require_once '../php/Database.php';
require_once '../php/Navbar.php';
require_once '../php/LoginHandler.php';
require_once '../php/Account.php';




$db = new Database();
$user = $db->LoadUserFromUsername(LoginHandler::GetCurrentAccountType(), LoginHandler::GetCurrentUsername());
$queryStr = "SELECT driverId, changeAm, dateC FROM PointHistory WHERE sponsorId={$user->GetID()};";

        $result = $db->sql->query($queryStr);
        if ($result && $result->num_rows > 0) {
        while($row = $result->fetch_assoc()){
                $driveo = $row["driverId"];
                 $queryStr2 = "SELECT fName, lName FROM Drivers WHERE id={$driveo};";
                 $result2 = $db->sql->query($queryStr2);
                 $nameo = $result2->fetch_assoc();
              ?>   <center><?php echo $nameo["fName"]; echo " "; echo $nameo["lName"];
                 echo " ";
                 echo $row["changeAm"];
                echo " ";
                echo $row["dateC"];
                echo "<br />";
?>
                </center>
<?php
        }
        }
?>
</body>

