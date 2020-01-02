<?php

require_once '../php/Navbar.php';
require_once '../php/Database.php';
require_once '../php/Account.php';
require_once '../php/LoginHandler.php';

LoginHandler::CheckPrivilege(UserAccount::DRIVER_ACCOUNT);

// Redirect to catalog page if they've submitted the form.
if (isset($_GET["sponsorId"])) {
    header("Location: view-catalog.php?sponsorId={$_GET["sponsorId"]}");
    exit;
}

// get all of this driver's sponsors
$db = new Database();
$user = $db->LoadUserFromUsername(LoginHandler::GetCurrentAccountType(), LoginHandler::GetCurrentUsername());
if (!$user) {
    error_log("Failed to get driver user");
    header("HTTP/1.1 500 Internal Server Error");
    exit;
}

$user->LoadSponsorIdsAndCredits();
$sponsors = [];

foreach ($user->sponsorIds as $id) {
    array_push($sponsors, $db->LoadUserFromId(UserAccount::SPONSOR_ACCOUNT, $id));
}

function PrintSelectBox(&$sponsors) {
    echo "<select class=\"form-control w-auto d-inline-block\" name=\"sponsorId\">";
    foreach ($sponsors as $sponsor) {
        echo "<option value=\"{$sponsor->GetID()}\">{$sponsor->companyName}</option>";
    }
    echo "</select>";
}

?>

<!doctype html>
<html lang="en">

<head>
    <title>Select Sponsor's Catalog</title>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css"
        integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">
</head>

<body>

    <!--#include file="Navbar.php" -->

    <div class="container text-center">
        <form action="select-sponsor-catalog.php" method="GET">
            <fieldset>
                <legend>Select which sponsor's catalog to view.</legend>
                <?php PrintSelectBox($sponsors); ?>
                <button class="btn btn-primary" type="submit">Go</button>
            </fieldset>
        </form>
    </div>

    <!-- Optional JavaScript -->
    <!-- jQuery first, then Popper.js, then Bootstrap JS -->
    <script src="https://code.jquery.com/jquery-3.3.1.slim.min.js"
        integrity="sha384-q8i/X+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo" crossorigin="anonymous">
    </script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js"
        integrity="sha384-UO2eT0CpHqdSJQ6hJty5KVphtPhzWj9WO1clHTMGa3JDZwrnQq4sF86dIHNDz0W1" crossorigin="anonymous">
    </script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js"
        integrity="sha384-JjSmVgyd0p3pXB1rRibZUAYoIIy6OrQ6VrjIEaFf/nJGzIxFDsf4x0xIM+B07jRM" crossorigin="anonymous">
    </script>
</body>

</html>
