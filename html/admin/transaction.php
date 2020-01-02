<!DOCTYPE html>
<html>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css"
        integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js"></script>


    <link rel="stylesheet" href="background.css">
<style>
.center-justified {
  text-align: justify;
  margin: 0 auto;
  width: 30em;
}
</style>
<body>
<?php

require_once '../php/Database.php';
require_once '../php/Navbar.php';
require_once '../php/LoginHandler.php';
require_once '../php/Account.php';




$db = new Database();
$user = $db->LoadUserFromUsername(LoginHandler::GetCurrentAccountType(), LoginHandler::GetCurrentUsername());
$queryStr = "SELECT * FROM PointHistory;";
?> <div class="center-justified">><h2> Transactions </h2></div><?php

        $result = $db->sql->query($queryStr);
        if ($result && $result->num_rows > 0) {
        while($row = $result->fetch_assoc()){
                $sponso = $row["sponsorId"];
                 $queryStr2 = "SELECT companyName FROM Sponsors WHERE id={$sponso};";
		$drivo = $row["driverId"];
		$queryStr3 = "SELECT fName, lName FROM Drivers WHERE id={$drivo};";
                 $result2 = $db->sql->query($queryStr2);
                 $compo = $result2->fetch_assoc();
		 $result3 = $db->sql->query($queryStr3);
                 $nameo = $result3->fetch_assoc();
              ?>   <div class="center-justified"><?php 
		echo $compo["companyName"]; echo " ";
		echo $nameo["fName"]; echo " "; echo $nameo["lName"];
                 echo " ";
                 echo $row["changeAm"];
                echo " ";
                echo $row["dateC"];
                echo "<br />";
?>
                </div>
<?php
        }
        }
?>
</body>

