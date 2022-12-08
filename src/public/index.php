<?php

use Helpers\OAuth2Helper;
use Classes\ChatArchitect\WhatsApp\WhatsApp;
use Classes\ChatArchitect\SendGrid\SendEmail;
use Classes\ChatArchitect\Helpers\Helper;

include_once '../../vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable('../../');
$dotenv->load();


$requestBody = file_get_contents('php://input');
$events = json_decode($requestBody, true);

$whatsApp = new WhatsApp();
$helper = new Helper();

if ($_SERVER['REQUEST_METHOD'] === "POST") {

    /* Get POST from sendgrid hook/gmail */
    if (!empty($_POST)) {

        if(isset($_POST['headers'])){

            $helper->prepareData($_POST);
            exit();

        }

    }

}


/* Get events from whatsapp */
if (isset($events['type'])) {

    if ($events['type'] === "message") {

        $sendEmail = new SendEmail();
        $sendEmail->sendEmail($events);
    }
    exit();
}

session_start();
$uri = parse_url($_SERVER['REQUEST_URI'])['path'];

try {

    $publicRoutes = require '../routes/public.php';
    $protectedRoutes = require '../routes/protected.php';

    if (in_array($uri, $protectedRoutes)) {
        if (!OAuth2Helper::isAuthenticated()) {
            header('Location: /oauth/login');
        }
    }

    if ('/' === $uri) {
        header('Location: /billing/index');

        exit;
    }

    if (!in_array($uri, array_merge($publicRoutes, $protectedRoutes))) {
        http_response_code(404);

        exit;
    }

    $path = __DIR__ . '/../actions' . $uri . '.php';

    require $path;
} catch (Throwable $t) {
    $message = $t->getMessage();
    include __DIR__ . '/../views/error.php';

    exit;
}
