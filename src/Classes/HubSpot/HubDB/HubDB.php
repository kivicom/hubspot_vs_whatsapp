<?php


namespace Classes\HubSpot\HubDB;


use Helpers\OAuth2Helper;
use HubSpot\Client\Cms\Hubdb\ApiException;
use HubSpot\Client\Cms\Hubdb\Model\ColumnRequest;
use HubSpot\Client\Cms\Hubdb\Model\HubDbTableRowV3Request;
use HubSpot\Client\Cms\Hubdb\Model\HubDbTableV3Request;
use HubSpot\Factory;

class HubDB
{
    const TABLE_NAME = 'chatarchitect';
    const TABLE_LABLE_NAME = 'ChatArchitect';
    public $client;

    public function __construct()
    {
        $this->client = Factory::createWithAccessToken(OAuth2Helper::refreshAndGetAccessToken());
    }

    //Get all published tables
    public function getAllTables()
    {
        try {
            $apiResponse = $this->client->cms()->hubdb()->tablesApi()->getAllTables();
            return $apiResponse->getResults();
        } catch (ApiException $e) {
            echo "Exception when calling tables_api->get_all_tables: ", $e->getMessage();
        }
    }

    public function issetTable()
    {
        if($this->createTable() === 409){
            $this->createTable();
        }

        return true;
    }

    public function createTable()
    {
        $columnRequest1 = new ColumnRequest([
            'id' => 1,
            'name' => 'billing_phone',
            'label' => 'Billing Phone',
            'type' => 'TEXT'
        ]);
        $columnRequest2 = new ColumnRequest([
            'id' => 2,
            'name' => 'app',
            'label' => 'App ID',
            'type' => 'TEXT'
        ]);
        $columnRequest3 = new ColumnRequest([
            'id' => 3,
            'name' => 'secret',
            'label' => 'App Secret',
            'type' => 'TEXT'
        ]);
        $columnRequest4 = new ColumnRequest([
            'id' => 4,
            'name' => 'white_list',
            'label' => 'White list',
            'type' => 'TEXT'
        ]);

        $hubDbTableV3Request = new HubDbTableV3Request([
            'name' => self::TABLE_NAME,
            'label' => self::TABLE_LABLE_NAME,
            'columns' => [$columnRequest1, $columnRequest2, $columnRequest3, $columnRequest4],
        ]);
        try {
            $apiResponse = $this->client->cms()->hubdb()->tablesApi()->createTable($hubDbTableV3Request);
            return $apiResponse;
        } catch (ApiException $e) {
            //echo "Exception when calling tables_api->create_table: ", $e->getMessage();
            return $e->getCode();
        }
    }

    public function createTableRow($data)
    {
        $values = [
            'billing_phone' => $data['billing_phone'],
            'app' => $data['app'],
            'secret' => $data['secret'],
            'white_list' => $data['email']
        ];
        $hubDbTableRowV3Request = new HubDbTableRowV3Request([
            'values' => $values,
        ]);

        try {
            $apiResponse = $this->client->cms()->hubdb()->rowsApi()->createTableRow(self::TABLE_NAME, $hubDbTableRowV3Request);

            $this->publishDraftTable();
            return $apiResponse->getId();
        } catch (ApiException $e) {
            echo "Exception when calling rows_api->create_table_row: ", $e->getMessage();
        }
    }

    public function publishDraftTable()
    {
        try {
            $apiResponse = $this->client->cms()->hubdb()->tablesApi()->publishDraftTable(self::TABLE_NAME);
            return $apiResponse;
        } catch (ApiException $e) {
            echo "Exception when calling tables_api->publish_draft_table: ", $e->getMessage();
        }
    }

    public function getRows()
    {
        try {
            $apiResponse = $this->client->cms()->hubdb()->rowsApi()->getTableRows(self::TABLE_NAME);
            return $apiResponse;
        } catch (ApiException $e) {
            echo "Exception when calling rows_api->get_table_rows: ", $e->getMessage();
            return null;
        }
    }

    public function getRowByID($id)
    {
        try {
            $apiResponse = $this->client->cms()->hubdb()->rowsApi()->getTableRow(self::TABLE_NAME, $id);
            return $apiResponse;
        } catch (ApiException $e) {
            echo "Exception when calling rows_api->get_table_row: ", $e->getMessage();
        }
    }

    public function getRowByPhone($phone)
    {
        $row = [];
        $rows = $this->getRows()->getResults();

        foreach ($rows as $k => $item) {
            if ($item['values']['billing_phone'][0] === $phone) {
                $row = $item['values'];
                continue;
            }
        }

        return $row;
    }

    public function deleteRowByID($rowId)
    {
        try {
            $this->client->cms()->hubdb()->rowsApi()->purgeDraftTableRow(self::TABLE_NAME, $rowId);
            $this->publishDraftTable();
            return true;
        } catch (ApiException $e) {
            echo "Exception when calling rows_api->purge_draft_table_row: ", $e->getMessage();
        }
    }

    public function getPath()
    {
        $rows = $this->getRows();
        $pathArray = [];

        foreach ($rows as $k => $item) {
            $pathArray[] = $item->getValues()['billing_phone'][0];
        }

        return $pathArray;
    }

    public function isUniqPath($path)
    {
        if (!in_array($path, $this->getPath())) {
            return true;
        }
        return false;
    }
}