<?php
require_once 'php/logInNavBar.php';
require_once 'php/LoginHandler.php';

$alertDisplay = false;

// if the user is already logged in, redirect them to the
// welcome page

if (LoginHandler::IsLoggedIn()) {
    $accType = LoginHandler::GetCurrentAccountType();
    
    switch ($accType) {
        case UserAccount::ADMIN_ACCOUNT:
            header("Location: /admin/welcome.php");
            exit;

        case UserAccount::DRIVER_ACCOUNT:
            header("Location: /driver/welcome.php");
            exit;   
        
        case UserAccount::SPONSOR_ACCOUNT:
            header("Location: /sponsor/welcome.php");
            exit;
            
    }


}
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // get account type, username, and password from the form
    $username = $_POST["username"];
    $password = $_POST["password"];

    $accType = -1;

    switch ($_POST["accountType"]) {
        case "Driver":
            $accType = UserAccount::DRIVER_ACCOUNT;
            break;

        case "Sponsor":
            $accType = UserAccount::SPONSOR_ACCOUNT;
            break;

        case "Admin":
            $accType = UserAccount::ADMIN_ACCOUNT;
            break;

        default:
            header("Location: index.php");
            exit;
    }

    $lh = new LoginHandler($accType, $username, "", $password);

    if ($accType == UserAccount::ADMIN_ACCOUNT){

      if ($lh->Login() !== false) {
          header("Location: /admin/welcome.php");
          exit;
      }
      else {
          $alertDisplay = true;
      }
            
    
    }
    else if ($accType == UserAccount::DRIVER_ACCOUNT){
    
      if ($lh->Login() !== false) {
          header("Location: /driver/welcome.php");
          exit;
      }
      else {
          $alertDisplay = true;
      }
    
    }

    else{
    
      if ($lh->Login() !== false) {
          header("Location: /sponsor/welcome.php");
          exit;
      }
      else {
          $alertDisplay = true;
      }
    }


}

  

?>

<!DOCTYPE html>
<html>

<head>
    <title> Login Page </title>

    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css"
        integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">

    <link rel="stylesheet" href="background.css">
</head>

<body>

    <!-- Login Boxes -->
    <div class="container">

        <div class="alert alert-danger" role="alert" style="display:<?php echo ($alertDisplay?"block":"none"); ?>;">
            Sorry, but your username or password is incorrect.
        </div>

        <form action="index.php" method="POST">
            <!-- Account type selection -->

            <!-- NOTE: I had to move this into the form, or else it wouldn't send
            the account type to the server. -->
            <div class="form-group">
                <label for="exampleFormControlSelect1">Select Account Type</label>
                <select class="form-control" id="exampleFormControlSelect1" name="accountType">
                    <option value="Driver">Driver</option>
                    <option value="Sponsor">Sponsor</option>
                    <option value="Admin">Admin</option>
                </select>
            </div>
            <!-- End Account type selection -->


            <div class="form-group">
                <label for="exampleInputUsername1">Username</label>
                <input type="username" class="form-control" id="exampleInputUsername1" aria-describedby="emailHelp"
                    placeholder="Username" name="username">
            </div>
            <div class="form-group">
                <label for="exampleInputPassword1">Password</label>
                <input type="password" class="form-control" id="exampleInputPassword1" placeholder="Password"
                    name="password">
                <small id="emailHelp" class="form-text text-muted">Never share your username or password with anyone else.</small>
            </div>
            <div class="form-check">
                <input type="checkbox" class="form-check-input" id="exampleCheck1">
                <label class="form-check-label" for="exampleCheck1">Remember My Username</label>
            </div>
 
    <!-- End Login Boxes -->

    <!-- SignUp box -->
      <button type="button" onclick="window.location.href = 'signup.php';" class="btn btn-primary">SignUp</button>
      
      <button type="submit" class="btn btn-primary">Login</button>
   
    </form>
    
  </div>



    <script src="https://code.jquery.com/jquery-3.3.1.slim.min.js" integrity="sha384-q8i/X
      +965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo" crossorigin="anonymous"></script>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js" integrity="sha384
      -UO2eT0CpHqdSJQ6hJty5KVphtPhzWj9WO1clHTMGa3JDZwrnQq4sF86dIHNDz0W1" crossorigin="anonymous"></script>

    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js" integrity="sha384
      -JjSmVgyd0p3pXB1rRibZUAYoIIy6OrQ6VrjIEaFf/nJGzIxFDsf4x0xIM+B07jRM" crossorigin="anonymous"></script>


</body>

</html>
