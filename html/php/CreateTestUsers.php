<?php

require_once 'Database.php';
require_once 'Account.php';

$fNames = array("Bob", "Bridgette", "Caden", "Mary", "Justin", "Tina", "Jake", "Eileen", "Silas", "Aneri", "Joey", "Alicia");
$lNames = array("Gold", "Smith", "Jones", "Miller", "Cooper", "Washington", "Clark", "Robinson", "Rodriguez", "Perez", "Howard", "Jimenez");

$N = 20;
$db = new Database();

// delete all test Driver-Sponsor relationships
if (!$db->sql->query("
DELETE FROM DriverSponsorRelations WHERE driverId IN (
    SELECT id FROM Drivers WHERE username LIKE 'test_%'
) AND sponsorId IN (
    SELECT id FROM Sponsors WHERE username LIKE 'test_%'
);")) {
    echo "Error deleting DriverSponsorRelations: '{$db->GetLastError()}'";
};
echo "Deleted old test driver-sponsor relationships.\n";

// delete all test users
$db->sql->query("DELETE FROM Admins WHERE username LIKE 'test_%';");
$db->sql->query("DELETE FROM Drivers WHERE username LIKE 'test_%';");
$db->sql->query("DELETE FROM Sponsors WHERE username LIKE 'test_%';");
echo "Deleted old test users.\n";

// create test users
//

// Admins
for ($i = 0; $i < $N; $i++) {
    $name = "test_admin{$i}";
    $acc = new AdminAccount();
    
    $acc->InitializeUsername($name);
    $acc->SetNewPassword($name);

    $acc->email = $name . "@example.com";
    $acc->phoneNumber = "1234567890";

    $acc->fName = $fNames[rand(0, count($fNames)-1)];
    $acc->lName = $lNames[rand(0, count($lNames)-1)];

    if ($acc->Register()) {
        echo "Created admin '{$name}' with ID {$acc->GetID()}.\n";
    }
    else {
        echo "Failed to create user '{$name}'.\n";
        echo "Error: '{$db->GetLastError()}'\n\n";
    }
}
echo "\n";

// Sponsors
$sponsorIds = array();

for ($i = 0; $i < $N; $i++) {
    $name = "test_sponsor{$i}";
    $acc = new SponsorAccount();

    $acc->InitializeUsername($name);
    $acc->SetNewPassword($name);

    $acc->email = $name . "@example.com";
    $acc->phoneNumber = "1234567890";

    $acc->companyName = "{$name}, Inc.";
    
    if ($acc->Register()) {
        echo "Created sponsor '{$name}' with ID {$acc->GetID()}.\n";
        array_push($sponsorIds, $acc->GetID());
    }
    else {
        echo "Failed to create user '{$name}'.\n";
        echo "Error: '{$db->GetLastError()}'.\n\n";
    }
}

echo "\n";

// Drivers
for ($i = 0; $i < $N; $i++) {
    $name = "test_driver{$i}";
    $acc = new DriverAccount();

    $acc->InitializeUsername($name);
    $acc->SetNewPassword($name);

    $acc->email = $name . "@example.com";
    $acc->phoneNumber = "1234567890";

    $acc->fName = $fNames[rand(0, count($fNames)-1)];
    $acc->lName = $lNames[rand(0, count($lNames)-1)];
    $acc->houseNumber = rand(100, 500);
    $acc->street = "Perimeter Rd.";
    $acc->city = "Clemson";
    $acc->stateCode = "SC";
    $acc->zipcode = "29631";
    $acc->isActive = true;

    if ($acc->Register()) {
        echo "Created driver '{$name}' with ID {$acc->GetID()}.\n";
    }
    else {
        echo "Failed to create user '{$name}'.\n";
        echo "Error: '{$db->GetLastError()}'\n\n";
    }

    // give this driver some random sponsors and random credits
    for ($j = 0; $j < 3; ++$j) {
        $db->AddDriverToSponsor($acc->GetID(), $sponsorIds[rand(0, count($sponsorIds)-1)], rand(0,200));
    }
}

?>