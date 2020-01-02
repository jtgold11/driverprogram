<?php

require_once '../php/Database.php';
require_once '../php/Account.php';
require_once '../php/LoginHandler.php';

LoginHandler::CheckPrivilege(UserAccount::SPONSOR_ACCOUNT);

$db = new Database();
$user = $db->LoadUserFromUsername(LoginHandler::GetCurrentAccountType(), LoginHandler::GetCurrentUsername());
$sender = 'gdiadmin@truckdrivers.com';
$headers = 'From:' . $sender;

if (isset($_POST["acceptId"])) {
    // Accept an application.
    $user->AcceptDriverApplication($_POST["acceptId"]);
    // Send email notification
    $driver = LoadUserFromId(UserAccount::DRIVER_ACCOUNT, $_POST["acceptId"]);
    $msg = "You have been Accepted in the Program!";
    if(mail("{$driver->email}", "Accepted", $msg, $headers)) {
      echo "Message sent.";
    }
    else {
      echo "ERROR: Message did not send.";
    }

}
else if (isset($_POST["rejectId"])) {
    // If the app. is rejected, delete their application for this sponsor.
    $db->sql->query("DELETE FROM DriverApplications WHERE sponsorId={$user->GetID()} AND driverId={$_POST["rejectId"]};");
    //Send email notification
    $driver = LoadUserFromId(UserAccount::DRIVER_ACCOUNT, $_POST["rejectId"]);
    $msg = "Sorry, but your application has been rejected.";
    if(mail("{$driver->email}", "Rejected", $msg, $headers)) {
      echo "Message sent.";
    }
    else {
      echo "ERROR: Message did not send.";
    }

}

$applicants = $user->GetApplyingDrivers();

function PrintApplicants(&$apps) {
    foreach ($apps as $app) {
        echo "
            <div class=\"applicant-card\">
                <h4 class=\"applicant-card-header text-center\">{$app->fName} {$app->lName}</h4>
                <h5 class=\"applicant-card-username text-center\">{$app->GetUsername()}</h5>
                <p><strong>Applicant Information</strong></p>
                <p>
                    <ul>
                        <li>Name: {$app->fName} {$app->lName}</li>
                        <li>Username: {$app->GetUsername()}</li>
                        <li>Email: {$app->email}</li>
                        <li>Phone #: {$app->phoneNumber}</li>
                        <li>Address: {$app->houseNumber} {$app->street}, {$app->city}, {$app->stateCode} {$app->zipcode}</li>
                    </ul>
                </p>

                <div class=\"text-center\">
                    <form action=\"view-applicants.php\" method=\"POST\">
                        <input type=\"hidden\" name=\"acceptId\" value=\"{$app->GetID()}\"></input>
                        <button class=\"btn btn-success\" type=\"submit\">Accept</button>
                    </form>

                    <form action=\"view-applicants.php\" method=\"POST\">
                        <input type=\"hidden\" name=\"rejectId\" value=\"{$app->GetID()}\"></input>
                        <button class=\"btn btn-danger\" type=\"submit\">Reject</button>
                    </form>
                </div>
            </div>
        ";
    }
}

?>

<!doctype html>
<html lang="en">

<head>
    <title>View Driver Applications</title>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css"
        integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">


    <style>
        .applicant-card {
            border-radius: 10px;
            padding: 15px;
            box-shadow: 0px 0px 4px gray;
            transition: box-shadow 500ms;
            transition: transform 200ms;
            margin: 10px 0;
        }

        .applicant-card:hover {
            box-shadow: 0px 0px 10px gray;
            transform: translateY(-5px);
        }

        .applicant-card form {
            display: inline;
        }

        .applicant-card-header {
            font-size: 1.7em;
        }

        .applicant-card-username {
            font-size: 1.33em;
            color: gray;
            padding-bottom: 10px;
            border-bottom: 1px solid lightgray;
        }
    </style>
</head>

<body>

    <?php require_once '../php/Navbar.php'; ?>

    <div class="container">
        <div class="jumbotron text-center">
            <h1>View Driver Applicants</h1>
            <p class="lead">Accept and deny drivers who have applied to work with you.</p>
        </div>
        <?php PrintApplicants($applicants); ?>
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
