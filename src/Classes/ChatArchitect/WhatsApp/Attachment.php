<?php


namespace Classes\ChatArchitect\WhatsApp;


class Attachment
{
    public $baseUrl;
    public $uploaddir;

    public function __construct()
    {
        $this->baseUrl = $_ENV['BASE_URL'];
        $this->uploaddir = $_ENV['UPLOAD_DIR'];
    }

    public function uploadFile($file)
    {
        $res = [];

        if ($file) {
            for ($i = 1; $i <= count($file); $i++) {
                $attachment = $file['attachment' . $i];
                $type = explode("/", $attachment['type']);
                $folderTypeName = $type[0];// Exemple 0 => image replace from image/jpeg

                if (!file_exists($this->uploaddir. "/" . $folderTypeName)) {
                    mkdir($this->uploaddir . $folderTypeName, 0750);
                }

                $uploadfile = $this->uploaddir . "/" . $folderTypeName . "/" . basename($attachment['name']);

                try {
                    $res = move_uploaded_file($attachment['tmp_name'], $uploadfile);
                } catch (\Exception $e) {
                    $res[$attachment['name']] = $e->getMessage();
                    file_put_contents("error_uploadFile.txt", print_r($res, true) . PHP_EOL, FILE_APPEND);
                }
            }
        }

        return $res;

    }

    public function getFile($folderTypeName, $fileName)
    {
        file_put_contents("getFile.txt", print_r([$folderTypeName, $fileName], true) . PHP_EOL, FILE_APPEND);
        return $this->baseUrl . $this->uploaddir . "/" . $folderTypeName . "/" . $fileName;
    }
}