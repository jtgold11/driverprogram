<?php

require_once '../php/Database.php';
require_once '../php/Catalog.php';
require_once '../php/LoginHandler.php';

LoginHandler::CheckPrivilege(UserAccount::DRIVER_ACCOUNT);

//Creates a cookie to hold data for sponsor id, item id, price, 


$cookie_sponsorID = $_GET["sponsorId"];
$cookie_driverID = $_GET["driverId"];
$cookie_time = date('Y-m-d');
setcookie(sponsorID, $cookie_sponsorID, time() + (86400 * 30), "/"); // 86400 = 1 day
setcookie(driverId, $cookie_driverID, time() + (86400 * 30), "/");
setcookie(time, $cookie_time, time() + (86400 * 30), "/");

// get the requested sponsor's catalog from the URL parameter
if (!isset($_GET["sponsorId"])) {
    header("Location: select-sponsor-catalog.php");
    exit;
}

$pageNo = 0;
$itemsPerPage = 50;

if (isset($_GET["page"])) $pageNo = $_GET["page"];
if (isset($_GET["itemsPerPage"])) $itemsPerPage = $_GET["itemsPerPage"];

// check to see that the given sponsor belongs to the driver
$db = new Database();
$user = $db->LoadUserFromUsername(LoginHandler::GetCurrentAccountType(), LoginHandler::GetCurrentUsername());
if (!$user) {
    error_log("Failed to load user from database");
    header("HTTP/1.1 500 Internal Server Error");
    exit;
}

$user->LoadSponsorIdsAndCredits();

// if this driver doesn't have the given sponsor, deny access to this page
if (!in_array($_GET["sponsorId"], $user->sponsorIds)) {
    header("HTTP/1.1 403 Access Denied");
    exit;
}

// get the sponsor's catalog
$sponsor = $db->LoadUserFromId(UserAccount::SPONSOR_ACCOUNT, $_GET["sponsorId"]);
if (!$sponsor) {
    error_log("Failed to load sponsor");
    header("HTTP/1.1 500 Internal Server Error");
    exit;
}

$catalogId = $sponsor->GetCatalogID();

// get the total number of items in the catalog
$itemCount = 0;
$qresult = $db->sql->query("SELECT id FROM CatalogItems WHERE id IN (SELECT itemId FROM CatalogList WHERE catalogId={$catalogId});");

if ($qresult) {
    $itemCount = $qresult->num_rows;
    $qresult->close();
}
else {
    error_log("Failed to get total number of items in catalog");
    header("HTTP/1.1 500 Internal Server Error");
    exit;
}

// get the catalog items in this page
$offset = $itemsPerPage * $pageNo;
$items = $db->GetCatalogItemsFromQuery("SELECT * FROM CatalogItems WHERE id IN (SELECT itemId FROM CatalogList WHERE catalogId={$catalogId}) ORDER BY title LIMIT {$offset},{$itemsPerPage};");
$pageCount = ceil($itemCount / $itemsPerPage);

if (!$items) {
    header("HTTP/1.1 500 Internal Server Error");
    error_log("Failed to get catalog items: ".$db->GetLastError());
    exit;
}

function PrintItems(&$items) {
    for ($i = 0; $i < count($items); ++$i) {
        $item = $items[$i];

        $shipPrice = $item->shippingCost == 0 ? "FREE!" : "$".$item->shippingCost;
        $total = $item->currentPrice + $item->shippingCost;

        echo "
            <div class=\"item-preview\">
                <h5>{$item->title}</h5>

                <div class=\"item-preview-description\">
                    <div>
                        <a href=\"{$item->viewItemURL}\"><img class=\"item-image\" src=\"{$item->imageURL}\"/></a>
                    </div>

                    <div class=\"item-cost-panel\">
                        <table>
                            <tr>
                                <td>Price</td>
                                <td><span class=\"price-text\">\${$item->currentPrice}</span></td>
                            </tr>
                            <tr>
                                <td>Shipping</td>
                                <td><span class=\"price-text\">{$shipPrice}</span></td>
                            </tr>
                            <tr style=\"box-shadow:0 0 3px green;background-color:#d6ffc9;\">
                                <td><strong>Total</strong></td>
                                <td><span class=\"price-text\">\${$total}</span></td>
                            </tr>
                        </table>
                    </div>

                    <div class=\"d-flex align-items-center\">
                        <div class=\"d-flex justify-content-center\">
                            <a href=\"cart-Submission-Page.php?itemId={$item->GetID()}\" class=\"btn btn-primary mr-3 mb-1\">Add to Cart</a>

                            <a href=\"{$item->viewItemURL}\" class=\"btn btn-primary mr-3 mb-1\">Go to eBay</a>
                        </div>
                    </div>
                </div>
            </div>
        ";
    }
}

