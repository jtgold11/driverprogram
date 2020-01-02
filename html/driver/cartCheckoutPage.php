<?php
require_once '../php/Navbar.php';
require_once '../php/LoginHandler.php';
require_once '../php/Database.php';
require_once '../php/Account.php';




if (!isset($_GET["sponsorId"])) {
    header("Location: cartSponsorSelect.php");
    exit;
}

$db = new Database();
$user = $db->LoadUserFromUsername(LoginHandler::GetCurrentAccountType(), LoginHandler::GetCurrentUsername());
$queryStr = "SELECT sponsorId,credits FROM DriverSponsorRelations WHERE driverId={$user->GetID()};";

$fname = "";

if ($user->GetAccountType() == UserAccount::DRIVER_ACCOUNT || $user->GetAccountType() == UserAccount::ADMIN_ACCOUNT) {
  $fname = $user->fName;
  $lname = $user->lName;
  $email = $user->email;
  $address = $user->houseNumber . " " . ltrim($user->street);
  $city = $user->city;
  $state = $user->stateCode;
  $zipcode = $user->zipcode;
  $sponsorid = $_GET["sponsorId"];
  
}
else {
  $fname = $user->GetUsername();
}


$sponsorId = $_GET["sponsorId"];

$sponsor = $db->LoadUserFromId(UserAccount::SPONSOR_ACCOUNT, $_GET["sponsorId"]);

$queryStr = "SELECT sponsorId,credits FROM DriverSponsorRelations WHERE driverId={$user->GetID()} AND sponsorId={$sponsorId};";
$result = $db->sql->query($queryStr);
if ($result && $result->num_rows > 0) {
  $row = $result->fetch_assoc();
  $credits = $row["credits"];
  
}


?>
<!-- Make sure to get the code from sponsor.php to get everything working on cart page. -->


<!DOCTYPE html>
<html>
<head>

    <meta charset="UTF-8">
    <title> Check Out</title>
    <meta name="viewport" cotent="width=device-width, initial-scale=1">

    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css"
        integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin=
        "anonymous">
    <link rel="stylesheet" href="background.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js"></script>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">


<style>
.jumbotron {
  background-color: #34ebd5;
  color: #fffff;
}
</style>


<div class="jumbotron text-center">
        <h1>Hi, <?php echo "Your total points available are  "; 
                  echo $row["credits"];
                  echo " points";
                ?>.</h1>
      </div>
    </div>






<style>

* {
  box-sizing: border-box;
}

.row {
  display: -ms-flexbox; /* IE10 */
  display: flex;
  -ms-flex-wrap: wrap; /* IE10 */
  flex-wrap: wrap;
  margin: 0 -16px;
}

.col-25 {
  -ms-flex: 25%; /* IE10 */
  flex: 25%;
}

.col-50 {
  -ms-flex: 50%; /* IE10 */
  flex: 50%;
}

.col-75 {
  -ms-flex: 75%; /* IE10 */
  flex: 75%;
}

.col-25,
.col-50,
.col-75 {
  padding: 0 16px;
}

.container {
  background-color: #f2f2f2;
  padding: 5px 20px 15px 20px;
  border: 1px solid lightgrey;
  border-radius: 3px;
}

input[type=text] {
  width: 100%;
  margin-bottom: 20px;
  padding: 12px;
  border: 1px solid #ccc;
  border-radius: 3px;
}

label {
  margin-bottom: 10px;
  display: block;
}

.icon-container {
  margin-bottom: 20px;
  padding: 7px 0;
  font-size: 24px;
}

.btn {
  background-color: #4FB2C8;
  color: white;
  border: none;
  width: 100%;
  border-radius: 3px;
  cursor: pointer;
}

.btn:hover {
  background-color: #117a8b;
}

a {
  color: #2196F3;
}

hr {
  border: 1px solid lightgrey;
}

span.price {
  float: right;
  color: grey;
}

/* Responsive layout - when the screen is less than 800px wide, make the two columns stack on top of each other instead of next to each other (also change the direction - make the "cart" column go on top) */
@media (max-width: 800px) {
  .row {
    flex-direction: column-reverse;
  }
  .col-25 {
    margin-bottom: 20px;
  }
}
</style>
</head>
<body>

<!--
  The <pre> below creates a gap in the page to produce a more aestheticly pleasing look.
-->

<pre>

</pre>

<div class="row">
  <div class="col-75">
    <div class="container">
      <form action="/action_page.php">



<!--
  Populates the field with the data from the server.
  First Name
  Email
  Address 
  etc.
-->

        <div class="row">
          <div class="col-50">
            <h3>Billing Address</h3>
            <label for="fname"><i class="fa fa-user"></i> Full Name</label>
            <input type="text" id="fname" name="firstname" value="<?= $fname ?> <?= $lname ?>">
            <label for="email"><i class="fa fa-envelope"></i> Email</label>
            <input type="text" id="email" name="email" value="<?= $email ?>">
            <label for="adr"><i class="fa fa-address-card-o"></i> Address</label>
            <input type="text" id="adr" name="address" value="<?= $address ?>">
            <label for="city"><i class="fa fa-institution"></i> City</label>
            <input type="text" id="city" name="city" value="<?= $city ?>">

            <div class="row">
              <div class="col-50">
                <label for="state">State</label>
                <input type="text" id="state" name="state" value="<?= $state ?>">
              </div>
              <div class="col-50">
                <label for="zip">Zip</label>
                <input type="text" id="zip" name="zip" value="<?= $zipcode ?>">
              </div>
            </div>
          </div>
          
<!--
  Display points for payment.
-->

          <div class="col-50">
            <h3>Payment</h3>
            <label for="fname">Points</label>          
            <?= $credits;?>
            <div class="icon-container">
               
<!--

    Need to subtract the total of the items from the total of the points. 
    
    
-->



 
            </div>
          </div>
        </div>
  
        <input type="submit" value="Continue to checkout" class="btn">
      </form>
    </div>
  </div>
  <div class="col-25">
    <div class="container">
      <h4>Cart <span class="price" style="color:black"><i class="fa fa-shopping-cart"></i> <b>4</b></span></h4>
      <p><a href="#">Product 1</a> <span class="price">$15</span></p>
      <p><a href="#">Product 2</a> <span class="price">$5</span></p>
      <p><a href="#">Product 3</a> <span class="price">$8</span></p>
      <p><a href="#">Product 4</a> <span class="price">$2</span></p>
      <hr>
      <p>Total <span class="price" style="color:black"><b>$30</b></span></p>
    </div>
  </div>
</div>

</body>
</html>
