<?php

namespace Classes\ChatArchitect\WhatsApp;

class WhatsApp
{
    public $url;
    public $channel;
    public $token;

    public function __construct()
    {
        $this->channel = $_ENV['CHANNEL'];
        $this->url = $_ENV['WHATSAPP_URL'];
    }

    public function setToken($data)
    {
        $app = $data['app'];
        $secret = $data['secret'];

        $this->token = base64_encode($app . ":" . $secret);
    }

    public function getToken()
    {
        return $this->token;
    }

    /* cURL use WhatsApp */
    public function cUrlWA($post_fields, $method)
    {
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => $this->url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => $method,
            CURLOPT_POSTFIELDS => $post_fields,
            CURLOPT_HTTPHEADER => array(
                "authorization: Basic " . $this->getToken(),
                "content-type: application/json"
            ),
        ));

        $response = json_decode(curl_exec($curl), true);

        $err = curl_error($curl);

        curl_close($curl);

        if ($err) {
            return "cURL Error #:" . $err;
        }

        return $response;
    }


    /*
     * Send a Text to WhatsApp
     * USE whatsappAccount()
    */
    public function sendText($phone, $message)
    {
        $post_fields = '{
            "channel": "' . $this->channel . '",
            "destination": "' . $phone . '",
            "payload": {
                "type": "text",
                "message": "' . trim($message) . '"
            }
        }';

        return $this->cUrlWA($post_fields, "POST");
    }

    public function sendImage($phone, $urlOfFile, $caption)
    {
        $post_fields = '{
            "channel": "' . $this->channel . '",
            "destination": "' . $phone . '",
            "payload": {
                "type": "image",
                "originalUrl": "' . $urlOfFile . '",
                "previewUrl": "' . $urlOfFile . '",
                "caption":"' . $caption . '"
            }
        }';

        return $this->cUrlWA($post_fields, "POST");
    }

    public function sendAudio($phone, $urlOfFile)
    {
        $post_fields = '{
            "channel": "' . $this->channel . '",
            "destination": "' . $phone . '",
            "payload": {
                "type": "audio",
                "url": "' . $urlOfFile . '"
            }
        }';

        return $this->cUrlWA($post_fields, "POST");
    }

    public function sendVideo($phone, $message, $urlOfFile)
    {
        $post_fields = '{
            "channel": "' . $this->channel . '",
            "destination": "' . $phone . '",
            "payload": {
                "type": "video",
                "url": "' . $urlOfFile . '",
                "caption":"' . $message . '"
            }
        }';

        return $this->cUrlWA($post_fields, "POST");
    }

    public function sendDocument($phone, $message, $urlOfFile, $fileName)
    {
        $post_fields = '{
            "channel": "' . $this->channel . '",
            "destination": "' . $phone . '",
            "payload": {
                "type": "file",
                "url": "' . $urlOfFile . '",
                "caption":"' . $message . '",
                "filename": "' . $fileName . '"
            }
        }';

        return $this->cUrlWA($post_fields, "POST");
    }

    public function save($data)
    {
        $this->setToken($data);

        try {
            $response = $this->setWebhook($data);
            $this->saveStorageWA($data);
        }catch (\Exception $e){
            file_put_contents("save.txt", date("Y-m-d H:i:s").": ".$e->getMessage() . PHP_EOL, FILE_APPEND);
            echo "Exception when installaton: ", $e->getMessage();
            return false;
        }

        return $response;
    }

    public function setWebhook($data)
    {
        $post_fields = '{
            "channel": "' . $this->channel . '",
            "webhook": "' . $data['webhook'] . '",
            "webhook_separate": "false"
        }';

        return $this->cUrlWA($post_fields, "POST");
    }

    public function saveStorageWA($data)
    {
        $post_fields = '{
            "channel": "' . $this->channel . '",
            "storage": {
                "email_forward": "' . $data['email'] . '"
            }

        }';

        return $this->cUrlWA($post_fields, "POST");
    }

    /*get Storage from whatsapp*/
    public function getStorage()
    {
        $post_fields = '{
            "channel": "' . $this->channel . '"
        }';

        $result = self::cUrlWA($post_fields, "POST");

        return $result;
    }

    public function deleteStorage($data)
    {
        $this->setToken($data);
        $post_fields = '{
            "channel": "' . $this->channel . '",
            "storage": {
                "email_forward": ""
            }
        }';

        return self::cUrlWA($post_fields, "POST");
    }

    /*get billing from whatsapp*/
    public function getBilling()
    {
        $post_fields = '{
            "channel": "' . $this->channel . '"
        }';
        $result = $this->cUrlWA($post_fields, "POST");

        return $result['billing'];
    }

}