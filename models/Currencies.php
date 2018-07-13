<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "Currencies".
 *
 * @property int $id
 * @property int $CoinId
 * @property string $Url
 * @property string $ImageUrl
 * @property string $Name
 * @property string $Symbol
 * @property string $CoinName
 * @property string $FullName
 * @property string $Algorithm
 * @property string $ProofType
 * @property string $FullyPremined
 * @property string $TotalCoinSupply
 * @property string $PreMinedValue
 * @property string $TotalCoinsFreeFloat
 * @property int $SortOrder
 * @property string $Sponsored
 * @property string $IsTrading
 *
 * @property Coinlistinfo[] $coinlistinfos
 */
class Currencies extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'currencies';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['CoinId', 'SortOrder'], 'integer'],
            // [['Url', 'ImageUrl', 'Name', 'Symbol', 'CoinName', 'FullName', 'Algorithm', 'ProofType', 'FullyPremined', 'TotalCoinSupply', 'PreMinedValue', 'TotalCoinsFreeFloat'], 'string', 'max' => 255],
            [['Name', 'Symbol', 'CoinName', 'FullName', 'Algorithm', 'ProofType', 'FullyPremined', 'TotalCoinSupply', 'PreMinedValue', 'TotalCoinsFreeFloat'], 'string', 'max' => 255],
            [['Sponsored', 'IsTrading'], 'string', 'max' => 45],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'CoinId' => 'Coin ID',
            // 'Url' => 'Url',
            // 'ImageUrl' => 'Image Url',
            'Name' => 'Name',
            'Symbol' => 'Symbol',
            'CoinName' => 'Coin Name',
            'FullName' => 'Full Name',
            'Algorithm' => 'Algorithm',
            'ProofType' => 'Proof Type',
            'FullyPremined' => 'Fully Premined',
            'TotalCoinSupply' => 'Total Coin Supply',
            'PreMinedValue' => 'Pre Mined Value',
            'TotalCoinsFreeFloat' => 'Total Coins Free Float',
            'SortOrder' => 'Sort Order',
            'Sponsored' => 'Sponsored',
            'IsTrading' => 'Is Trading',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCoinlistinfos()
    {
        // return $this->hasOne(Coinlistinfo::className(), ['CoinlistId' => 'id']);
        return $this->hasMany(Coinlistinfo::className(), ['CoinlistId' => 'id']);
    }
}
