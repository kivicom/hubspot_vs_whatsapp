<?php

$whatsApp = new \Classes\ChatArchitect\WhatsApp\WhatsApp();
$appstorage = new \Classes\ChatArchitect\WhatsApp\AppStorage();
$conversation = new \Classes\HubSpot\Conversation\Conversation();
$hubdb = new Classes\HubSpot\HubDB\HubDB();

$channelAccount = $conversation->getChannelAccounts();
$access_token = \Helpers\OAuth2Helper::refreshAndGetAccessToken();

//echo $access_token;
unset($_SESSION['billingPhone']);
unset($_SESSION['unauthorized']);

if ($_SERVER['REQUEST_METHOD'] === "POST") {

    $install = $whatsApp->save($_POST);


    if($install['status'] === "Unauthorized"){
        $_SESSION['unauthorized'] = true;
    }

    if ($install['billing']) {

        //Create table if not exists with columns
        $hubdb->issetTable();

        //get billing phone
        $appKey = $install['billing']['phone'];

        //for used Whatsapp AppStorage
        $res = $appstorage->saveAppStorageWA($appKey, $_POST);

        if($hubdb->isUniqPath($appKey)){

            $_POST['billing_phone'] = $appKey;

            //for used HubDB
            $res = $hubdb->createTableRow($_POST);

        }else{
            $_SESSION['billingPhone'] = true;
        }

        if($res){
            header('Location: /billing/index');
            exit();
        }


    }
}


include __DIR__.'/../../views/installation/index.php';