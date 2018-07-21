<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "exchangelist".
 *
 * @property int $id
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
 * @property double $VOLUME24HOUR
 * @property double $VOLUME24HOURTO
 * @property double $OPEN24HOUR
 * @property double $HIGH24HOUR
 * @property double $LOW24HOUR
 * @property double $CHANGE24HOUR
 * @property double $CHANGEPCT24HOUR
 * @property double $CHANGEPCTDAY
 * @property double $CHANGEDAY
 */
class Exchangelist extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'exchangelist';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id'], 'required'],
            [['id', 'TYPE', 'FLAGS'], 'integer'],
            [['PRICE', 'LASTUPDATE', 'LASTVOLUME', 'LASTVOLUMETO', 'LASTTRADEID', 'VOLUME24HOUR', 'VOLUME24HOURTO', 'OPEN24HOUR', 'HIGH24HOUR', 'LOW24HOUR', 'CHANGE24HOUR', 'CHANGEPCT24HOUR', 'CHANGEPCTDAY', 'CHANGEDAY'], 'number'],
            [['MARKET', 'FROMSYMBOL', 'TOSYMBOL'], 'string', 'max' => 255],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
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
            'VOLUME24HOUR' => 'Volume24 Hour',
            'VOLUME24HOURTO' => 'Volume24 Hourto',
            'OPEN24HOUR' => 'Open24 Hour',
            'HIGH24HOUR' => 'High24 Hour',
            'LOW24HOUR' => 'Low24 Hour',
            'CHANGE24HOUR' => 'Change24 Hour',
            'CHANGEPCT24HOUR' => 'Changepct24 Hour',
            'CHANGEPCTDAY' => 'Changepctday',
            'CHANGEDAY' => 'Changeday',
        ];
    }
}
