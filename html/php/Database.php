<?php

require_once 'Account.php';

class Database {
    public $sql;
    private static $sqlConn;

    // Helper function that returns the name of the table
    // that the given type of User is stored in. For example,
    // for DRIVER_ACCOUNT, the return value is "Drivers".
    public static function GetAccountTypeTableName($accountType) {
        switch ($accountType) {
            case UserAccount::ADMIN_ACCOUNT:
                return "Admins";

            case UserAccount::SPONSOR_ACCOUNT:
                return "Sponsors";

            case UserAccount::DRIVER_ACCOUNT:
                return "Drivers";

            default: throw new Exception("Invalid account type");
        }

        return null; // should never be reached
    }

    public function __construct() {
        if (self::$sqlConn === NULL) {
            self::$sqlConn = new mysqli("mysql1.cs.clemson.edu", , "dbms_nx0t", "dbmsjtgold1", "dbms_a8qs");

            if (self::$sqlConn->connect_errno !== 0) {
                throw new Exception("Failed to connect to DB: ".$this->sql->connect_error);
            }
        }

        $this->sql = self::$sqlConn;
    }

    public function __destruct() {
    }

    /**
     * Return a string containing the last error reported by the database.
     */
    public function GetLastError() {
        return $this->sql->error;
    }

    /**
     * Return the last error code reported by the database.
     */
    public function GetLastErrorNo() {
        return $this->sql->errno;
    }

    /**
     * Queries the database to see if the given username is taken.
     *
     *      $username, string : The username to check.
     *
     * RETURNS: true if the username is taken, false otherwise.
     */
    public function IsUsernameTaken($username) {
        // Check if the username exists
        $doesExist = false;
        $queryStr = "SELECT id FROM Admins WHERE username='{$username}'";
        $queryStr .= " UNION SELECT id FROM Sponsors WHERE username='{$username}'";
        $queryStr .= " UNION SELECT id FROM Drivers WHERE username='{$username}';";

        $result = $this->sql->query($queryStr);

        if ($result && $result->num_rows > 0) {
            $doesExist = true;
            $result->free();
        }

        return $doesExist;
    }

    /**
     * Check if the given email is taken by any user.
     *
     *      $email, string : A string containing the email to check.
     *
     *  RETURNS: true if the email is taken, false otherwise.
     */
    public function IsEmailTaken($email) {
        $taken = false;
        $queryStr = "SELECT id FROM Admins WHERE email='{$email}'";
        $queryStr .= " UNION SELECT id FROM Sponsors WHERE email='{$email}'";
        $queryStr .= " UNION SELECT Id FROM Drivers WHERE email='{$email}';";

        $result = $this->sql->query($queryStr);

        if ($result && $result->num_rows > 0) {
            $taken = true;
            $result->free();
        }

        return $taken;
    }

    /**
     * Returns a UserAccount object, of the requested type,
     * with the given id. Returns false if the user doesn't
     * exist.
     *
     *      $accountType, int : The type of the account
     *      $id, int : The ID of the account you are requesting.
     *
     * RETURNS: The UserAccount with the given id and type, or
     * false if no such user exists.
     */
    public function LoadUserFromId($accountType, $id) {
        switch ($accountType) {
            case UserAccount::ADMIN_ACCOUNT:
                return $this->LoadAdminUser_("", $id);

            case UserAccount::SPONSOR_ACCOUNT:
                return $this->LoadSponsorUser_("", $id);

            case UserAccount::DRIVER_ACCOUNT:
                return $this->LoadDriverUser_("", $id);

            default: throw new Exception("Invalid account type");
        }

        return null; // should never be reached
    }

    /**
     * Returns a UserAccount object, of the requested type,
     * with the given username. Returns false if no such user exists.
     *
     *      $accountType, int : The type of the account.
     *      $username, string : The username of the account.
     *
     * RETURNS: The UserAccount with the requested type and username,
     * or false if no such account exists.
     */
    public function LoadUserFromUsername($accountType, $username) {
        switch ($accountType) {
            case UserAccount::ADMIN_ACCOUNT:
                return $this->LoadAdminUser_($username);

            case UserAccount::SPONSOR_ACCOUNT:
                return $this->LoadSponsorUser_($username);

            case UserAccount::DRIVER_ACCOUNT:
                return $this->LoadDriverUser_($username);

            default: throw new Exception("Invalid account type");
        }

        return null; // should never be reached
    }

