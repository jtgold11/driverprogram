<?php 

require_once '../php/LoginHandler.php';
require_once '../php/Database.php';
require_once '../php/Account.php';
require_once '../php/Navbar.php';

// Redirect to login page if the user isn't logged in.
if (!LoginHandler::IsLoggedIn()) {
  header("Location: index.php");
  exit;
}


// Get the user's first name or, if they're a sponsor,
// their username.
$db = new Database();
$user = $db->LoadUserFromUsername(LoginHandler::GetCurrentAccountType(), LoginHandler::GetCurrentUsername());
$fname = "";


if ($user->GetAccountType() == UserAccount::DRIVER_ACCOUNT || $user->GetAccountType() == UserAccount::ADMIN_ACCOUNT) {
  $fname = $user->fName;
}
else {
  $fname = $user->GetUsername();
}

?>



<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Welcome</title>
    <meta name="viewport" cotent="width=device-width, initial-scale=1">

    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css"
        integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin=
        "anonymous">
    <link rel="stylesheet" href="background.css">


    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js"></script>


</head>
<style>
.jumbotron {
  background-color: #34ebd5;
  color: #fffff;
}
</style>

<body>

    <!--#include file="Navbar.php" -->
    
    <div class="container">
      <p>
        <a href="/logout.php" class="btn btn-info">Sign Out of Your Account</a>
      </p>
      <div class="jumbotron text-center">
        <h1>Hi, <?php echo $fname; ?>.</h1>
        <h2>Welcome to the Good Driving Incentive Program.</h2>
      </div>
    </div>
    <div class="container">
      <h3>Special Deals Today Only!</h3>
        <pre class="pre-scrollable">
          This will be where items will
          be placed on front page that have special deals going on
          at the moment. </pre>
      <h3>Recent Purchases</h3>
        <pre class="pre-scrollable">
          This will be where items will be placed
          for recent purchases by the driver.</pre>
    </div>
    <div class="container-fluid bg-grey">
      <div class="row">
        <div class="col-sm-5">
          <p><span class="glyphicon glyphicon-map-marker"></span> Clemson, SC</p>
          <p><span class="glyphicon glyphicon-phone"></span> (864) 867-5309</p>
          <p><span class="glyphicon glyphicon-envelope"></span> gooddriver@incentive.com</p>
        </div>
      </div>
    </div>


</body>
</html>
