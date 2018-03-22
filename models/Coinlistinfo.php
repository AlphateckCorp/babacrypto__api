<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "coinlistinfo".
 *
 * @property int $id
 * @property int $CoinlistId
 * @property string $CoinInputSymbol
 * @property int $LiveCoinId
 * @property int $TYPE
 * @property string $MARKET
 * @property string $FROMSYMBOL
 * @property string $TOSYMBOL
 * @property int $FLAGS
 * @property double $PRICE
 * @property double $LASTUPDATE
 * @property double $LASTVOLUME
 * @property double $LASTVOLUMETO
 * @property double $LASTTRADEID
 * @property double $VOLUMEDAY
 * @property double $VOLUMEDAYTO
 * @property double $VOLUME24HOUR
 * @property double $VOLUME24HOURTO
 * @property double $OPENDAY
 * @property double $HIGHDAY
 * @property double $LOWDAY
 * @property double $OPEN24HOUR
 * @property double $HIGH24HOUR
 * @property double $LOW24HOUR
 * @property string $LASTMARKET
 * @property double $CHANGE24HOUR
 * @property double $CHANGEPCT24HOUR
 * @property double $CHANGEPCTDAY
 * @property double $SUPPLY
 * @property double $MKTCAP
 * @property double $TOTALVOLUME24H
 * @property double $TOTALVOLUME24HTO
 *
 * @property Coinlist $coinlist
 */
class Coinlistinfo extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'coinlistinfo';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['CoinlistId'], 'required'],
            [['CoinlistId', 'LiveCoinId', 'TYPE', 'FLAGS'], 'integer'],
            [['PRICE', 'LASTUPDATE', 'LASTVOLUME', 'LASTVOLUMETO', 'LASTTRADEID', 'VOLUMEDAY', 'VOLUMEDAYTO', 'VOLUME24HOUR', 'VOLUME24HOURTO', 'OPENDAY', 'HIGHDAY', 'LOWDAY', 'OPEN24HOUR', 'HIGH24HOUR', 'LOW24HOUR', 'CHANGE24HOUR', 'CHANGEPCT24HOUR', 'CHANGEPCTDAY', 'SUPPLY', 'MKTCAP', 'TOTALVOLUME24H', 'TOTALVOLUME24HTO'], 'number'],
            [['CoinInputSymbol'], 'string', 'max' => 45],
            [['MARKET', 'FROMSYMBOL', 'TOSYMBOL', 'LASTMARKET'], 'string', 'max' => 255],
            [['CoinlistId'], 'exist', 'skipOnError' => true, 'targetClass' => Coinlist::className(), 'targetAttribute' => ['CoinlistId' => 'id']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'CoinlistId' => 'Coinlist ID',
            'CoinInputSymbol' => 'Coin Input Symbol',
            'LiveCoinId' => 'Live Coin ID',
            'TYPE' => 'Type',
            'MARKET' => 'Market',
            'FROMSYMBOL' => 'Fromsymbol',
            'TOSYMBOL' => 'Tosymbol',
            'FLAGS' => 'Flags',
            'PRICE' => 'Price',
            'LASTUPDATE' => 'Lastupdate',
            'LASTVOLUME' => 'Lastvolume',
            'LASTVOLUMETO' => 'Lastvolumeto',
            'LASTTRADEID' => 'Lasttradeid',
            'VOLUMEDAY' => 'Volumeday',
            'VOLUMEDAYTO' => 'Volumedayto',
            'VOLUME24HOUR' => 'Volume24 Hour',
            'VOLUME24HOURTO' => 'Volume24 Hourto',
            'OPENDAY' => 'Openday',
            'HIGHDAY' => 'Highday',
            'LOWDAY' => 'Lowday',
            'OPEN24HOUR' => 'Open24 Hour',
            'HIGH24HOUR' => 'High24 Hour',
            'LOW24HOUR' => 'Low24 Hour',
            'LASTMARKET' => 'Lastmarket',
            'CHANGE24HOUR' => 'Change24 Hour',
            'CHANGEPCT24HOUR' => 'Changepct24 Hour',
            'CHANGEPCTDAY' => 'Changepctday',
            'SUPPLY' => 'Supply',
            'MKTCAP' => 'Mktcap',
            'TOTALVOLUME24H' => 'Totalvolume24 H',
            'TOTALVOLUME24HTO' => 'Totalvolume24 Hto',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    // public function getCoinlist()
    // {
    //     return $this->hasOne(Coinlist::className(), ['id' => 'CoinlistId']);
    // }
}