    /**
     * Returns a UserAccount object, of the requested type,
     * with the given email. Returns false if no such user exists.
     *
     *      $accountType, int : The type of the account.
     *      $email, string : The email of the account.
     *
     * RETURNS: The UserAccount with the requested type and username,
     * or false if no such account exists.
     */
    public function LoadUserFromEmail($accountType, $email) {
        switch ($accountType) {
            case UserAccount::ADMIN_ACCOUNT:
                return $this->LoadAdminUser_("", -1, $email);

            case UserAccount::SPONSOR_ACCOUNT:
                return $this->LoadSponsorUser_("", -1, $email);

            case UserAccount::DRIVER_ACCOUNT:
                return $this->LoadDriverUser_("", -1, $email);

            default: throw new Exception("Invalid account type");
        }

        return null; // should never be reached
    }

    /**
     * Permanently delete the given user from the database.
     *
     *      $accountObject, UserAccount : The account to delete.
     *
     * RETURNS: true on success, false on failure
     */
    public function DeleteUser($accountObject) {
        $table = Database::GetAccountTypeTableName($accountObject->GetAccountType());
        $queryStr = "DELETE FROM {$table} WHERE id={$accountObject->GetID()};";
        return $this->sql->query($queryStr);
    }

    /**
     * RETURNS: An array of all sponsors registered in the database.
     * Each item in the array is a SponsorAccount object.
     */
    public function LoadAllSponsors() {
        return $this->LoadSponsorsFromQuery("SELECT * FROM Sponsors;");
    }

    /**
     * RETURNS: An array of all drivers registered in the database.
     * Each item in the array is a DriverAccount object.
     */
    public function LoadAllDrivers() {
        return $this->LoadDriversFromQuery("SELECT * FROM Drivers;");
    }

    /**
     * RETURNS: An array of all admins registered in the database.
     * Each item in the array is a AdminAccount object.
     */
    public function LoadAllAdmins() {
        return $this->LoadAdminsFromQuery("SELECT * FROM Admins;");
    }

    /**
     * Assign a driver to a sponsor.
     * RETURNS: true on success, false on failure.
     */
    public function AddDriverToSponsor($driverId, $sponsorId, $credits) {
        $queryStr = "INSERT INTO DriverSponsorRelations (sponsorId,driverId,credits) VALUES ({$sponsorId},{$driverId},{$credits});";
        return $this->sql->query($queryStr);
    }

    /**
     * Remove a driver from a sponsor.
     * RETURNS: true on success, false on failure.
     */
    public function RemoveDriverFromSponsor($driverId, $sponsorId) {
        $queryStr = "DELETE FROM DriverSponsorRelations WHERE driverId={$driverId} AND sponsorId={$sponsorId};";
        return $this->sql->query($queryStr);
    }

    /**
     * Update the credit count for a particular sponsor's driver.
     * RETURNS: true on success, false on failure.
     */
    public function UpdateDriverCredits($driverId, $sponsorId, $credits) {
        $q = "UPDATE DriverSponsorRelations SET credits={$credits} WHERE driverId={$driverId} AND sponsorId={$sponsorId};";
        return $this->sql->query($q);
    }

    /**
     * Add a new item to the catalog.
     * RETURNS: true on success, false on failure.
     */
    public function AddCatalogItem($item) {
        $available = $item->buyItNowAvailable ? 1 : 0;
        $startTime = $this->sql->real_escape_string(eBayAPI::ConvertTimeStringToMySQL($item->startTime));
        $endTime = $this->sql->real_escape_string(eBayAPI::ConvertTimeStringToMySQL($item->endTime));

        $queryStr = "INSERT INTO CatalogItems (id,title,location,viewItemURL,imageURL,shippingCost,currentPrice,conditionDisplayName,buyItNowAvailable,startTime,endTime,categoryId,categoryName) VALUES ({$item->GetID()},'{$this->sql->real_escape_string($item->title)}','{$this->sql->real_escape_string($item->location)}','{$this->sql->real_escape_string($item->viewItemURL)}','{$this->sql->real_escape_string($item->imageURL)}',{$item->shippingCost},{$item->currentPrice},'{$this->sql->real_escape_string($item->conditionDisplayName)}',{$available},'{$startTime}','{$endTime}',{$item->categoryId},'{$this->sql->real_escape_string($item->categoryName)}');";
        return $this->sql->query($queryStr);
    }

