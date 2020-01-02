<?php
require_once 'php/Database.php';
require_once 'php/Navbar.php';
require_once 'php/LoginHandler.php';



LoginHandler::CheckPrivilege(UserAccount::ADMIN_ACCOUNT);


//Pulling data from database

  $db = new Database();
  $allSponsors = $db->LoadAllSponsors();

?>


/**this is the example that I need to be able to get back
*  SELECT driverId FROM DriverSponsorRelations where sponsorID - <sponsor ID>
*/
<!DOCTYPE html>
<html lang="en">

<head> 
 <meta charset="UTF-8">
    <title>Account Page</title>
    <meta name="viewport" cotent="width=device-width, initial-scale=1">

    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css"
        integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin=
        "anonymous">
    <link rel="stylesheet" href="background.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js"></script>


</head>




</html>
