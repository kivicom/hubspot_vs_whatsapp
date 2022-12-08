<?php


namespace Classes\HubSpot\Conversation;


use Helpers\OAuth2Helper;

class Conversation
{
    function getAccessToken(){
        return OAuth2Helper::refreshAndGetAccessToken();
    }

    /* cURL use hubspot*/
    function cUrlHS($url, $post_fields, $method)
    {
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => $method,
            CURLOPT_POSTFIELDS => $post_fields,
            CURLOPT_HTTPHEADER => array(
                "accept: application/json",
                "authorization: Bearer " . $this->getAccessToken(),
                "content-type: application/json"
            ),
        ));

        $response = json_decode(curl_exec($curl), true);
        $err = curl_error($curl);

        curl_close($curl);

        if ($err) {
            return "cURL Error #:" . $err;
        } else {
            return $response;
        }

    }

    /*
     * Get channel accounts
     * Use HubSpot cURL()
    */
    public function getChannelAccounts()
    {
        $url = "https://api.hubapi.com/conversations/v3/conversations/channel-accounts";

        $response = $this->cUrlHS($url, null, "GET");
        $channelAccount = [];

        foreach ($response['results'] as $k => $result) {
            if ($result['deliveryIdentifier']['type'] === "HS_EMAIL_ADDRESS") {
                $channelAccount = $result;
                //file_put_contents("channel_accounts.txt", print_r($result, true) . PHP_EOL, FILE_APPEND);
            }
            continue;
        }

        return $channelAccount;
    }
}