<?php
/**
*    This is more for the login handling from the index page and then goes into each subfolder based 
*    on Account Type.
*
*/


require_once 'php/LoginHandler.php';
require_once 'php/Database.php';
require_once 'php/Account.php';
require_once 'php/Navbar.php';


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

//** Test to what type of User Account has logged in and redirects to the correct folder for pages.
if ($user->GetAccountType() == UserAccount::ADMIN_ACCOUNT){
          header("Location: /admin/welcome.php");

}

else if ($user->GetAccountType() == UserAccount::DRIVER_ACCOUNT){
          header("Location: /driver/welcome.php");

}

else{
          header("Location: /sponsor/welcome.php");

}
  


?>