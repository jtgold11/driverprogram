<?php

require_once 'Database.php';
require_once 'Account.php';

class LoginHandler {
    /**
     * End the current session.
     *
     * RETURNS: Nothing
     */
    public static function Logout() {
        session_destroy();
        $_SESSION = array();
    }

    /**
     * RETURNS: true if the current client is logged in,
     * false otherwise.
     */
    public static function IsLoggedIn() {
      session_write_close();
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        return isset($_SESSION["loggedin"]);
    }

    /**
     * RETURNS: the account type of the current client,
     * or false if they aren't logged in.
     */
    public static function GetCurrentAccountType() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (isset($_SESSION["accountType"]))
            return $_SESSION["accountType"];
        else
            return false;
    }

    /**
     * RETURNS: the username of the current user, or
     * false if they aren't logged in.
     */
    public static function GetCurrentUsername() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (isset($_SESSION["username"]))
            return $_SESSION["username"];
        else
            return false;
    }

    /**
     * Check if the user is logged in and has the given account type.
     * If they are not logged in, they are re-directed to the login page.
     * If they are logged in but do not have the required account type,
     * they are sent a 403 Access Denied response. Otherwise,
     * this function allows the user to continue.
     *
     * RETURNS: Nothing
     */
    public static function CheckPrivilege($requiredAccType) {
        if (!self::IsLoggedIn()) {
            header("Location: /index.php");
            exit;
        }
        else if (self::GetCurrentAccountType() !== $requiredAccType) {
            header("HTTP/1.1 403 Access Denied");
            exit;
        }
    }

    // Members
    //
    public $username;
    public $password;
    public $accountType;
    public $email;

    protected $db;

    // Methods
    //

    /**
     * Construct a new LoginHandler object, which can handle the login
     * of a given user. Either the username or email, or both, must be
     * provided in order to log the user in.
     *
     *      $accType, int : The type of the account.
     *      $username_, string : The username of the account. Leave
     *                           blank if unknown.
     *      $email_, string : The email of the account. Leave blank
     *                        if unknown.
     *      $passwd, string : The password of the account
     *
     * RETURNS: Nothing
     */
    public function __construct($accType, $username_, $email_, $passwd) {
        $this->db = new Database();
        $this->username = $username_;
        $this->email = $email_;
        $this->password = $passwd;
        $this->accountType = $accType;

        if ($accType !== UserAccount::ADMIN_ACCOUNT && $accType !== UserAccount::SPONSOR_ACCOUNT && $accType !== UserAccount::DRIVER_ACCOUNT) {
            throw new Exception('Invalid account type');
        }
    }

    public function __destruct() {
    }

    /**
     * Checks the currently set username and password against
     * the database.
     *
     * RETURNS: true if the credentials are valid (and thus the
     * user can log in), false otherwise.
     */
    public function AreCredentialsValid() {
        $isValid = false;
        $queryStr = "";

        // Query database against username/email/password/account type
        //

        if (empty($this->email)) {
            // check against username
            $queryStr = "SELECT passwd FROM {$this->AccountTypeString_()} WHERE username='{$this->username}';";
        }
        else if (empty($this->username)) {
            // check against email
            $queryStr = "SELECT passwd FROM {$this->AccountTypeString_()} WHERE email='{$this->email}';";
        }
        else {
            // Check against username AND email
            $queryStr = "SELECT passwd FROM {$this->AccountTypeString_()} WHERE username='{$this->username}' AND email='{$this->email}';";
        }


        // make the query
        $result = $this->db->sql->query($queryStr);

        // If there is exactly one result associated with this username--which should
        // always be the case if the user provides a registered username--check the
        // given password against the one in the database.
        if ($result && $result->num_rows == 1) {
            $passwdHash = $result->fetch_assoc()["passwd"];
            $isValid = password_verify($this->password, $passwdHash);
            $result->free();
        }

        return $isValid;
    }

    /**
     * Attempt to log in with the currently set username and password.
     * This also sets session variables for the user.
     *
     * RETURNS: The UserAccount of the user logged in, or
     * false if the login failed.
     */
    public function Login() {
        if ($this->AreCredentialsValid()) {
            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }

            $_SESSION["loggedin"] = true;
            $_SESSION["username"] = $this->username;
            $_SESSION["accountType"] = $this->accountType;

            if (!empty($this->username)) {
                return $this->db->LoadUserFromUsername($this->accountType, $this->username);
            }
            else if (!empty($this->email)) {
                return $this->db->LoadUserFromEmail($this->accountType, $this->email);
            }
        }

        return false;
    }



    /**
     * PRIVATE METHODS
     */

    // Returns the name of the table that the given account is
    // stored in.
    private function AccountTypeString_() {
        switch ($this->accountType) {
            case UserAccount::ADMIN_ACCOUNT:
                return "Admins";

            case UserAccount::SPONSOR_ACCOUNT:
                return "Sponsors";

            case UserAccount::DRIVER_ACCOUNT:
                return "Drivers";

            default: throw new Exception("Invalid account type");
        }

        return "??? This shouldn't be reached ???";
    }
}
?>
