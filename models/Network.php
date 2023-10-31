<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "network".
 *
 * @property int         $id
 * @property string|null $network
 * @property string|null $mask
 * @property int|null    $mask_size
 * @property int|null    $country_id
 * @property string|null $ip
 *
 * @property Country     $country
 */
class Network extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'network';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['mask_size', 'country_id'], 'integer'],
            [['network', 'mask'], 'string', 'max' => 255],
            [['country_id'], 'exist', 'skipOnError' => true, 'targetClass' => Country::class, 'targetAttribute' => ['country_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'network' => 'Network',
            'mask' => 'Mask',
            'mask_size' => 'Mask Size',
            'country_id' => 'Country ID',
        ];
    }

    public function getNetworkByIp($ip)
    {
        $sql = 'SELECT network.id, network.mask, network.network, country.iso_code FROM network INNER JOIN country ON country.id = network.country_id WHERE network.mask_size = ( SELECT MIN(network.mask_size) FROM network) AND network.ip =' . $ip;
        return Yii::$app->db->createCommand($sql)->queryOne();
    }

    /**
     * Gets query for [[Country]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getCountry()
    {
        return $this->hasOne(Country::class, ['id' => 'country_id']);
    }
}
