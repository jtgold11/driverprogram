<?php
require_once 'Database.php';
require_once 'CatalogAPI.php';

class CatalogItem {
    private $id = -1;

    public $title;
    public $location;
    public $viewItemURL;
    public $imageURL;
    public $shippingCost;
    public $currentPrice;
    public $conditionDisplayName;
    public $buyItNowAvailable;
    public $startTime;
    public $endTime;
    public $categoryId;
    public $categoryName;

    //Functions

    //simple constrcut function
    public function __construct() {
    }

    public function destruct() {}

    //Getter/Setter Functions
    public function GetID() {
      return $this->id;
    }

    public function InitializeID($newId) {
      if ($this->id === -1) {
        $this->id = $newId;
      }
    }

    public function GetPrice() {
      return $this->currentPrice;
    }

    public function GetAvailable() {
      return $this->buyItNowAvailable;
    }
}

// A catalog category.
class Category {
  public $id;
  public $categoryParentId;
  public $categoryName;
  public $leafCategory;

  public function __construct($id, $pid, $name, $leaf) {
    $this->id = $id;
    $this->categoryParentId = $pid;
    $this->categoryName = $name;
    $this->leafCategory = $leaf;
  }

  public function GetID() {return $this->id;}
}

class Catalog {
  protected $db;
  private $id = -1;
  private $sponsorId = -1;

  const SELECT_BY_CATEGORY = "CATEGORY";
  const SELECT_BY_RULES = "RULES";
  const SELECT_BY_LIST = "LIST";

  const VALID_SELECTION_MODES = [
    Catalog::SELECT_BY_CATEGORY,
    Catalog::SELECT_BY_RULES,
    Catalog::SELECT_BY_LIST
  ];

  private $itemFilters = [];
  private $keywords = "";
  private $categories = [];
  private $items = [];
  private $selectionMode = "CATEGORY";

  public function __construct($id, $sponsorId) {
    $this->id = $id;
    $this->sponsorId = $sponsorId;
    $this->db = new Database();
  }

  public function GetID() {return $this->id;}
  public function GetSponsorID() {return $this->sponsorId;}
  public function GetItemFilters() {return $this->itemFilters;}
  public function GetKeywords() {return $this->keywords;}
  public function GetCategories() {return $this->categories;}
  public function GetItems() {return $this->items;}
  public function GetSelectionMode() {return $this->selectionMode;}

  public function SetSelectionMode($sm) {
    if (!in_array($sm, Catalog::VALID_SELECTION_MODES)) {
      return false;
    }

    $sm = $this->db->sql->real_escape_string($sm);
    $this->selectionMode = $sm;
    $this->db->sql->query("UPDATE Catalogs SET selectionMode='{$sm}' WHERE id={$this->GetID()};");

    return true;
  }

  public function AddItem($item) {
    if (!in_array($item, $this->items)) {
      array_push($this->items, $item);
    }
    $this->db->AddOrUpdateCatalogItem($item);
    $this->db->sql->query("INSERT INTO CatalogList (catalogId,itemId) VALUES ({$this->GetID()}, {$item->GetID()});");
  }

  public function RemoveItem($item) {
    $index = array_search($item, $this->items);
    if ($index !== false) {
      array_splice($this->items, $index, 1);
    }

    $this->db->sql->query("DELETE FROM CatalogList WHERE itemId={$item->GetID()};");
  }

  public function AddCategory($category) {
    if (!in_array($category, $this->categories)) {
      array_push($this->categories, $category);
    }

    $this->db->sql->query("INSERT INTO CatalogCategories (catalogId,categoryId) VALUES ({$this->GetID()}, {$category->GetID()});");
  }

  public function RemoveCategory($category) {
    $index = array_search($category, $this->categories);
    if ($index !== false) {
      array_splice($this->categories, $index, 1);
    }

    $this->db->sql->query("DELETE FROM CatalogCategories WHERE catalogId={$this->GetID()} AND categoryId={$category->GetID()};");
  }

  public function SetKeywords($kw) {
    $this->keywords = $kw;
    
    if ($this->db->DoesFieldExistInTable("catalogId", $this->GetID(), "CatalogItemKeywords")) {
      $this->db->sql->query("UPDATE CatalogItemKeywords SET keywords='{$kw}' WHERE catalogId={$this->GetID()};");
    }
    else {
      $this->db->sql->query("INSERT INTO CatalogItemKeywords (catalogId,keywords) VALUES ({$this->GetID()}, '{$kw}');");
    }
  }

  public function InitializeSelectionMode($sm) {
    $this->selectionMode = $sm;
  }

  public function InitializeItems($items) {
    $this->items = $items;
  }
  
  public function InitializeKeywords($keywords) {
    $this->keywords = $keywords;
  }

  public function InitializeCategories($cats) {
    $this->categories = $cats;
  }

  public function InitializeItemFilters($filters) {
    $this->itemFilters = $filters;
  }

  public function RegenerateCatalog() {
    // 1. Query eBay API
    // 2. Make CatalogItem objects from the result
    //    (see eBayAPI::GetCatalogItemsFromJSON)
    // 3. AddOrUpdate those items to the database
    //    (see Database::AddOrUpdateCatalogItem)
    // 4. Empty the sponsor's catalog
    // 5. Populate the sponsor's catalog with the above items
    // 6. ???
    // 7. Profit
    
    $this->db->sql->autocommit(false);

    switch ($this->selectionMode) {
      case Catalog::SELECT_BY_CATEGORY:
        $this->RegenCatalogByCategory_();
        break;

      case Catalog::SELECT_BY_RULES:
        $this->RegenCatalogByRules_();
        break;

      case Catalog::SELECT_BY_LIST:
        $this->RegenCatalogByList_();
        break;
    }

    $this->db->sql->commit();
    $this->db->sql->autocommit(true);
  }

  private function RegenCatalogByCategory_(){
    $this->EmptyCatalog_();

    foreach ($this->categories as $category) {
      $json = eBayAPI::FindItemsByCategories($category->GetID());
      $items = eBayAPI::GetCatalogItemsFromJSON($json);

      foreach ($items as $item) {
        $this->AddItem($item);
      }
    }
  }

  private function RegenCatalogByRules_() {
    $this->EmptyCatalog_();

    $json = eBayAPI::FindItemsAdvanced($this->keywords, $this->itemFilters);
    $items = eBayAPI::GetCatalogItemsFromJSON($json);

    foreach ($items as $item) {
      $this->AddItem($item);
    }
    
  }

  private function RegenCatalogByList_() {

  }

  private function EmptyCatalog_() {
    return $this->db->sql->query("DELETE FROM CatalogList WHERE catalogId={$this->GetID()};");
    $this->items = array();
  }

  public function Reset() {
    $this->EmptyCatalog_();
    $this->categories = [];
    $this->itemFilters = [];
    $this->keywords = "";
    $this->selectionMode = Catalog::SELECT_BY_CATEGORY;
    $this->db->sql->query("DELETE FROM CatalogCategories WHERE catalogId={$this->GetID()};");
    $this->db->sql->query("DELETE FROM CatalogItemFilters WHERE catalogId={$this->GetID()};");
    $this->db->sql->query("DELETE FROM CatalogItemKeywords WHERE catalogId={$this->GetID()};");
  }
}

?>
