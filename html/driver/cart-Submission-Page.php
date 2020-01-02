<?php

require_once '../php/LoginHandler.php';
require_once '../php/Database.php';
require_once '../php/Account.php';


?>



<!DOCTYPE html>
<html lang="en">
<head>
  <body>






<!--
  Add Item to purchase history table in database for use with the cart page.
-->
<?php
$itemId = $_GET[itemId];

?>


<!--
 Redirect back to the catalog page.
-->

<?php echo $_COOKIE["itemId"];
echo $_COOKIE["sponsorID"];
echo $_COOKIE["time"];
echo $_COOKIE["driverId"];
?>
<!--
   <meta http-equiv = "refresh" content = "0; url = view-catalog.php?sponsorId=<?= $_COOKIE["sponsorID"] ?>" />
-->
</body>
</head>