    /**
     * Update an existing catalog item.
     * RETURNS: true on success, false on failure.
     */
    public function UpdateCatalogItem($item) {
        $available = $item->buyItNowAvailable ? 1 : 0;
        $startTime = $this->sql->real_escape_string(eBayAPI::ConvertTimeStringToMySQL($item->startTime));
        $endTime = $this->sql->real_escape_string(eBayAPI::ConvertTimeStringToMySQL($item->endTime));

        $title = $this->sql->real_escape_string($item->title);
        $location = $this->sql->real_escape_string($item->location);
        $viewItemURL = $this->sql->real_escape_string($item->viewItemURL);
        $imageURL = $this->sql->real_escape_string($item->imageURL);
        $condition = $this->sql->real_escape_string($item->conditionDisplayName);
        $categoryName = $this->sql->real_escape_string($item->categoryName);

        $q = "UPDATE CatalogItems SET title='{$title}',location='{$location}',viewItemURL='{$viewItemURL}',imageURL='{$imageURL}',shippingCost={$item->shippingCost},currentPrice={$item->currentPrice},conditionDisplayName='{$condition}',buyItNowAvailable={$available},startTime='{$startTime}',endTime='{$endTime}',categoryId={$item->categoryId},categoryName='{$categoryName}' WHERE id={$item->GetID()};";
        return $this->sql->query($q);
    }

    /**
     * If the given catalog item isn't in the database, it is added.
     * If it is, it is updated to match the properties of the given
     * item.
     * RETURNS: true on success, false on failure.
     */
    public function AddOrUpdateCatalogItem($item) {
        if ($this->DoesFieldExistInTable("id", $item->GetID(), "CatalogItems")) {
            // update
            return $this->UpdateCatalogItem($item);
        }
        else {
            // add
            return $this->AddCatalogItem($item);
        }
    }

    /**
     * Return a list of CatalogItem objects from the database,
     * using the given query.
     *
     *      $query, string : The query to use.
     *
     * RETURNS: A list on success, false on failure.
     */
    public function GetCatalogItemsFromQuery($query) {
        $qresult = $this->sql->query($query);

        if ($qresult) {
            $items = [];

            while ($row = $qresult->fetch_assoc()) {
                $item = new CatalogItem();

                $item->InitializeID($row["id"]);
                $item->title = $row["title"];

                if (isset($row["location"])) {
                    $item->location = $row["location"];
                }
                else {
                    $item->location = "";
                }

                $item->viewItemURL = $row["viewItemURL"];

                if (isset($row["imageURL"])){
                    $item->imageURL = $row["imageURL"];
                }
                else {
                    $item->imageURL = "";
                }

                $item->shippingCost = $row["shippingCost"];
                $item->currentPrice = $row["currentPrice"];

                if (isset($row["conditionDisplayName"])) {
                    $item->conditionDisplayName = $row["conditionDisplayName"];
                }
                else {
                    $item->conditionDisplayName = "unknown";
                }

                $item->buyItNowAvailable = $row["buyItNowAvailable"] === 1 ? true : false;
                $item->startTime = $row["startTime"];
                $item->endTime = $row["endTime"];
                $item->categoryId = $row["categoryId"];
                $item->categoryName = $row["categoryName"];

                array_push($items, $item);
            }

            $qresult->free();
            return $items;
        }

        return false;
    }

    /**
     * Get a list from eBay Item Categories cached in the DB
     * from the given query.
     *
     *      $query, string : The query to use.
     *
     * RETURNS: A list of categories, or false on failure.
     */
    public function GetItemCategoriesFromQuery($query) {
        $qresult = $this->sql->query($query);

        if ($qresult) {
            $categories = [];
            while ($row = $qresult->fetch_assoc()) {
                $id = $row["id"];
                $pid = $row["categoryParentId"];
                $name = $row["categoryName"];
                $leaf = $row["leafCategory"];

                $c = new Category($id, $pid, $name, $leaf);
                array_push($categories, $c);
            }

            $qresult->free();
            return $categories;
        }

        return false;
    }

