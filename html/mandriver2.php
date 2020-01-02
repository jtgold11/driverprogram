
<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
require_once '../php/Database.php';
require_once '../php/LoginHandler.php';
require_once '../php/Account.php';
$alertDisplay = false;
$userDr = true;
$userSp = false;
$db = new Database();
$admin = $db->LoadUserFromUsername(LoginHandler::GetCurrentAccountType(), LoginHandler::GetCurrentUsername());
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $db1 = new Database();
    $username = $_POST["username"];
    $user = $db1->LoadUserFromUsername(3, $username);
    if(!$user){
      $userDr = false;
      $userSp = true;
      $user = $db1->LoadUserFromUsername(2, $username);
      if(!$user){
        header("Location: mannouser.php");
        exit;
      }
    }

    if(isset($_POST["changeinfo"]) && $userDr){
      if(isset($_POST["psw"])){
        $pass = $_POST["psw"];
        $email = $_POST["email"];
        $fname = $_POST["fname"];
        $lname = $_POST["lname"];
        $uname = $_POST["usn"];
        $hnum = $_POST["hnum"];
        $street = $_POST["street"];
        $city = $_POST["city"];
        $zip = $_POST["zip"];
        $state = $_POST["state"];
        $phone = $_POST["phone"];


      if(!password_verify($pass,$admin->GetPasswordHash())){
          $alertDisplay = true;
      }
      else{
      $id = $user->GetID();
     $queryStr = "UPDATE Drivers SET stateCode='{$state}', phoneNumber='{$phone}', email='{$email}', fName='{$fname}', lName='{$lname}', houseNumber={$hnum}, street='{$street}', city='{$city}', zipcode='{$zip}', username='{$uname}' WHERE id={$id};";
     $db->sql->query($queryStr);
     header("Location: mandriver.php");
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

      <?php require_once '../php/Navbar.php'; ?>


      <center>  <h2>Change User Info</h2> </center>
      </div>

    <form action="mandriver2.php" method="post" style="border:1px solid #ccc">
      <div class="container">

        <label for="firstname"><b>First Name</b></label>
        <input type="text" class="form-control" value="<?php echo $user->fName;?>" name="fname" required>
        <br>
        <label for="lname"><b>Last Name</b></label>
        <input type="text" class="form-control" value="<?php echo $user->lName;?>" name="lname" required>
        <br>
        <label for="email"><b>Email</b></label>
        <input type="text" class="form-control" value="<?php echo $user->email;?>" name="email" required>
        <br>
        <label for="username"><b>Username</b></label>
        <input type="text" class="form-control"value="<?php echo $user->GetUsername();?>" name="usn" required>
        <br>
        <label for="hnum"><b>House Number</b></label>
        <input type="text" class="form-control"value="<?php echo $user->houseNumber;?>" name="hnum" required>
        <br>
        <label for="street"><b>Street</b></label>
        <input type="text" class="form-control"value="<?php echo $user->street;?>" name="street" required>
        <br>
        <label for="city"><b>City</b></label>
        <input type="text" class="form-control"value="<?php echo $user->city;?>" name="city" required>
        <br>
        <label for="zip"><b>Zipcode</b></label>
        <input type="text" class="form-control"value="<?php echo $user->zipcode;?>" name="zip" required>
        <br>
        <label for="state"><b>State</b></label>
        <input type="text" class="form-control"value="<?php echo $user->stateCode;?>" name="state" required>
        <br>
        <label for="phone"><b>Phone number</b></label>
        <input type="text" class="form-control"value="<?php echo $user->phoneNumber;?>" name="phone" required>
        <br>
        <label for="psw"><b>Admin Password</b></label>
        <input type="text" class="form-control" placeholder="Input Admin Password" name="psw" required>
        <br>
        <input type="hidden" name="username" id="hiddenField" value="<?php echo $username; ?>" />
        <div class="clearfix">
          <button type="button" onclick="window.location.href = 'mandriver.php';" class="btn btn-primary">Cancel</button>
          <button type="submit" name="changeinfo" class="btn btn-primary">Submit</button>

        </div>
      </div>
    </form>
      </html>
      </body>


      </html>
<?php
    }
    if(isset($_POST["delete"]) && $userDr){
      if(isset($_POST["psw"])){
        $pass = $_POST["psw"];
        if(!password_verify($pass, $admin->GetPasswordHash())){
            $alertDisplay = true;
        }
        else{
        $id = $user->GETID();
        $queryStr = "DELETE FROM Drivers WHERE UID={$id};";
        $db->sql->query($queryStr);

        header("Location: mandriver.php");
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
        <h2>Are you sure you want to delete user: <?php echo $username ?></h2>
        <br>
      </div>

      <div class="alert alert-danger" role="alert" style="display:<?php echo ($alertDisplay?"block":"none"); ?>;">
          Password Incorrect
      </div>
      <form action="mandriver2.php" method="post">
        <div class="container">
          <label for="psw"><b>Admin Password</b></label>
          <input type="password" class="form-control" placeholder="Enter Admin Password To Confirm" name="psw" required>
          <br>
          <input type="hidden" name="username" id="hiddenField" value="<?php echo $username ?>">
          <div class="clearfix">
            <button type="button" onclick="window.location.href = 'mandriver.php';" class="btn btn-primary">Cancel</button>
            <button type="submit" name="delete" class="btn btn-primary">Submit</button>
            <br>
            <br>
          </div>
        </div>
      </form>
<?php
    }


if(isset($_POST["changeinfo"]) && $userSp){
  if(isset($_POST["psw"])){
    $pass = $_POST["psw"];
    $comp = $_POST["comp"];
    $uname = $_POST["usn"];
    $email = $_POST["email"];
    $phone = $_POST["phone"];


  if(!password_verify($pass,$admin->GetPasswordHash())){
      $alertDisplay = true;
  }
  else{

  $id = $user->GetID();
 $queryStr = "UPDATE Sponsors SET email='{$email}', companyName='{$comp}', phoneNumber='{$phone}', username='{$uname}' WHERE id={$id};";
 $db->sql->query($queryStr);
 header("Location: mandriver.php");
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

  <?php require_once '../php/Navbar.php'; ?>


  <center>  <h2>Change User Info</h2> </center>
  </div>

<form action="mandriver2.php" method="post" style="border:1px solid #ccc">
  <div class="container">


    <label for="email"><b>Email</b></label>
    <input type="text" class="form-control" value="<?php echo $user->email;?>" name="email" required>
    <br>
    <label for="username"><b>Username</b></label>
    <input type="text" class="form-control"value="<?php echo $user->GetUsername();?>" name="usn" required>
    <br>
    <label for="comp"><b>companyName</b></label>
    <input type="text" class="form-control"value="<?php echo $user->companyName;?>" name="comp" required>
    <br>
    <label for="phone"><b>Phone number</b></label>
    <input type="text" class="form-control"value="<?php echo $user->phoneNumber;?>" name="phone" required>
    <br>
    <label for="psw"><b>Admin Password</b></label>
    <input type="text" class="form-control" placeholder="Input Admin Password" name="psw" required>
    <br>
    <input type="hidden" name="username" id="hiddenField" value="<?php echo $username; ?>" />
    <div class="clearfix">
      <button type="button" onclick="window.location.href = 'mandriver.php';" class="btn btn-primary">Cancel</button>
      <button type="submit" name="changeinfo" class="btn btn-primary">Submit</button>

    </div>
  </div>
</form>
  </html>
  </body>


  </html>
<?php
}
if(isset($_POST["delete"]) && $userSp){
  if(isset($_POST["psw"])){
    $pass = $_POST["psw"];
    if(!password_verify($pass, $admin->GetPasswordHash())){
        $alertDisplay = true;
    }
    else{
    $id = $user->GETID();
    $queryStr = "DELETE FROM Sponsors WHERE UID={$id};";
    $db->sql->query($queryStr);

    header("Location: mandriver.php");
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
    <h2>Are you sure you want to delete user: <?php echo $username ?></h2>
    <br>
  </div>

  <div class="alert alert-danger" role="alert" style="display:<?php echo ($alertDisplay?"block":"none"); ?>;">
      Password Incorrect
  </div>
  <form action="mandriver2.php" method="post">
    <div class="container">
      <label for="psw"><b>Admin Password</b></label>
      <input type="password" class="form-control" placeholder="Enter Admin Password To Confirm" name="psw" required>
      <br>
      <input type="hidden" name="username" id="hiddenField" value="<?php echo $username ?>">
      <div class="clearfix">
        <button type="button" onclick="window.location.href = 'mandriver.php';" class="btn btn-primary">Cancel</button>
        <button type="submit" name="delete" class="btn btn-primary">Submit</button>
        <br>
        <br>
      </div>
    </div>
  </form>
<?php
}

}
?>
