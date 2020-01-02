<?php

require_once '../php/Navbar.php';
require_once '../php/Database.php';
require_once '../php/Account.php';
require_once '../php/LoginHandler.php';

LoginHandler::CheckPrivilege(UserAccount::SPONSOR_ACCOUNT);


// get all of this driver's sponsors
$db = new Database();
$user = $db->LoadUserFromUsername(LoginHandler::GetCurrentAccountType(), LoginHandler::GetCurrentUsername());
if (!$user) {
    error_log("Failed to get driver user");
    header("HTTP/1.1 500 Internal Server Error");
    exit;
}

$user->LoadDriverIdsAndCredits();
$drivers = [];

foreach ($user->driverIds as $id) {
    array_push($drivers, $db->LoadUserFromId(UserAccount::DRIVER_ACCOUNT, $id));
}

function PrintSelectBox(&$drivers) {
    echo "<select class=\"form-control w-auto d-inline-block\" name=\"driverId\">";
    foreach ($drivers as $driver) {
        echo "<option value=\"{$driver->GetID()}\">{$driver->fName} {$driver->lName}</option>";
    }
    echo "</select>";
}


if (isset($_POST["driverId"])) {
  $did =  $_POST["driverId"];
}
$points = 0;
?>

<!doctype html>
<html lang="en">

<head>
    <title>Select Sponsor's Catalog</title>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css"
        integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">
</head>

<body>

    <!--#include file="Navbar.php" -->

    <div class="container text-center">
        <form action="givepoints.php" method="POSt">
            <fieldset>
                <legend>Select which driver to give points to</legend>
                <?php PrintSelectBox($drivers); ?>
                <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>">
		<br />Amount of Points  <input type="text" name="points"><br />
		
		<button class="btn btn-primary" type="submit">Go</button>
            </fieldset>
        </form>
    </div>
<?php
$today = getdate();
$month = $year = $mday = $newmday = "";
$month =  $today["mon"];
$year = $today["year"];
$mday = $today["mday"];

if($today["mday"] < 10){
$newmday = "0" . $mday;}
else{
$newmday = $mday;}

$date1 = $year . "-" . $month . "-" . $newmday;


if (!empty($_POST["points"])) {
	$points = $_POST["points"];
$queryStr = "UPDATE DriverSponsorRelations SET credits= credits + {$points}  WHERE sponsorId={$user->GetID()} AND driverId={$did};";
$db->sql->query($queryStr);
$queryStr2 = "INSERT INTO PointHistory VALUES({$did}, {$user->getID()},{$points},'{$date1}');";
$db->sql->query($queryStr2);
}

?>


    <!-- Optional JavaScript -->
    <!-- jQuery first, then Popper.js, then Bootstrap JS -->
    <script src="https://code.jquery.com/jquery-3.3.1.slim.min.js"
        integrity="sha384-q8i/X+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo" crossorigin="anonymous">
    </script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js"
        integrity="sha384-UO2eT0CpHqdSJQ6hJty5KVphtPhzWj9WO1clHTMGa3JDZwrnQq4sF86dIHNDz0W1" crossorigin="anonymous">
    </script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js"
        integrity="sha384-JjSmVgyd0p3pXB1rRibZUAYoIIy6OrQ6VrjIEaFf/nJGzIxFDsf4x0xIM+B07jRM" crossorigin="anonymous">
    </script>
</body>

</html>








