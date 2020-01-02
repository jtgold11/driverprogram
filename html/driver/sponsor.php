<?php
require_once '../php/Database.php';
require_once '../php/Account.php';
require_once '../php/LoginHandler.php';
require_once '../php/Navbar.php';

$db = new Database();
$user = $db->LoadUserFromUsername(LoginHandler::GetCurrentAccountType(), LoginHandler::GetCurrentUsername());
$fname = "";


if ($user->GetAccountType() == UserAccount::DRIVER_ACCOUNT || $user->GetAccountType() == UserAccount::ADMIN_ACCOUNT) {
  $fname = $user->fName;
}
else {
  $fname = $user->GetUsername();
}

if (!isset($_GET["sponsorId"])) {
    header("Location: select-sponsor-page.php");
    exit;
}

$sponsorId = $_GET["sponsorId"];

$sponsor = $db->LoadUserFromId(UserAccount::SPONSOR_ACCOUNT, $_GET["sponsorId"]);
$queryStr = "SELECT sponsorId,credits FROM DriverSponsorRelations WHERE driverId={$user->GetID()} AND sponsorId={$sponsorId};";
$result = $db->sql->query($queryStr);
if ($result && $result->num_rows > 0) {
  $row = $result->fetch_assoc();
  $credits = $row["credits"];
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Sponsor Points Page</title>
    <meta name="viewport" cotent="width=device-width, initial-scale=1">

    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css"
        integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin=
        "anonymous">
    <link rel="stylesheet" href="background.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js"></script>
</head>

<body>
  <!--#include file="Navbar.php" -->

  <div class="container text-center">
    <h3> Welcome to <?php echo "{$sponsor->companyName}" ?>'s page.<h3>
  </div>
  <div class="container text-center">
    <h2> Your points: <?php echo "{$credits}" ?> <h2>
  </div>
  <div class="containter text-center">
    <h4> Sponsor's Catalog <a href="view-catalog.php?sponsorId=<?=$sponsorId ?>">Page</a> <h4>
  </div>
</body>
