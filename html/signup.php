<?php
require_once 'php/Database.php';
require_once 'php/Account.php';
require_once 'php/LoginHandler.php';

$db = new Database();
$allSponsors = $db->LoadAllSponsors();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // get account type, username, and password from the form
    $fname = $_POST["fname"];
    $lname = $_POST["lname"];
    $email = $_POST["email"];
    $username = $_POST["usn"];
    $password = $_POST["psw"];
    $sponsorId = $_POST["sponsor"];

    $newUser = new DriverAccount();
    $newUser->fName = $fname;
    $newUser->lName = $lname;
    $newUser->email = $email;
    $newUser->InitializeUsername($username);
    $newUser->SetNewPassword($password);
    $newUser->isActive = false;

    if (!$newUser->Register()) {
      error_log("Failed to register new user: ".$db->GetLastError());
      header("HTTP/1.1 500 Internal Server Error");
      exit;
    }

    if (!$newUser->ApplyToSponsor($sponsorId)) {
      $db->DeleteUser($newUser);
      error_log("Failed to apply driver to sponsor: ".$db->GetLastError());
      header("HTTP/1.1 500 Internal Server Error");
      exit;
    }

    // now that they've signed up, re-direct to login page
    header("Location: /index.php");
    exit;
}



?>

<!DOCTYPE html>
<html>
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
    background-color: #1E8449;
    color: #fffff;
  }
  </style>

<body>

  <?php require_once 'php/Navbar.php'; ?>

  <div class="jumbotron text-center">
    <h1>Sign up</h1>
  </div>

<form action="signup.php" method="post" style="border:1px solid #ccc">
  <div class="container">

    <label for="firstname"><b>First Name</b></label>
    <input type="text" class="form-control" placeholder="Enter First Name" name="fname" required>
    <br>
    <label for="lname"><b>Last Name</b></label>
    <input type="text" class="form-control" placeholder="Enter Last Name" name="lname" required>
    <br>
    <label for="email"><b>Email</b></label>
    <input type="text" class="form-control" placeholder="Enter Email" name="email" required>
    <br>
    <label for="username"><b>Username</b></label>
    <input type="text" class="form-control" placeholder="Enter Username" name="usn" required>
    <br>
    <label for="psw"><b>Password</b></label>
    <input type="password" class="form-control" placeholder="Enter Password" name="psw" required>
    <br>
    <label for="psw-repeat"><b>Repeat Password</b></label>
    <input type="password" class="form-control" placeholder="Repeat Password" name="psw-repeat" required>
    <br>
    <div class="form-group">
        <label for="exampleFormControlSelect1"><b>Select Sponsor to Register With</b></label>
        <select class="form-control" id="exampleFormControlSelect1" name="sponsor">
          <?php

            $i = 0;

            foreach($allSponsors as $sponsor) {
              echo "
              <option value=\"{$sponsor->GetID()}\">
                        $sponsor->companyName
              </option>
              ";
              $i++;
            }

          ?>
        </select>
    </div>

    <div class="clearfix">
      <button type="button" onclick="window.location.href = 'index.php';" class="btn btn-primary">Cancel</button>
      <button type="submit" class="btn btn-primary">Register</button>

    </div>
  </div>
</form>






  </body>

</html>
