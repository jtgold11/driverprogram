<?php
require_once '../php/LoginHandler.php';
require_once '../php/Database.php';
require_once '../php/Account.php';
require_once '../php/Catalog.php';
require_once '../php/Navbar.php';


// Get the user's first name or, if they're a sponsor,
// their username.
// Same code from welcome page
$db = new Database();
$catalog = new CatalogItem($db);
$user = $db->LoadUserFromUsername(LoginHandler::GetCurrentAccountType(), LoginHandler::GetCurrentUsername());
$fname = "";

if ($user->GetAccountType() == UserAccount::DRIVER_ACCOUNT || $user->GetAccountType() == UserAccount::ADMIN_ACCOUNT) {
  $fname = $user->fName;
}
else {
  $fname = $user->GetUsername();
}

$itemId = $catalog->GetId();
$price = $catalog->GetPrice();
$available = $catalog->GetAvailable();

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

<body>
  <p>
    <b>
    Welcome to your catalog, <?php echo $fname; ?>.
    </b>
  </p>
  <div class="container">
    <h3>Items</h3>
      <pre class="pre-scrollable">
        <div class="container">
          <img src="https://static6.depositphotos.com/thumbs/1032712/image/614/6147938/api_thumb_450.jpg" alt="No Image" height="78" width="78">
          <p>
            Id:<?php echo $itemId; ?>
            Price:<?php echo $price; ?>
            Availability:<?php echo $available; ?>
          </p>
        </div>
      </pre>
    <h3>Promos</h3>
      <pre class="pre-scrollable">
        This will be where items will be placed
        for recent purchases by the driver.</pre>
  </div>
</body>
