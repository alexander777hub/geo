<?php


namespace app\models;

use GuzzleHttp\Client;
use Yii;
use yii\helpers\Json;

/**
 * Class Request
 *
 * @package app\models
 */
class Request extends \yii\base\BaseObject
{
    public const RESPONSE_CODE_SUCCESS = 200;

    public const STATUS_FAIL = 'fail';

    public const STATUS_SUCCESS = 'ok';

    public array $params = [];

    public string $base_url;

    public Client $client;

    /**
     * @param array $config
     */
    public function __construct(array $config = [])
    {
        $this->base_url = Yii::$app->params['go-base-url'] . '/api/';
        parent::__construct($config);
    }

    public function getResponse(string $go_controller_url)
    {
        $this->client = new \GuzzleHttp\Client([
            'base_uri' => $this->base_url,
        ]);
        try {
            $response = $this->client->request('GET', $go_controller_url, [
                'headers' => ['worker-access-token' => Yii::$app->params['worker-access-token']],
                'query' => $this->params,
            ]);

            $rows = Json::decode((string)$response->getBody());

            return $rows;
        } catch (\Exception $e) {
            if ($e->getCode() != self::RESPONSE_CODE_SUCCESS) {
                return [
                    "result" => self::STATUS_FAIL,
                    "code" => $e->getCode(),
                ];
            }
        }
    }
}
