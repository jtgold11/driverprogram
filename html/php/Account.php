<?php 

require_once 'Database.php';

abstract class UserAccount {
    // Types of user accounts.
    //
    const ADMIN_ACCOUNT = 1;
    const SPONSOR_ACCOUNT = 2;
    const DRIVER_ACCOUNT = 3;

    // Members
    //
    public $email = "";
    public $phoneNumber = "";

    protected $db;
    protected $accountType;

    private $id = -1;
    private $username = "";
    private $passwdHash = "";

    // Methods
    //

    /**
     * Construct an empty UserAccount object.
     */
    public function __construct() {
        $this->db = new Database();
        // no need to set $accountType since
        // this is an abstract base class
    }

    public function __destruct() {
        
    }

    /**
     * RETURNS: Return this account's username (a string).
     */
    public function GetUsername() {
        return $this->username;
    }
    
    /**
     * Initialize the User's username if it hasn't 
     * been set already. Otherwise, does nothing.
     * 
     *      $usernm, string : The username to initialize the
     *                        account with.
     * 
     * RETURNS: Nothing
     */
    public function InitializeUsername($usernm) {
        if (empty($this->username)) {
            $this->username = $usernm;
        }
    }

    /**
     * Set a new password for this account.
     * 
     *      $new_passwd, string : The new password for this account.
     * 
     * RETURNS: Nothing
     */
    public function SetNewPassword($new_passwd) {
        $this->passwdHash = password_hash($new_passwd, PASSWORD_BCRYPT);
    }

    /**
     * If the password hash is currently uninitialized, set
     * the password hash. Otherwise, this function does nothing.
     * 
     * This is meant to be used by the Database class
     * when returning a populated UserAccount object.
     * For now, I don't think there's too much reason to use
     * this otherwise.
     * 
     *      $passwd_hash, string : The user's hashed password.
     * 
     * RETURNS: Nothing
    */
    public function InitializePasswordHash($passwd_hash) {
        if (empty($this->passwdHash)){
            $this->passwdHash = $passwd_hash;
        }
    }

    /**
     * RETURNS: The account's hashed password.
     */
    public function GetPasswordHash() {
        return $this->passwdHash;
    }

    /**
     * RETURNS: The account's ID.
     */
    public function GetID() {
        return $this->id;
    }

    /**
     * RETURNS: The account's type. This is one of:
     * UserAccount::ADMIN_ACCOUNT, UserAccount::SPONSOR_ACCOUNT, or
     * UserAccount::DRIVER_ACCOUNT.
     */
    public function GetAccountType() {
        return $this->accountType;
    }

    /**
     * Set a new ID for this account, if the ID has not already been set.
     * If the id is already set (i.e., it's not -1), this function does
     * nothing.
     * 
     * This is meant to be used by the Database class
     * when returning a populated UserAccount object.
     * For now, I don't think there's too much reason to use
     * this otherwise.
     * 
     *      $newId, int : The account's ID.
     * 
     * RETURNS: Nothing
     */
    public function InitializeID($newId) {
        if ($this->id == -1) {
            $this->id = $newId;
        }
    }

    /**
     * Register a new user in the database.
     * This UserAccount object is then populated with
     * the ID given to it by the database, which you can
     * get by calling GetID().
     * 
     * Note: No need to call FlushToDatabase()
     * after calling Register().
     * 
     * RETURNS: true on success, false on failure.
     */
    abstract public function Register();

    /**
     * Write the existing user into the database, recording any
     * changes that have been made.
     * 
     * RETURNS: true on success, false on failure.
     */
    abstract public function FlushToDatabase();


    /**
     * Queries the database for this user's ID, based on their
     * username.
     */
    protected function QueryForID_() {
        $table = Database::GetAccountTypeTableName($this->accountType);
        $queryStr = "SELECT id FROM {$table} WHERE username='{$this->username}';";

        $result = $this->db->sql->query($queryStr);

        if ($result && $result->num_rows > 0) {
            $this->id = $result->fetch_assoc()["id"];
            $result->free();
        }
    }

    protected function IsUsernameOrEmailTaken_() {
        return $this->db->IsUsernameTaken($this->username) ||
            $this->db->IsEmailTaken($this->email);
    }
}

class AdminAccount extends UserAccount {

    public $fName = "";
    public $lName = "";

    public function __construct() {
        parent::__construct();
        $this->accountType = UserAccount::ADMIN_ACCOUNT;
    }

