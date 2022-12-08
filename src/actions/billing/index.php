<?php
$hubdb = new \Classes\HubSpot\HubDB\HubDB();
$whatsapp = new \Classes\ChatArchitect\WhatsApp\WhatsApp();
$appstorage = new \Classes\ChatArchitect\WhatsApp\AppStorage();

$requestBody = file_get_contents('php://input');
$event = json_decode($requestBody, true);

if(isset($event['rowId']) && ($event['rowId'] !== "")){

    $hubdb->deleteRowByID($event['rowId']);

    $data = $appstorage->getAppStorageWA($event['billingPhone']);
    $whatsapp->deleteStorage($data);

    $appstorage->deleteAppStorageWA($event['billingPhone']);
}

$rows = $hubdb->getRows();

if($rows){
    $rows = $rows->getResults();
}else{
    $rows = [];
}


include __DIR__.'/../../views/billing/index.php';