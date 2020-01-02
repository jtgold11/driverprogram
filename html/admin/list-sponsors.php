<?php
require_once '../php/Account.php';
require_once '../php/Database.php';
require_once '../php/LoginHandler.php';
require_once '../php/Navbar.php';

//Check Privilege of User
  LoginHandler::CheckPrivilege(UserAccount::ADMIN_ACCOUNT);


//Create Database Table for Display
  $db = new Database();
  $sponsors = $db->LoadAllSponsors();
?>

<!doctype html>
<html lang="en">



<head>
    <title>View Sponsors</title>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">


    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css"
        integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">
</head>

<body>
  <div align= "center">
    <div class="jumbotron">
        <h1 class="display-3">All Sponsors</h1>
        <p class="lead">Click on a sponsor for options.</p>
    </div>
  </div>
    <div class="container">
        <div align= "center">
          <h1 class="text-center">Sponsors</h1>
          <div class="row">
            <div class="col-md-6"></div>
        </div>
            <div class="col-md-8">
            
            
            
<!-- populate list of sponsors here -------------------------------- -->
                <?php
                $i = 0;
                foreach ($sponsors as $sponsor) {
                    echo "
                    <button class=\"btn btn-primary dropdown-toggle btn-block\" data-toggle=\"collapse\" data-target=\"#sponsor{$i}\" href=\"javascript:void(0)\" style=\"margin: 5px 0;\">
                        {$sponsor->companyName}
                    </button>

                    <div class=\"collapse\" id=\"sponsor{$i}\">
                        <div class=\"card card-body\">
                            <h2>{$sponsor->companyName}</h2>
                            <strong>Info:</strong>
                            <ul>
                            <li>Email: {$sponsor->email}</li>
                            <li>Phone #: {$sponsor->phoneNumber}</li>
                            <li>Username: {$sponsor->GetUsername()}</li>
                            <li>ID: {$sponsor->GetID()}</li>
                            </ul>

                            <a href=\"javascript:void(0)\">View Profile</a>
                            <a href=\"javascript:void(0)\">Edit Sponsor</a>
                        </div>
                    </div>
                    ";

                    $i++;
                }
            
                ?>
            </div>
        </div>
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