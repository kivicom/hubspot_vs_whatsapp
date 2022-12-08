<?php


namespace Classes\ChatArchitect\WhatsApp;


class AppStorage
{
    public $appStorageUrl;
    public $appstorage_token;

    /*return base64 appID and appSecret AppStorage*/
    public function __construct()
    {
        $this->appStorageUrl = $_ENV['APP_STORAGE_URL'];
        $this->appstorage_token = base64_encode($_ENV['APP_STORAGE_TOKEN']);
    }

    /* cURL use WhatsApp appstorage*/
    public function cUrlWAAppStorage($appKey, $post_fields, $method)
    {
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => $this->appStorageUrl . "/" . $appKey,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => $method,
            CURLOPT_POSTFIELDS => $post_fields,
            CURLOPT_HTTPHEADER => array(
                "authorization: Basic " . $this->appstorage_token,
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

    public function saveAppStorageWA($appKey, $data)
    {
        $post_fields = '{
            "app": "' . $data['app'] . '",
            "secret": "' . $data['secret'] . '",
            "white_list": ["' . $data['email'] . '"]
        }';

        return $this->cUrlWAAppStorage($appKey, $post_fields, "PUT");
    }

    public function deleteAppStorageWA($appKey)
    {
        return $this->cUrlWAAppStorage($appKey, null, "DELETE");
    }

    public function getAppStorageWA($appKey)
    {
        $result = $this->cUrlWAAppStorage($appKey, null, "GET");
        return $result['appstorage'];
    }

}