    public function __destruct() {
        parent::__destruct();
    }

    public function Register() {
        // insert new admin into the db
        //
        if ($this->IsUsernameOrEmailTaken_()) return false;

        $queryStr = "INSERT INTO Admins (email,passwd,fName,lName,username,phoneNumber) VALUES ('{$this->email}','{$this->GetPasswordHash()}','{$this->fName}','{$this->lName}','{$this->GetUsername()}','{$this->phoneNumber}');";
        if ($this->db->sql->query($queryStr)) {
            $this->QueryForID_();
            return true;
        }
        return false;
    }

    public function FlushToDatabase() {
        $queryStr = "UPDATE Admins SET email='{$this->email}',passwd='{$this->GetPasswordHash()}',fName='{$this->fName}',lName='{$this->lName}',phoneNumber='{$this->phoneNumber}' WHERE id={$this->GetID()};";
        return $this->db->sql->query($queryStr);
    }
}

class SponsorAccount extends UserAccount {

    private $catalogId = -1;
    
    public $companyName = "";

    public $driverIds = [];
    public $credits = [];

    public function __construct() {
        parent::__construct();
        $this->accountType = UserAccount::SPONSOR_ACCOUNT;
    }

    public function __destruct() {
        parent::__destruct();
    }

    /**
     * Load this sponsor's drivers and their associated credits.
     * The IDs are put into the sponsors's $driverIds array,
     * while the credit counts are put into the $credits array,
     * which maps driver IDs to credit counts. For example:
     * 
     * $some_sponsor->credits[$a_driver_id] = 123;
     * 
     * RETURNS: true on success, false on failure.
     */
    public function LoadDriverIdsAndCredits() {
        $q = "SELECT driverId,credits FROM DriverSponsorRelations WHERE sponsorId={$this->GetID()};";
        $qresult = $this->db->sql->query($q);

        if ($qresult && $qresult->num_rows > 0) {
            for ($row = $qresult->fetch_assoc(); $row !== NULL; $row = $qresult->fetch_assoc()) {
                $id = $row["driverId"];
                array_push($this->driverIds, $id);
                $this->credits[$id] = $row["credits"];
            }

            $qresult->free();
        }
        else if (!$qresult) {
            return false;
        }

        return true;
    }

    /**
     * RETURNS: An array of drivers that are applying to this sponsor,
     * or false on failure.
     */
    public function GetApplyingDrivers() {
        return $this->db->LoadDriversFromQuery("SELECT * FROM Drivers WHERE id IN (SELECT driverId FROM DriverApplications WHERE sponsorId={$this->GetID()});");
    }

    /**
     * Accept the application of the driver with the given ID.
     * 
     *      $driverId, int : The ID of the driver whose application is being accepted.
     * 
     * RETURNS: true on success, false on failure or if there was no application
     * for this driver to begin with
     */
    public function AcceptDriverApplication($driverId) {
        // 1. Check if there is an application from this driver to this sponsor.
        //      a. If so, remove the application, add the driver to this sponsor,
        //      and set the Driver's active status to 1.
        //
        //      b. If not, return false.

        $qresult = $this->db->sql->query("SELECT * FROM DriverApplications WHERE sponsorId={$this->GetID()} AND driverId={$driverId};");
        if ($qresult && $qresult->num_rows > 0) {
            $qresult->free();

            if (!$this->db->AddDriverToSponsor($driverId, $this->GetID(), 0)) {
                return false;
            }

            if (!$this->db->sql->query("DELETE FROM DriverApplications WHERE sponsorId={$this->GetID()} AND driverId={$driverId};")) {
                return false;
            }

            if (!$this->db->sql->query("UPDATE Drivers SET isActive=1 WHERE id={$driverId};")) {
                return false;
            }

            return true;
        }

        return false;
    }

    public function Register() {
        if ($this->IsUsernameOrEmailTaken_()) return false;

        $queryStr = "INSERT INTO Sponsors (companyName,username,email,passwd,phoneNumber) VALUES ('{$this->companyName}','{$this->GetUsername()}','{$this->email}','{$this->GetPasswordHash()}','{$this->phoneNumber}');";
        if ($this->db->sql->query($queryStr)) {
            $this->QueryForID_();

            // Create an empty catalog for this sponsor
            $queryStr = "INSERT INTO Catalogs (sponsorId, selectionMode) VALUES ({$this->GetID()}, 'CATEGORY');";
            $this->db->sql->query($queryStr);

            return true;
        }
        
        return false;
    }

