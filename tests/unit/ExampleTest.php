<?php

use app\models\Request;

class ServicesTest extends \Codeception\Test\Unit
{
    public $config;

    public array $ips = [];

    public function _before()
    {
        $config = [];
        $config['ip'] = 'Wrong ip doesn not exists';
        $this->config = $config;
        $this->ips = [
            "2001:bc8:1640:51b:dc00:ff:fe11:6bc7",
            "2a00:1838:37:223::5ea9",
        ];
        parent::_before();
    }

    public function test404()
    {
        $config = [];
        $config['ip'] = '111';

        $client = \Yii::createObject([
            'class' => Request::class,
            'params' => $config,
        ]);
        $response = $client->getResponse('/wrong_address');

        $this->assertTrue($response['code'] == 404);
        $this->assertTrue($response['result'] == Request::STATUS_FAIL);
    }

    public function testIpValidation()
    {
        $client = \Yii::createObject([
            'class' => Request::class,
            'params' => $this->config,
        ]);
        $response = $client->getResponse(Yii::$app->params['go-country']);

        $this->assertTrue($response['code'] == 400);
        $this->assertTrue($response['result'] == Request::STATUS_FAIL);
    }

    public function testDataFromServer()
    {
        foreach ($this->ips as $ip) {
            $client = \Yii::createObject([
                'class' => Request::class,
                'params' => [
                    "ip" => $ip,
                ],
            ]);
            $response = $client->getResponse(Yii::$app->params['go-country']);

            $this->assertTrue($response['result'] == Request::STATUS_SUCCESS);
        }
    }
}