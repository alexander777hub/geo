<?php


namespace app\models;

/**
 * Class Memcache
 *
 * @package app\models
 */
class Memcache
{
    public $cache;

    public function __construct()
    {
        $this->cache = \Yii::$app->cache;
    }

    public function getKey($key)
    {
        return $this->cache->get($key);
    }

    public function addKey($key, $data)
    {
        $this->cache->set($key, $data);
    }
}