    /**
     * Load the given sponsor's Catalog from the database.
     *
     *      $sponsorId, int : The ID of the sponsor whom you would like to
     *                        retrieve the catalog of.
     *
     * RETURNS: The Catalog object of the Sponsor, or false on failure.
    */
    public function LoadCatalog($sponsorId) {
        $q = "SELECT * FROM Catalogs WHERE sponsorId={$sponsorId};";
        $qresult = $this->sql->query($q);

        if ($qresult && $qresult->num_rows > 0) {
            $row = $qresult->fetch_assoc();
            $id = $row["id"];
            $selMode = $row["selectionMode"];
            $qresult->free();

            // Create the catalog object.
            $catalog = new Catalog($id, $sponsorId);
            $catalog->InitializeSelectionMode($selMode);

            // Load all of the items in this catalog.
            $items = $this->GetCatalogItemsFromQuery("SELECT * FROM CatalogItems WHERE id IN (SELECT itemId FROM CatalogList WHERE catalogId={$id});");
            if ($items !== false) {
                $catalog->InitializeItems($items);
            }

            // Load all of the item filters assigned to this catalog.
            //
            $q = "SELECT * FROM CatalogItemFilters WHERE catalogId={$id};";
            $qresult = $this->sql->query($q);

            if ($qresult && $qresult->num_rows > 0) {
                $itemFilters = [];
                while ($row = $qresult->fetch_assoc()) {
                    array_push($itemFilters, [$row["itemFilter"], $row["itemFilterValue"]]);
                }
                $catalog->InitializeItemFilters($itemFilters);
                $qresult->free();
            }

            // Load this catalog's keywords.
            $q = "SELECT * FROM CatalogItemKeywords WHERE catalogId={$id};";
            $qresult = $this->sql->query($q);

            if ($qresult && $qresult->num_rows > 0) {
                $catalog->InitializeKeywords($qresult->fetch_assoc()["keywords"]);
                $qresult->free();
            }

            // Load all of the categories assigned to this catalog.
            //
            $catalog->InitializeCategories(
                $this->GetItemCategoriesFromQuery("SELECT * FROM Category WHERE id IN (SELECT categoryId FROM CatalogCategories WHERE catalogId={$id});")
            );

            return $catalog;
        }

        return false;
    }

    /**
     * Load a Notification from the database.
     */
    public function GetNotificationsFromQuery($query) {
        $qresult = $this->sql->query($query);
        if ($qresult) {
            $notifications = [];

            while ($row = $qresult->fetch_assoc()) {
                $accType = $row["targetAccType"];
            }
        }
        return false;
    }

    /**
     * Returns true if the given field, with the given value, exists
     * in the given table.
     *
     *      $rowname, string : The name of the field to check.
     *      $rowvalue, mixed : The value of the field to check against.
     *      $table, string : The name of the table to check.
     *
     * RETURNS: true if the field exists in the table, false otherwise.
     * EXAMPLE: $db->DoesFieldExistInTable('id', 123456, 'Drivers');
     */
    public function DoesFieldExistInTable($rowname, $rowvalue, $table) {
        if (is_string($rowvalue)) {
            $rowvalue = $this->sql->real_escape_string($rowvalue);
            $q = "SELECT {$rowname} FROM {$table} WHERE {$rowname}='{$rowvalue}'";
        }
        else {
            $q = "SELECT {$rowname} FROM {$table} WHERE {$rowname}={$rowvalue}";
        }

        $qresult = $this->sql->query($q);

        if ($qresult && $qresult->num_rows > 0) {
            $qresult->free();
            return true;
        }

        return false;
    }

    public function LoadAdminsFromQuery($query) {
        $qresult = $this->sql->query($query);
        $admins = array();

        if ($qresult && $qresult->num_rows > 0) {
            $row = $qresult->fetch_assoc();

            while ($row !== NULL) {
                $user = new AdminAccount();

                $user->InitializeID($row["id"]);
                $user->InitializePasswordHash($row["passwd"]);
                $user->InitializeUsername($row["username"]);

                $user->email = $row["email"];
                $user->phoneNumber = $row["phoneNumber"];
                $user->fName = $row["fName"];
                $user->lName = $row["lName"];

                array_push($admins, $user);
                $row = $qresult->fetch_assoc();
            }

            $qresult->free();
        }

        return $admins;
    }

