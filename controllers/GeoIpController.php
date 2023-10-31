<?php

namespace app\controllers;

use app\models\Country;
use app\models\Memcache;
use app\models\Network;
use app\models\Request;
use Symfony\Component\CssSelector\Exception\InternalErrorException;
use yii\web\BadRequestHttpException;
use yii\web\Controller;

/**
 * Class GeoIpController
 *
 * @package app\controllers
 */
class GeoIpController extends Controller
{
    public function beforeAction($action)
    {
        $this->enableCsrfValidation = false;
        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        return parent::beforeAction($action);
    }

    public function actionCountry()
    {
        if (!$ip = \Yii::$app->request->get('ip')) {
            throw new BadRequestHttpException("Ip не найден");
        }
        return $this->actionGetCountry($ip);
    }

    public function actionNetwork()
    {
        if (!$ip = \Yii::$app->request->get('ip')) {
            throw new BadRequestHttpException("Ip не найден");
        }
        $config = [];
        $config['ip'] = $ip;
        $client = \Yii::createObject([
            'class' => Request::class,
            'params' => $config,
        ]);
        $arr = $client->getResponse(\Yii::$app->params['go-network']);
        $cache = new Memcache();
        $key = $ip . 'network';
        if (!$cache->getKey($key)) {
            $cache->addKey($key, $arr);
        }
        return $cache->getKey($key);
    }

    public function actionGetRecords()
    {
        $ip = '1.0.0.0';
        $client = \Yii::createObject([
            'class' => Request::class,
            'params' => [
                "ip" => $ip,
            ],
        ]);
        $arr = $client->getResponse('tester/networks');
        $country_record = $this->actionGetCountry($ip);
        if (!isset($country_record['data'])) {
            throw new InternalErrorException("Invalid data");
        }

        if (!$country = Country::find()->where(['iso_code' => $country_record['data']['iso_code']])->one()) {
            $country = new Country();
            $country->iso_code = $country_record['data']['iso_code'];
            $country->save();
        }

        foreach ($arr['data'] as $k => $subnet) {
            $object = \Yii::createObject([
                'class' => Network::class,
                "ip" => $subnet['ip'],
                "network" => $subnet['network'],
                'mask' => $subnet['mask'],
                'mask_size' => $subnet['size'],
                'country_id' => $country->id,

            ]);
            $object->save(false);
        }

        return ['Updated'];
    }

    private function actionGetCountry(string $ip)
    {
        $config = [];
        $config['ip'] = $ip;

        $client = \Yii::createObject([
            'class' => Request::class,
            'params' => $config,
        ]);
        $arr = $client->getResponse(\Yii::$app->params['go-country']);
        if ($arr['result'] == Request::STATUS_FAIL) {
            return $arr;
        }
        $cache = new Memcache();
        if (!$cache->getKey($ip)) {
            $cache->addKey($ip, $arr);
        }
        return $cache->getKey($ip);
    }
}
