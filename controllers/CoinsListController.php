<?php
namespace app\controllers;
use Yii;
// use yii\web\Controller;
use app\models\Currencies;
use app\models\Coinlistinfo;
use app\models\Exchangelist;
use yii\helpers\Json;
use yii\data\ActiveDataProvider;
use yii\rest\ActiveController;
use app\helpers\CryptoCoins;

class CoinsListController extends ActiveController
{
    public $modelClass = 'app\models\Coinlistinfo';

    public function actionYour() {
        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        $dataz = Currencies::find()->joinWith(['coinlistinfos'])->asArray()->all();
        return  $dataz;
    }

    public function actionIndex(){}


    public function extraFields() {
        return ['coinlistinfos'];
    }


    public function actionExchangeList(){
        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        $exchangeList = Exchangelist::find()->all();
        return ($exchangeList);
    }
   
    public function actionExchangeCoinList(){
        if(Yii::$app->request->post())
        {   
            $coinInputSymbol = Yii::$app->request->post('coinInputSymbol');
            $coinName = trim($coinInputSymbol);
            $coinName = str_replace('_', ' / ', $coinName);
            $coinName = str_replace('-', ' ', $coinName);
           
            $data = Currencies::find()
            ->where(['CoinName'=>$coinName])
            ->joinWith(['coinlistinfos'])
            ->asArray()
            ->all();
            return ($data);
        }
    }

    public function actionExchangeMarketList(){
        if(Yii::$app->request->post())
        {   
            $coinInputSymbol = Yii::$app->request->post('coinInputSymbol');
            $coinName = trim($coinInputSymbol);
            $coinName = str_replace('_', ' / ', $coinName);
            $coinName = str_replace('-', ' ', $coinName);
            $market = Currencies::find()
            ->where(['CoinName'=>$coinName])
            ->one();
           
            $data = Exchangelist::find()
            ->where(['FROMSYMBOL'=>$market['id']])
            ->all();
            
            return ($data);
        }
        
    }

    public function actionOnlyMarket(){
        if(Yii::$app->request->post())
        {   
            //TODO: handle market by id
            $market = Yii::$app->request->post('MARKET');
            $data = Exchangelist::find()
            ->where(['MARKET'=>$market])
            ->all();
            return ($data);
        }
    }
}