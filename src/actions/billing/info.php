<?php

$whatsapp = new \Classes\ChatArchitect\WhatsApp\WhatsApp();
$appstorage = new \Classes\ChatArchitect\WhatsApp\AppStorage();
$hubdb = new \Classes\HubSpot\HubDB\HubDB();
$helper = new \Classes\ChatArchitect\Helpers\Helper();

/*
$access_token = \Helpers\OAuth2Helper::refreshAndGetAccessToken();
echo $access_token;
*/

if(isset($_GET['phone'])){
    $appKey = $_GET['phone'];

    //for used Whatsapp AppStorage
    //$db = $appstorage->getAppStorageWA($appKey);


    //for used HubDB
    $data = $hubdb->getRowByPhone($appKey);
    $db = $helper->isArray($data);


    $whatsapp->setToken($db);
    $billingInfo = $whatsapp->getBilling();
}

include __DIR__.'/../../views/billing/info.php';