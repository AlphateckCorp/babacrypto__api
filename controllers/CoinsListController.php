<?php
namespace app\controllers;
use Yii;
// use yii\web\Controller;
use app\models\Currencies;
use app\models\Coinlistinfo;
use app\models\Exchangelist;
use app\models\Exchanges;
use yii\helpers\Json;
use yii\data\ActiveDataProvider;
use yii\rest\ActiveController;
use app\helpers\CryptoCoins;

class CoinsListController extends ActiveController
{
    public $modelClass = 'app\models\Coinlistinfo';

    public function actionYour() {
        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        $queryParams = Yii::$app->request->queryParams;
        $dataz = Currencies::find()
        ->joinWith(['coinlistinfos'])
        ->limit($queryParams['limit'])
        ->offset($queryParams['offset'])
        ->groupBy('id')
        ->asArray()->all();
        return  $dataz;
    }

    public function actionIndex(){}


    public function extraFields() {
        return ['coinlistinfos'];
    }


    public function actionExchangeList(){
        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        $exchangeList = Exchangelist::find()->joinWith(['exchanges','currencies'])->asArray()->all();
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
            ->joinWith(['exchanges'])->asArray()->all();
            
            return ($data);
        }
        
    }

    public function actionOnlyMarket(){
        if(Yii::$app->request->post())
        {   
            $market = Yii::$app->request->post('MARKET');

            $data = Exchanges::find()
            ->where(['MARKET'=>$market])
            ->one();

            $data = Exchangelist::find()
            ->where(['exchangelist.MARKET'=>$data->id])
            ->joinWith(['currencies','exchanges'])->asArray()->all();
            return ($data);
        }
    }
}