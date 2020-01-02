<?php
require_once 'Database.php';
require_once 'Account.php';

class Notification {
    protected $db;
    private $id = -1;
    private $alertText = "Default Alert Text.";
    private $targetAccType;
    private $dateSent;
    private $driverId=-1;
    private $sponsorId=-1;
    private $adminId=-1;
    private $hasBeenSeen = false;
    private $accTypeToString = [
        UserAccount::DRIVER_ACCOUNT => "DRIVER",
        UserAccount::SPONSOR_ACCOUNT => "SPONSOR",
        UserAccount::ADMIN_ACCOUNT => "ADMIN",
    ];

    public function __construct($accType, $targetId) {
        $this->db = new Database();
        $this->targetAccType = $accType;
        
        switch ($this->targetAccType) {
            case UserAccount::DRIVER_ACCOUNT:
                $this->driverId = $targetId;
                break;

            case UserAccount::ADMIN_ACCOUNT:
                $this->adminId = $targetId;
                break;

            case UserAccount::SPONSOR_ACCOUNT:
                $this->sponsorId = $targetId;
                break;
        }
    }

    // Initialize fields that were retrieved from the database when
    // loading an existing notification.
    public function InitializeID($id) {if ($this->id === -1) $this->id = $id;}
    public function InitializeTimestamp($date) {$this->dateSent = $date;}
    public function InitializeHasBeenSeen($b) {$this->hasBeenSeen = $b;}

    // Getters.
    //
    public function GetID() {return $this->id;}
    public function GetTargetID() {
        switch ($this->targetAccType) {
            case UserAccount::DRIVER_ACCOUNT: return $this->driverId;
            case UserAccount::SPONSOR_ACCOUNT: return $this->sponsorId;
            case UserAccount::ADMIN_ACCOUNT: return $this->adminId;
        }

        throw new Exception("Invalid account type");
    }
    public function GetTargetAccountType() {return $this->targetAccType;}
    public function GetText() {return $this->alertText;}
    public function GetTimestamp() {
        return DateTime::createFromFormat("Y-m-d H:i:s", $this->dateSent);
    }
    public function HasBeenSeen() {return $this->hasBeenSeen;}

    // Setters.
    //
    public function SetText($alert) {$this->alertText = $alert;}

    public function SetHasBeenSeen($boolean) {
        $this->hasBeenSeen = $boolean;

        // update status if this alert exists in the database
        if ($this->db->DoesFieldExistInTable("id", $this->GetID(), "AutomaticAlerts")) {
            $seen = $this->hasBeenSeen == true ? 1 : 0;
            $this->db->sql->query("UPDATE AutomaticAlerts SET hasBeenSeen={$seen} WHERE id={$this->GetID()};");
        }
    }

    public function Post() {
        $text = $this->db->sql->real_escape_string($this->alertText);
        $accType = $this->db->sql->real_escape_string($accTypeToString[$this->targetAccType]);

        if ($this->sponsorId === -1) $sid = NULL;
        if ($this->adminId === -1) $aid = NULL;
        if ($this->driverId === -1) $did = NULL;

        return $this->db->sql->query("INSERT INTO AutomaticAlerts (alertText,targetAccType,driverId,sponsorId,adminId) VALUES ('{$text}','{$accType}',{$did},{$sid},{$aid});");
    }
}

?>