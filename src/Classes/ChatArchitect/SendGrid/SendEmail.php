<?php

namespace Classes\ChatArchitect\SendGrid;

use Classes\ChatArchitect\Helpers\Helper;
use Classes\ChatArchitect\WhatsApp\AppStorage;
use Classes\ChatArchitect\WhatsApp\WhatsApp;

use SendGrid\Mail\Mail;

class SendEmail
{
    public $email;
    public $whatsApp;
    public $apikey;
    public $helper;
    public $appstorage;

    public function __construct()
    {
        $this->email = new Mail();
        $this->helper = new Helper();
        $this->whatsApp = new WhatsApp();
        $this->appstorage = new AppStorage();
        $this->apikey = $_ENV['SENDGRID_API_KEY'];
    }

    public function sendEmail($data)
    {
        /*get email from storage whatsapp*/
        $storageWA = $data['storage'];
        $billingWA = $data['billing']['phone'];
        $app = $this->appstorage->getAppStorageWA($billingWA);

        $pp_type = $data['payload']['type'];

        $this->email->setFrom($data['payload']['sender']['phone']."@em7741.chatarchitect.com", "ChatArchitect");
        $this->email->setSubject("RE: whatsapp ". $billingWA);
        $this->email->addTo($storageWA['email_forward'], $data['payload']['sender']['name']);

        if (!$this->helper->emailCompare($storageWA['email_forward'], $app['white_list'][0])) {
            return false;
        }

        if($pp_type === "image"){
            $urlFile = base64_encode(file_get_contents($data['payload']['payload']['url']));
            $contentType = $data['payload']['payload']['contentType'];
            $this->email->addAttachment($urlFile, $contentType, 'whatsapp',"attachment");
            $this->email->addContent("text/html", $data['payload']['payload']['caption'] ?? " ");

        }
        else if($pp_type === "audio"){

            $urlFile = base64_encode(file_get_contents($data['payload']['payload']['url']));
            $contentType = $data['payload']['payload']['contentType'];
            $fileName = $data['payload']['payload']['filename'];

            if(($contentType === "audio/amr") || ($contentType === "audio/ogg")){
                $fileName = $data['payload']['payload']['name'];
            }

            $this->email->addAttachment($urlFile, $contentType, $fileName,"attachment");
            $this->email->addContent("text/html", " ");

        }
        else if($pp_type === "video"){

            $urlFile = base64_encode(file_get_contents($data['payload']['payload']['url']));
            $contentType = $data['payload']['payload']['contentType'];
            $fileName = $data['payload']['payload']['filename'];

            $this->email->addAttachment($urlFile, $contentType, $fileName,"attachment");
            $this->email->addContent("text/html", $data['payload']['payload']['caption'] ?? " ");

        }
        else if($pp_type === "file"){

            $urlFile = base64_encode(file_get_contents($data['payload']['payload']['url']));
            $contentType = $data['payload']['payload']['contentType'];
            $fileName = $data['payload']['payload']['name'];

            $this->email->addAttachment($urlFile, $contentType, $fileName,"attachment");
            $this->email->addContent("text/plain", $data['payload']['payload']['caption']);

        }
        else if($pp_type === "location"){

            $longitude = $data['payload']['payload']['longitude'];
            $latitude = $data['payload']['payload']['latitude'];
            $geolocation = htmlspecialchars('https://www.google.com/maps/search/?api=1&zoom=14&query=' . $latitude . ',' . $longitude);

            $this->email->addContent("text/html", $geolocation);

        }
        else if($pp_type === "contact"){

            $contatName = $data['payload']['payload']['contacts'][0]['name']['formatted_name'];
            $contatPhone = $data['payload']['payload']['contacts'][0]['phones'][0]['phone'];
            $type = $data['payload']['payload']['contacts'][0]['phones'][0]['type'];
            $content = "<h3>Name: ".$contatName."</h3><h4>Phone number: ".$contatPhone."</h4>".$type;

            $this->email->addContent("text/html", $content);

        }
        else if($pp_type === "text"){
            $this->email->addContent("text/html", $data['payload']['payload']['text']);
        }
        else{
            exit();
        }

        $sendgrid = new \SendGrid($this->apikey);
        try {
            $response = $sendgrid->send($this->email);
            return $response;
        } catch (\Exception $e) {
            file_put_contents("Exception.txt", print_r($e, true));
            return 'Caught exception: '. $e->getMessage() ."\n";
        }
    }
}