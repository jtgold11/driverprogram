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
    Address <input type="text" name="address" value = <?php echo $user->houseNumber; ?>>
    <input type="text" name="back" value =" <?php echo $user->street; ?>"?><br />
    City <input type="text" name="city" value = <?php echo $user->city; ?>><br />
    Zip Code <input type="text" name="zip" value = <?php echo $user->zipcode; ?>><br />
    State <input type="text" name="state" value = <?php echo $user->stateCode; ?>><br />
    Phone Number <input type="text" name="phone" value = <?php echo $user->phoneNumber; ?>><br />
    Please Type Password To Confirm <input type="text" name="pass"><br />
<input type="submit" value="Submit">
</form>
<?php
$fname = $lname = $Street = $City = $zip = $phone = $pass = $state = "";
$hnum = 0;
$pass = $_POST["pass"];
$fname = $_POST["fname"];
$lname = $_POST["lname"];
$Street = $_POST["back"];
$hnum = $_POST["address"];
$City = $_POST["city"];
$zip = $_POST["zip"];
$state = $_POST["state"];
$phone = $_POST["phone"];

if(!password_verify($pass, $user->GetPasswordHash())){
	if($pass == ""){}
else
echo "Wrong password";
}
else{
$queryStr = "UPDATE Drivers SET fName='{$fname}',lName='{$lname}',phoneNumber='{$phone}',houseNumber={$hnum},street='{$Street}',city='{$City}',stateCode='{$state}',zipcode='{$zip}' WHERE id={$user->GetID()};";
$db->sql->query($queryStr);
}
?>
</html>
</body>
