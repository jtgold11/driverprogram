
<?php

require_once '../php/Database.php';
require_once '../php/Catalog.php';
require_once '../php/LoginHandler.php';

LoginHandler::CheckPrivilege(UserAccount::SPONSOR_ACCOUNT);

// get the requested sponsor's catalog from the URL parameter
/*if (!isset($_GET["driverId"])) {
    header("Location: select-driver.php");
    exit;
}*/

$driverid = $_GET["driverId"];

$db = new Database();
$user = $db->LoadUserFromUsername(LoginHandler::GetCurrentAccountType(), LoginHandler::GetCurrentUsername());
$queryStr = "SELECT pRatio FROM DriverSponsorRelations WHERE sponsorId={$user->GetID()} AND driverId={$driverid};";
$result = $db->sql->query($queryStr);

if ($result && $result->num_rows > 0) {
  $row = $result->fetch_assoc();
  $pratio = $row["pRatio"];
}


?><center>The dollar to point ratio for this driver is</br><?php
	echo $pratio;
	?></br></br> Change the Current ratio to
		 <form method="POST" action="<?php echo $_SERVER["PHP_SELF"];?>">
                <br /><input type="text" name="pratio"><br />

                <button class="btn btn-primary" type="submit">Submit</button>

</center><?php

if (!empty($_POSt["points"])) {
        $pratio = $_POST["pratio"];
$queryStr = "UPDATE DriverSponsorRelations SET pratio={$pratio} WHERE sponsorId={$user->GetID()} AND driverId={$driverid};";
$db->sql->query($queryStr);
}




?>
