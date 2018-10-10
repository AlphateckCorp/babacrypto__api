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
            $sort = explode(',',$queryParams['sort']);
                $dataz = Currencies::find()
                ->where(['!=','Name','USD'])
                ->andWhere(['!=','Name','EUR'])
                ->andWhere(['=','TOSYMBOL',$queryParams['currency']])
                ->joinWith(['coinlistinfos'])
                ->limit($queryParams['limit'])
                ->offset($queryParams['offset'])
                ->orderBy([$sort[0] => $sort[1] == 'asc' ? SORT_ASC : SORT_DESC]) 
                ->groupBy($sort[0])
                ->asArray()
                ->all();
                return  $dataz;
        }

             public function actionMarketCap() {
                \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
                $queryParams = Yii::$app->request->queryParams;
                $curr = Currencies::find()->where(['Symbol'=>$queryParams['symbol']])->one();
                $result = Coinlistinfo::find()->where(['TOSYMBOL'=>$curr->id])->sum('MKTCAP');
                return $result;
            }

        public function actionIndex(){}


        public function extraFields() {
            return ['coinlistinfos'];
        }


    public function actionExchangeList(){
        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        $count =  Exchanges::find()->count();
        $queryParams = Yii::$app->request->queryParams;
        $sort = explode(',',$queryParams['sort']);
        $limit = $queryParams['limit'];
        $offset = $queryParams['offset'];
        $command = Yii::$app->db->createCommand("SELECT exchanges.id,exchanges.market,group_concat(DISTINCT(currencies.Symbol)) as coins,exchanges.externalLink, sum(exchangelist.VOLUME24HOUR) as VOLUME24HOUR FROM `exchanges` LEFT JOIN `exchangelist` ON exchanges.id = exchangelist.MARKET LEFT JOIN `currencies` ON currencies.id=exchangelist.FROMSYMBOL GROUP BY exchanges.MARKET  ORDER BY ".$sort[0]." ".$sort[1]." LIMIT ".$limit." OFFSET ".$offset);
        $result = $command->queryAll();
        return ['rows'=>$result,'count'=>$count];
    }
   
    public function actionMarket() {
        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        $queryParams = Yii::$app->request->queryParams;
        $market =  Exchanges::find()->where(['MARKET' => $queryParams['market']])->one();
        return $market;
    }

    public function actionExchangeCoinList(){
        if(Yii::$app->request->post())
        {   
            $coinInputSymbol = Yii::$app->request->post('coinInputSymbol');
            $coinName = trim($coinInputSymbol);
            $coinName = str_replace('_', ' / ', $coinName);
            $coinName = str_replace('-', ' ', $coinName);
           
            $data = Currencies::find()
            ->where(['currencies.CoinName'=>$coinName])
            ->joinWith(['coinlistinfos','coinlistinfos.tosymbol coin'])
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