    public function LoadSponsorsFromQuery($query) {
        $qresult = $this->sql->query($query);
        $sponsors = array();

        if ($qresult && $qresult->num_rows > 0) {
            $row = $qresult->fetch_assoc();

            while ($row !== NULL) {
                $user = new SponsorAccount();

                $user->InitializeID($row["id"]);
                $user->InitializePasswordHash($row["passwd"]);
                $user->InitializeUsername($row["username"]);

                $user->email = $row["email"];
                $user->phoneNumber = $row["phoneNumber"];
                $user->companyName = $row["companyName"];

                array_push($sponsors, $user);
                $row = $qresult->fetch_assoc();
            }

            $qresult->free();
        }

        return $sponsors;
    }

    public function LoadDriversFromQuery($query) {
        $qresult = $this->sql->query($query);
        $drivers = array();

        if ($qresult && $qresult->num_rows > 0) {
            $row = $qresult->fetch_assoc();


            while ($row !== NULL) {
                $user = new DriverAccount();

                $user->InitializeID($row["id"]);
                $user->InitializePasswordHash($row["passwd"]);
                $user->InitializeUsername($row["username"]);

                $user->email = $row["email"];
                $user->phoneNumber = $row["phoneNumber"];
                $user->fName = $row["fName"];
                $user->lName = $row["lName"];
                $user->houseNumber = $row["houseNumber"];
                $user->street = $row["street"];
                $user->city = $row["city"];
                $user->stateCode = $row["stateCode"];
                $user->zipcode = $row["zipcode"];
                $user->isActive = $row["isActive"];

                array_push($drivers, $user);
                $row = $qresult->fetch_assoc();
            }

            $qresult->free();
        }

        return $drivers;
    }

    /********************/
    /** PRIVATE METHODS */
    /********************/

    /*
        Load an Admin user.

            $username - The username of the user to look up.
            $id       - The ID of the person to look up. If not
                        equal to -1, the ID is used to look up the
                        user and not the username.
            $email    - The email of the person to look up. If not
                        empty, the email is used to look up the user.

        RETURNS: An AdminAccount object on success, false otherwise
    */
    private function LoadAdminUser_($username, $id = -1, $email = "") {
        $queryStr = "";

        if ($id == -1 && $email == "") {
            $queryStr = "SELECT * FROM Admins WHERE username='{$username}';";
        }
        else if ($email == "") {
            $queryStr = "SELECT * FROM Admins WHERE id={$id};";
        }
        else {
            $queryStr = "SELECT * FROM Admins WHERE email='{$email}';";
        }

        $admin = $this->LoadAdminsFromQuery($queryStr);

        if (count($admin) > 0) {
            return $admin[0];
        }

        return false;
    }

    /*
        Load Sponsor user.

            $username - The username of the user to look up.
            $id       - The ID of the person to look up. If not
                        equal to -1, the ID is used to look up the
                        user and not the username.
            $email    - The email of the person to look up. If not
                        empty, the email is used to look up the user.

        RETURNS: A SponsorAccount object on success, false otherwise
    */
    private function LoadSponsorUser_($username, $id = -1, $email = "") {
        $queryStr = "";

        if ($id == -1 && $email == "") {
            $queryStr = "SELECT * FROM Sponsors WHERE username='{$username}';";
        }
        else if ($email == "") {
            $queryStr = "SELECT * FROM Sponsors WHERE id={$id};";
        }
        else {
            $queryStr = "SELECT * FROM Sponsors WHERE email='{$email}';";
        }

        $sponsor = $this->LoadSponsorsFromQuery($queryStr);

        if (count($sponsor) > 0) {
            return $sponsor[0];
        }

        return false;
    }

    /*
        Load Driver user.

            $username - The username of the user to look up.
            $id       - The ID of the person to look up. If not
                        equal to -1, the ID is used to look up the
                        user and not the username.
            $email    - The email of the person to look up. If not
                        empty, the email is used to look up the user.

        RETURNS: A DriverAccount object on success, false otherwise
    */
    private function LoadDriverUser_($username, $id = -1, $email = "") {
        $queryStr = "";

        if ($id == -1 && $email == "") {
            $queryStr = "SELECT * FROM Drivers WHERE username='{$username}';";
        }
        else if ($email == "") {
            $queryStr = "SELECT * FROM Drivers WHERE id={$id};";
        }
        else {
            $queryStr = "SELECT * FROM Drivers WHERE email='{$email}';";
        }

        $driver = $this->LoadDriversFromQuery($queryStr);

        if (count($driver) > 0) {
            return $driver[0];
        }

        return false;
    }
}

?>
