<?php


namespace Classes\ChatArchitect\Helpers;


use Classes\ChatArchitect\WhatsApp\AppStorage;
use Classes\ChatArchitect\WhatsApp\Attachment;
use Classes\ChatArchitect\WhatsApp\WhatsApp;

class Helper
{
    public $whatsapp;
    public $appstorage;
    public $attachments;

    public function __construct()
    {
        $this->attachments = new Attachment();
        $this->appstorage = new AppStorage();
        $this->whatsapp = new WhatsApp();
    }

    public function isArray($data)
    {
        if(is_array($data['app'])){
            foreach($data['app'] as $item){
                if(!is_array($item)){
                    $data['app'] = $item;
                }
            }
        }

        if(is_array($data['secret'])){
            foreach($data['secret'] as $item){
                if(!is_array($item)){
                    $data['secret'] = $item;
                }
            }
        }

        if(is_array($data['billing_phone'])){
            foreach($data['billing_phone'] as $item){
                if(!is_array($item)){
                    $data['billing_phone'] = $item;
                }
            }
        }

        return $data;
    }

    public function prepareData($events)
    {
        if (!empty($_FILES)) {
            $this->attachments->uploadFile($_FILES);
        }

        //Get envelope array from $requestMail
        $envelope = json_decode($events['envelope'], true);
        $partOfEmail = stristr($envelope['to'][0], '@', true);;

        $appKey = $this->phoneFromSubject($events['subject']);


        if (!$appKey) {
            return false;
        }
        $db = $this->appstorage->getAppStorageWA($appKey);

        $this->whatsapp->setToken($db);

        if (!$this->isDigit($partOfEmail)) {
            return false;
        }

        if (!$this->checkSubject($events['subject'])) {
            return false;
        }

        $white_list = $db['white_list'][0];

        if (!$this->emailCompare($events['from'], $white_list)) {
            return false;
        }

        //$cyrillicPattern = "/\n(\w{1,}),\s(\d{1,2})\s(\w+).\s(\d+)\s(\w{1,}).\s()(\w{1,})\s(\d{1,2}):(\d{1,2})/u";
        //$latPattern = "/\nOn(.*?)wrote:(.*?)$/si";
        $phone = $partOfEmail;
        $message = trim(preg_replace('/\nOn(.*?)wrote:(.*?)$/si', "", $events['text']));

        return $this->manager($phone, $message, $events['attachment-info']);
    }

    public function manager($phone, $message, $attachments)
    {
        if ($attachments) {
            $this->whatsapp->sendText($phone, $message);
            $files = json_decode($attachments, true);

            for ($i = 1; $i <= count($files); $i++) {
                $message = "";
                $attachment = $files['attachment' . $i];

                $fileName = $attachment['filename'];
                $typeOfFIle = $attachment['type'];
                $folderTypeName = explode("/", $attachment['type']);// Exemple 0 => image replace from image/jpeg
                $urlOfFile = $this->attachments->getFile($folderTypeName[0], $fileName);

                $this->sendFromManager($phone, $message, $typeOfFIle, $urlOfFile, $fileName);
                //sleep(2);
            }
            return true;
        } else {
            return $this->whatsapp->sendText($phone, $message);
        }
    }

    public function sendFromManager($phone, $message, $typeOfFIle, $urlOfFile, $fileName)
    {
        try {
            switch ($typeOfFIle) {
                case "image/jpeg":
                case "image/png":
                    return $this->whatsapp->sendImage($phone, $urlOfFile, $message);
                case "audio/mpeg":
                case "audio/vnd.dlna.adts":
                case "video/ogg":
                    return $this->whatsapp->sendAudio($phone, $urlOfFile);
                case "video/mp4":
                    return $this->whatsapp->sendVideo($phone, $message, $urlOfFile);
                case "text/plain":
                case "application/vnd.openxmlformats-officedocument.wordprocessingml.document":
                case "application/pdf":
                case "application/octet-stream":
                    return $this->whatsapp->sendDocument($phone, $message, $urlOfFile, $fileName);
            }
        } catch (\Exception $e) {
            file_put_contents("log_error/error_manager.txt", $e->getMessage() . PHP_EOL, FILE_APPEND);
        }
    }

    public function emailCompare($from, $email_forward)
    {
        $patternEmail = "/([a-zA-Z0-9._-]+@[a-zA-Z0-9._-]+\.[a-zA-Z0-9_-]+)/ism";
        preg_match($patternEmail, $from, $matches);
        $email_from = $matches[0];

        if (strcmp($email_from, $email_forward) !== 0) {
            file_put_contents("prepareData.txt", print_r("emailCompare something wrong - " . $email_forward . " = " . $email_from, true) . PHP_EOL, FILE_APPEND);
            return false;
        }

        return true;
    }

    public function checkSubject($subject)
    {
        $patternRESubject = "/^(RE:\s)?whatsapp\s\d{7,15}$/im";
        //$patternSubject = "/^whatsapp\s\d{7,15}$/im";
        if (!preg_match($patternRESubject, $subject)) {
            file_put_contents("prepareData.txt", print_r("checkSubject something wrong - " . $subject, true) . PHP_EOL, FILE_APPEND);
            return false;
        }

        return true;

    }

    public function phoneFromSubject($subject)
    {
        $patternRESubject = "/\d{7,15}$/im";
        preg_match($patternRESubject, $subject, $matches);
        if (!$matches[0]) {
            file_put_contents("prepareData.txt", print_r("phoneFromSubject something wrong - " . $subject, true) . PHP_EOL, FILE_APPEND);
            return false;
        }
        return $matches[0];

    }

    public function isDigit($num)
    {
        $pattern = "/^\d{7,15}$/";
        if (!preg_match($pattern, $num)) {
            file_put_contents("prepareData.txt", print_r("isDigit something wrong - " . $num, true) . PHP_EOL, FILE_APPEND);
            return false;
        }

        return true;
    }
}