    public function FlushToDatabase() {
        $queryStr = "UPDATE Sponsors SET companyName='{$this->companyName}',email='{$this->email}',passwd='{$this->GetPasswordHash()}',phoneNumber='{$this->phoneNumber}' WHERE id={$this->GetID()};";
        return $this->db->sql->query($queryStr);
    }

    /**
     * Return this sponsor's catalog ID.
     * RETURNS: The catalog ID, or false if they have no catalog or
     * an error occurred.
     */
    public function GetCatalogID() {
        if ($this->catalogId === -1) {
            $qresult = $this->db->sql->query("SELECT id FROM Catalogs WHERE sponsorId={$this->GetID()};");
            
            if ($qresult && $qresult->num_rows > 0) {
                $this->catalogId = $qresult->fetch_assoc()["id"];
            }
            else {
                return false;
            }
        }
        
        return $this->catalogId;
    }
}

class DriverAccount extends UserAccount {

    // Name information
    public $fName = "";
    public $lName = "";

    // Address information
    public $houseNumber = 0;
    public $street = "";
    public $city = "";
    public $stateCode = "";
    public $zipcode = "";

    // if this driver is active (assigned to a sponsor) or not yet
    public $isActive = false;

    // This driver's sponsors.
    public $sponsorIds = [];

    // The credits this driver has with each sponsor.
    // The keys to this array are the individual sponsor IDs
    // found in $sponsorIds.
    public $credits = [];

    public function __construct() {
        parent::__construct();
        $this->accountType = UserAccount::DRIVER_ACCOUNT;
    }

    public function __destruct() {
        parent::__destruct();
    }

    /**
     * Load this driver's sponsors and their associated credits.
     * The IDs are put into the driver's $sponsorIds array,
     * while the credit counts are put into the $credits array,
     * which maps sponsor IDs to credit counts. For example:
     * 
     * $some_driver->credits[$a_sponsor_id] = 123;
     * 
     * RETURNS: true on success, false on failure.
     */
    public function LoadSponsorIdsAndCredits() {
        $q = "SELECT sponsorId,credits FROM DriverSponsorRelations WHERE driverId={$this->GetID()};";
        $qresult = $this->db->sql->query($q);

        if ($qresult && $qresult->num_rows > 0) {
            for ($row = $qresult->fetch_assoc(); $row !== NULL; $row = $qresult->fetch_assoc()) {
                $id = $row["sponsorId"];
                array_push($this->sponsorIds, $id);
                $this->credits[$id] = $row["credits"];
            }

            $qresult->free();
        }
        else if (!$qresult) {
            return false;
        }

        return true;
    }

    /**
     * Request to become a driver under the given sponsor.
     *      $sponsorId, int : The ID of the sponsor this driver is applying to.
     * 
     * RETURNS: true on success, false on failure
     */
    public function ApplyToSponsor($sponsorId) {
        return $this->db->sql->query("INSERT INTO DriverApplications (sponsorId,driverId) VALUES ({$sponsorId},{$this->GetID()});");
    }

    public function Register() {
        if ($this->IsUsernameOrEmailTaken_()) return false;

        $isActive = ($this->isActive ? 1 : 0);
        $queryStr = "INSERT INTO Drivers (fName,lName,username,passwd,email,phoneNumber,houseNumber,street,city,stateCode,zipcode,isActive) VALUES ('{$this->fName}','{$this->lName}','{$this->GetUsername()}','{$this->GetPasswordHash()}','{$this->email}','{$this->phoneNumber}',{$this->houseNumber},'{$this->street}','{$this->city}','{$this->stateCode}','{$this->zipcode}',{$isActive});";
        if ($this->db->sql->query($queryStr)) {
            $this->QueryForID_(); // grab the ID generated by the database
            return true;
        }
        return false;
    }

    public function FlushToDatabase() {
        $queryStr = "UPDATE Drivers SET fName='{$this->fName}',lName='{$this->lName}',passwd='{$this->GetPasswordHash()}',email='{$this->email}',phoneNumber='{$this->phoneNumber}',houseNumber={$this->houseNumber},street='{$this->street}',city='{$this->city}',stateCode='{$this->stateCode}',zipcode='{$this->zipcode}' WHERE id={$this->GetID()};";
        return $this->db->sql->query($queryStr);
    }
}

?>