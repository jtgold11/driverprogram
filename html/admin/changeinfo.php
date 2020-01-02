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
?>

  <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>">
    First Name <input type="text" name="fname" value = <?php echo $user->fName; ?>><br />
    Last Name <input type="text" name="lname" value = <?php echo $user->lName; ?>><br />
    Email <input type="text" name="email" value = <?php echo $user->email; ?>><br />
    Phone Number <input type="text" name="phone" value = <?php echo $user->phoneNumber; ?>><br />
    Please Type Password To Confirm <input type="text" name="pass"><br />
<input type="submit" value="Submit">
</form>
<?php
$fname = $lname = $phone = $pass = $email = "";
$pass = $_POST["pass"];
$fname = $_POST["fname"];
$lname = $_POST["lname"];
$email = $_POST["email"];
$phone = $_POST["phone"];

if(!password_verify($pass, $user->GetPasswordHash())){
	if($pass == ""){}
else
echo "Wrong password";
}
else{
$queryStr = "UPDATE Admins SET fName='{$fname}',lName='{$lname}',phoneNumber='{$phone}',email='{$email}' WHERE id={$user->GetID()};";
$db->sql->query($queryStr);
}
?>
</html>
</body>
