<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "country".
 *
 * @property int         $id
 * @property string|null $iso_code
 *
 * @property Network[]   $networks
 */
class Country extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'country';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['iso_code'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'iso_code' => 'Iso Code',
        ];
    }

    /**
     * Gets query for [[Networks]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getNetworks()
    {
        return $this->hasMany(Network::class, ['country_id' => 'id']);
    }
}

