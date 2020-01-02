<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
require_once '../php/Database.php';
require_once '../php/LoginHandler.php';
require_once '../php/Account.php';
$alertDisplay = false;
$alertDisplay2 = false;
$alertDisplay3 = false;

$db = new Database();
$admin = $db->LoadUserFromUsername(LoginHandler::GetCurrentAccountType(), LoginHandler::GetCurrentUsername());
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $pass = $_POST["psw"];
    $passr = $_POST["rpsw"];
    $uname = $_POST["usn"];
    $email = $_POST["email"];
    $phone = $_POST["phone"];
    $lname = $_POST["lname"];
    $fname = $_POST["fname"];


  if($pass != $passr){
      $alertDisplay = true;
  }
  else if($db->IsUsernameTaken($uname)){
    $alertDisplay2 = true;
  }
  else if($db->IsEmailTaken($email)){
    $alertDisplay3 = true;
  }
  else{
    $passhash = password_hash($pass, PASSWORD_DEFAULT);
 $queryStr = "INSERT INTO Admins VALUES (NULL, '{$email}', '{$passhash}', '{$fname}', '{$lname}', '{$uname}', '{$phone}');";
 $db->sql->query($queryStr);

 header("Location: adduser.php");
 exit;

  }
}
  ?>
  <!DOCTYPE html>
  <html>
      <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css"
          integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">

      <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>
      <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js"></script>


      <link rel="stylesheet" href="background.css">

  <body>
  <div class="jumbotron1 text-center">
    <br>
  </div>
  <div class="alert alert-danger" role="alert" style="display:<?php echo ($alertDisplay?"block":"none"); ?>;">
      Password Incorrect
  </div>
  <div class="alert alert-danger" role="alert" style="display:<?php echo ($alertDisplay2?"block":"none"); ?>;">
      Username is taken
  </div>
  <div class="alert alert-danger" role="alert" style="display:<?php echo ($alertDisplay3?"block":"none"); ?>;">
      Email is taken
  </div>

  <?php require_once '../php/Navbar.php'; ?>


  <center>  <h2>Add a Admin</h2> </center>
  </div>

<form action="addadmin.php" method="post" style="border:1px solid #ccc">
  <div class="container">

    <label for="fname"><b>First Name</b></label>
    <input type="text" class="form-control" placeholder="First name" name="fname" required>
    <br>
    <label for="lname"><b>Last Name</b></label>
    <input type="text" class="form-control" placeholder="Last name" name="lname" required>
    <br>
    <label for="email"><b>Email</b></label>
    <input type="text" class="form-control" placeholder="email" name="email" required>
    <br>
    <label for="username"><b>Username</b></label>
    <input type="text" class="form-control" placeholder="username" name="usn" required>
    <br>
    <label for="phone"><b>Phone number</b></label>
    <input type="text" class="form-control" placeholder="phone number" name="phone" required>
    <br>
    <label for="psw"><b>Password</b></label>
    <input type="password" class="form-control" placeholder="Password" name="psw" required>
    <br>
    <label for="rpsw"><b>Repeat Password</b></label>
    <input type="password" class="form-control" placeholder="Repeat Password" name="rpsw" required>
    <br>
      <button type="button" onclick="window.location.href = 'adduser.php';" class="btn btn-primary">Cancel</button>
      <button type="submit" name="changeinfo" class="btn btn-primary">Submit</button>

    </div>
  </div>
</form>
  </html>
  </body>


  </html>