function PrintPageBreadcrumbs($currentPage, $pageCount, $itemsPerPage, $sponsorId, $itemCount) {
    echo "<ol class=\"breadcrumb align-items-center\">";
    for ($i = 1; $i <= $pageCount; ++$i) {
        if ($i !== $currentPage+1){
            echo "<li class=\"breadcrumb-item\"><a href=\"".GetPageURL($i-1, $itemsPerPage, $sponsorId)."\">{$i}</a></li>";
        }
        else {
            echo "<li class=\"breadcrumb-item active\">{$i}</li>";
        }
    }

    // create a list of the available items-per-page counts
    $pageSizeOptions = [];
    for ($j = 1; $j <= 20; ++$j) {
        array_push($pageSizeOptions, $j*5);
    }
    
    // if a custom value was selected, add it to the array
    // if not already in it
    if (!in_array($itemsPerPage, $pageSizeOptions)) {
        array_push($pageSizeOptions, $itemsPerPage);
    }

    sort($pageSizeOptions);

    // Create items-per-page dropdown box
    echo "<li class=\"ml-auto\"><label for=\"itemsPerPageSelect\">Items Per Page: <select class=\"form-control d-inline w-auto\" id=\"itemsPerPageSelect\"></label>";
    foreach ($pageSizeOptions as $sizeOption) {
        // calculate the new page this option should send us to:
        //  1. If the current page is greater than the new number of pages,
        //  the new max. page number will be the new page.
        //  2. If the current page is less than the new number of pages,
        //  the new page will not change.
        $newPageCount = ceil($itemCount / $sizeOption);
        if ($currentPage >= $newPageCount) $currentPage = $newPageCount-1; // subtract 1 because zero-indexed

        echo "<option value=\"{$sizeOption} {$currentPage} {$sponsorId}\"";
        if ($sizeOption == $itemsPerPage) {
            echo " selected=\"selected\"";
        }
        echo ">{$sizeOption}</option>";
    }
    echo "</select></ol>";
}

function GetPageURL($page, $itemsPerPage, $sponsorId) {
    $scriptName = basename($_SERVER['PHP_SELF']);
    return "./".$scriptName."?sponsorId={$sponsorId}&page={$page}&itemsPerPage={$itemsPerPage}";
}


?>

<!doctype html>
<html lang="en">

<head>
    <title>View Catalog</title>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css"
        integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">

    <style>
        .item-preview {
            display: inline-block;
            max-width: 350px;
            border-radius: 5px;
            margin: 10px;
            padding: 5px;
            box-shadow: 1px 1px 5px #ccc;
            transition: box-shadow 100ms;
            transition: transform 100ms;
        }

        @media screen and (max-width: 1000px) {
            .item-preview {
                display: block;
                max-width: 100%;
            }
        }

        .item-preview > h5 {
            font-weight: bold;
            border-bottom: 1px solid #ddd;
            margin-bottom: 1em;
        }

        .item-preview:nth-child(odd) {
            background-color: #efefef;
        }

        .item-preview:hover {
            box-shadow: 0px 0px 2px #222;
            transform: translateY(-2px);
        }

        .item-preview-description {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 15px;
        }

        .item-cost-panel {
            background-color: #eee;
            border-radius: 10px;
            padding: 15px;
            margin-right: 15px;
            margin-bottom: 10px;
        }

        .item-preview:nth-child(odd) .item-cost-panel {
            background-color: white;
        }

        .item-image {
            margin-right: 10px;
            margin-bottom: 10px;
            box-shadow: 1px 1px 5px gray;
        }

        .price-text {
            color: green;
            font-weight: bold;
        }

        td {
            padding-right: 15px;
        }
    </style>
</head>

<body>
    <?php require_once '../php/Navbar.php'; ?>

    <div class="container">
        <?php
            echo "
                <div class=\"jumbotron text-center\">
                    <h1 class=\"display-lead\">{$sponsor->companyName}'s Catalog</h1>
                    <p class=\"lead\">View items offered by your sponsor.</p>
                </div>
            ";
        ?>

        <div style="text-align:center;">
            <?php PrintPageBreadcrumbs($pageNo, $pageCount, $itemsPerPage, $sponsor->GetID(), $itemCount); ?>
        </div>

        <div class="d-flex flex-wrap">
            <?php PrintItems($items); ?>
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

    <script>
        $(document).ready(function(){
            $('#itemsPerPageSelect').change(function(){
                let tokens = this.value.split(" ");

                let itemsPerPage = tokens[0];
                let currentPage = tokens[1];
                let sponsorId = tokens[2];

                let newUrl = window.location.pathname;
                newUrl += "?sponsorId="+sponsorId;
                newUrl += "&page="+currentPage;
                newUrl += "&itemsPerPage="+itemsPerPage;

                window.location.href = newUrl;
            });
        });
    </script>
</body>

</html>