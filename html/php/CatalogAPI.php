<?php

require_once 'Catalog.php';

class eBayAPI {

    // sandbox app id
    //const APPID = "WilliamW-driverpr-SBX-6dfe99ed4-44491ac6";

    // production app id
    const APPID = "WilliamW-driverpr-PRD-2dfe99f22-45ae2e56";

    // Production endpoints
    //
    const FINDING_ENDPOINT = "https://svcs.ebay.com/services/search/FindingService/v1";
    const SHOPPING_ENDPOINT = "http://open.api.ebay.com/shopping";
    const TRADING_ENDPOINT = "https://api.ebay.com/ws/api.dll";

    // Sandbox endpoints
    //

    //const FINDING_ENDPOINT = "https://svcs.sandbox.ebay.com/services/search/FindingService/v1";
    //const SHOPPING_ENDPOINT = "http://open.api.sandbox.ebay.com/shopping";

    public static function FindItemsByKeywords($keywords) {
        $apicall = self::GetFindingAPICallTemplate("findItemsByKeywords");
        $apicall .= "&keywords=".urlencode($keywords);
        $apicall .= "&outputSelector(0)=SellerInfo";
        $apicall .= "&outputSelector(1)=GalleryInfo";
        $apicall .= "&itemFilter(0).name=HideDuplicateItems";
        $apicall .= "&itemFilter(0).value=1";
        return file_get_contents($apicall);
    }

    public static function FindItemsByCategories($categoryID1, $categoryID2 = -1, $categoryID3 = -1) {
        $apicall = self::GetFindingAPICallTemplate("findItemsByCategory");

        if ($categoryID2 !== -1) {
            $apicall .= "&categoryId(0)={$categoryID1}";
            $apicall .= "&categoryId(1)={$categoryID2}";

            if ($categoryID3 !== -1) {
                $apicall .= "&categoryId(2)={$categoryID3}";
            }
        }
        else {
            $apicall .= "&categoryId={$categoryID1}";
        }

        $apicall .= "&outputSelector(0)=SellerInfo";
        $apicall .= "&outputSelector(1)=GalleryInfo";

        $apicall .= "&itemFilter(0).name=HideDuplicateItems";
        $apicall .= "&itemFilter(0).value=1";

        return file_get_contents($apicall);
    }

    public static function FindItemsAdvanced($keywords, $itemFilters) {
        $apicall = self::GetFindingAPICallTemplate("findItemsAdvanced");
        $apicall .= "&outputSelector(0)=SellerInfo";
        $apicall .= "&outputSelector(1)=GalleryInfo";

        $apicall .= "&keywords=".urlencode($keywords);
        
        for ($i = 0; $i < count($itemFilters); ++$i) {
            $apicall .= "&itemFilter({$i}).name=".$itemFilters[$i][0];
            $apicall .= "&itemFilter({$i}).value=".$itemFilters[$i][1];
        }

        return file_get_contents($apicall);
    }

    public static function FindItemsByProduct($productId) {
        $apicall = self::GetFindingAPICallTemplate("findItemsByProduct");
        $apicall .= "&productId.@type=ReferenceID";
        $apicall .= "&productId={$productId}";

        return file_get_contents($apicall);
    }

    public static function GetCategories($categoryId = -1) {
        $apicall = self::GetShoppingAPICallTemplate("GetCategoryInfo");
        $apicall .= "&CategoryID={$categoryId}";
        $apicall .= "&IncludeSelector=ChildCategories";
        return file_get_contents($apicall);
    }

    public static function GetFindingAPICallTemplate($operation) {
        $apicall = self::FINDING_ENDPOINT;
        $apicall .= "?OPERATION-NAME={$operation}";
        $apicall .= "&GLOBAL-ID=EBAY-US";
        $apicall .= "&RESPONSE-DATA-FORMAT=JSON";
        $apicall .= "&SECURITY-APPNAME=".self::APPID;

        return $apicall;
    }

    public static function GetShoppingAPICallTemplate($operation) {
        $apicall = self::SHOPPING_ENDPOINT;
        $apicall .= "?appid=".self::APPID;
        $apicall .= "&callname={$operation}";
        $apicall .= "&responseencoding=JSON";
        $apicall .= "&siteid=0";
        $apicall .= "&version=1099";

        return $apicall;
    }

    /**
     * Return a list of the eBay items listed in the given JSON string.
     * The string provided should be a result of a call made with 
     * eBayAPI. If it's not a correctly formatted string, all bets are
     * off.
     * 
     *      $json, string : The JSON to load catalog items from.
     * 
     * RETURNS: A list of CatalogItem objects.
     */
    public static function GetCatalogItemsFromJSON($json) {
        $items = [];

        // get the search results from the result of the api call
        $decoded = json_decode($json, true);
        $keys = array_keys($decoded);
        $searchResults = $decoded[$keys[0]][0]["searchResult"][0]["item"];

        for ($i = 0; $i < count($searchResults); $i++) {
            // Create a catalog item from this entry in the array
            // created from the JSON.
            $result = $searchResults[$i];
            
            $citem = new CatalogItem();
            $citem->InitializeID($result["itemId"][0]);

            $citem->title = $result["title"][0];
            $citem->location = $result["location"][0];
            $citem->viewItemURL = $result["viewItemURL"][0];

            if (isset($result["galleryURL"])) {
                $citem->imageURL = $result["galleryURL"][0];
            }
            else {
                $citem->imageURL = "";
            }

            if (isset($result["shippingInfo"][0]["shippingServiceCost"])) {
                $citem->shippingCost = $result["shippingInfo"][0]["shippingServiceCost"][0]["__value__"];
            }

            $citem->currentPrice = $result["sellingStatus"][0]["currentPrice"][0]["__value__"];

            if (isset($result["condition"]) && isset($result["condition"][0]["conditionDisplayName"])) {
                $citem->conditionDisplayName = $result["condition"][0]["conditionDisplayName"][0];
            }
            else {
                $citem->conditionDisplayName = "Unknown";
            }

            $citem->buyItNowAvailable = $result["listingInfo"][0]["buyItNowAvailable"][0] === "false" ? false : true;
            $citem->startTime = $result["listingInfo"][0]["startTime"][0];
            $citem->endTime = $result["listingInfo"][0]["endTime"][0];
            $citem->categoryId = $result["primaryCategory"][0]["categoryId"][0];
            $citem->categoryName = $result["primaryCategory"][0]["categoryName"][0];

            array_push($items, $citem);
        }

        return $items;
    }

    public static function ConvertTimeStringToMySQL($datetime) {
        $timeParts = explode("T", $datetime);
        $timeParts[1] = substr($timeParts[1], 0, strpos($timeParts[1], "."));

        return $timeParts[0]." ".$timeParts[1];
    }

    
}

?>