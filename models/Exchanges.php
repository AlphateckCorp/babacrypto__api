<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "Exchanges".
 *
 *
 */
class Exchanges extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'exchanges';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['MARKET', 'externalLink'], 'safe']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'MARKET' => 'Market',
            // 'Url' => 'Url',
            // 'ImageUrl' => 'Image Url',
            'externalLink' => 'External link'
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    // public function getCoinlistinfos()
    // {
    //     // return $this->hasOne(Coinlistinfo::className(), ['CoinlistId' => 'id']);
    //     return $this->hasMany(Coinlistinfo::className(), ['CoinlistId' => 'id']);
    // }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getExchangeList()
    {
        return $this->hasMany(Exchangelist::className(), ['MARKET' => 'id']);
    }
}
