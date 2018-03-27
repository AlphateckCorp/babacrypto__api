<?php
namespace app\controllers;
use Yii;
// use yii\web\Controller;
use app\models\Coinlist;
use app\models\Coinlistinfo;
use app\models\Exchangelist;
//use yii\helpers\Json;
use yii\data\ActiveDataProvider;
use yii\rest\ActiveController;

class CoinsListController extends ActiveController
{
    public $modelClass = 'app\models\Coinlistinfo';

    public function actionYour() {

        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        $url = 'https://min-api.cryptocompare.com/data/all/coinlist';
        $result = $this->curlToRestApi('get', $url);
        $decode = json_decode($result, true);
        $topCoins= $decode['DefaultWatchlist']['CoinIs'];
        $coinContentList = [];
        $data = Coinlist::find()->all();    
        
        $url_string = explode(',', $topCoins);
        foreach($url_string as $urls){
            $dataz[] = Coinlist::find()->where(['CoinId'=> $urls])->asArray()->one();
        }
        
        foreach($dataz as $datazz){  
            if(in_array($datazz['CoinId'], $url_string)){
                $coinContentList[] = Coinlist::find()
                    ->where(['CoinId'=>$datazz['CoinId']])
                    ->joinWith(['coinlistinfos'])
                    ->asArray()
                    ->one();
            }
        }    
        return $coinContentList;

        // $query = new yii\db\Query;
        // \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        // return new ActiveDataProvider([
        //     'query' => Coinlist::find()
        //     ->joinWith(['coinlistinfos'])
        //     // ->with(['coinlistinfos'])
        //     ->asArray()
        //     // ->orderBy(['SortOrder' => SORT_ASC])
        //     ->limit(10)
        // ]);
    }

    public function actionIndex()
    {
        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        // // // $model = new Coinlist();
        // $coinlistData = Coinlistinfo::find()
        // ->all();

        // $query = Coinlist::find()
        // ->joinWith(['coinlistinfos'])
        // ->orderBy(['SortOrder' => SORT_ASC])
        // ->limit(10)
        // ->asArray()
        // ->all();

        // return $provider = new ActiveDataProvider([
        //     'query' => $query,
        //  ]);

        /*
        $data = CoinList::find()
        ->orderBy(['SortOrder' => SORT_ASC])
        ->limit(10)
        ->all();
        */
       // return ($data);
        // return (["data"=> $data, "coinlistdata" =>$coinlistData]);
        // return ({"data":$data, "coinlistdata":$coinlistData});
    //    return $this->render('index');
    }

    public function curlToRestApi($method, $url, $data = null)
    {

        $curl = curl_init();
        switch ($method) {
            case 'POST':
                curl_setopt($curl, CURLOPT_POST, 1);

                if($data !== null) {
                    curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
                }
                break;
                // logic for other methods of interest
                // .
                // .
                // .

            default:
                if ($data !== null){
                    $url = sprintf("%s?%s", $url, http_build_query($data));
                }
        }

        // Authentication [Optional]
        // curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
        // curl_setopt($curl, CURLOPT_USERPWD, "username:password");
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);

        $result = curl_exec($curl);

        curl_close($curl);

        return $result;
    }

    public function curlToGetPriceApi($method, $symbol, $endpoint, $data = null)
    {           
        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        
        // $url = "https://min-api.cryptocompare.com/data/pricemultifull?fsyms=".$symbol."&tsyms=BTC,USD,EUR";
        $url = "https://min-api.cryptocompare.com/data/pricemultifull?fsyms=".$symbol."&tsyms=".$endpoint;
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);

        $result = curl_exec($curl);
        
        curl_close($curl);
        return $result;
        
    }
    public function actionFetch(){

        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        $url = 'https://min-api.cryptocompare.com/data/all/coinlist';
        $result = $this->curlToRestApi('get', $url);
        $decode = json_decode($result, true);
        $length = count($decode['Data']);
        $sho= $decode['DefaultWatchlist']['CoinIs'];
        $coinContentList = [];
        $url_string = explode(',', $sho);
        $data = Coinlist::find()->all();
        $listSymbols = [];
        $staticListSymbol = "USD,EUR,ETH";
        foreach($data as $datazz){  
            if(in_array($datazz['CoinId'], $url_string)){
                // $listSymbols[] = $datazz['Symbol'];
                $listSymbolz = $datazz['Symbol'];
                $datas = json_decode($this->curlToGetPriceApi('get', $datazz->Symbol, $staticListSymbol));
                //  $listofData[] = json_decode($datas);
                $fordata = $datas->RAW->$listSymbolz;
                // print_r($datas->RAW->$listSymbolz);
                foreach($fordata as $ls){
                    $models = new Coinlistinfo();
                    $models->CoinlistId = $datazz['id'];
                    $models->LiveCoinId = $datazz['CoinId'];
                    $models->CoinInputSymbol = $datazz['Symbol'];
                    $models->TYPE = $ls->TYPE;
                    $models->MARKET = $ls->MARKET;
                    $models->FROMSYMBOL = $ls->FROMSYMBOL;
                    $models->TOSYMBOL = $ls->TOSYMBOL;
                    $models->FLAGS = $ls->FLAGS;
                    $models->PRICE = $ls->PRICE;
                    $models->LASTUPDATE = $ls->LASTUPDATE;
                    $models->LASTVOLUME = $ls->LASTVOLUME;
                    $models->LASTVOLUMETO = $ls->LASTVOLUMETO;
                    $models->LASTTRADEID = $ls->LASTTRADEID;
                    $models->VOLUMEDAY = $ls->VOLUMEDAY;
                    $models->VOLUMEDAYTO = $ls->VOLUMEDAYTO;
                    $models->VOLUME24HOUR = $ls->VOLUME24HOUR;
                    $models->VOLUME24HOURTO = $ls->VOLUME24HOURTO;
                    $models->OPENDAY = $ls->OPENDAY;
                    $models->HIGHDAY = $ls->HIGHDAY;
                    $models->LOWDAY = $ls->LOWDAY;
                    $models->OPEN24HOUR = $ls->OPEN24HOUR;
                    $models->HIGH24HOUR = $ls->HIGH24HOUR;
                    $models->LOW24HOUR = $ls->LOW24HOUR;
                    $models->LASTMARKET = $ls->LASTMARKET;
                    $models->CHANGE24HOUR = $ls->CHANGE24HOUR;
                    $models->CHANGEPCT24HOUR = $ls->CHANGEPCT24HOUR;
                    $models->CHANGEPCTDAY = $ls->CHANGEPCTDAY;
                    $models->SUPPLY = $ls->SUPPLY;
                    $models->MKTCAP = $ls->MKTCAP;
                    $models->TOTALVOLUME24H = $ls->TOTALVOLUME24H;
                    $models->TOTALVOLUME24HTO = $ls->TOTALVOLUME24HTO;
                    $models->save();     
                }
                
            }
        }
        return $datas;
    }

    public function actionStoreCoin(){
        
        // \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        $url = 'https://min-api.cryptocompare.com/data/all/coinlist';
        $result = $this->curlToRestApi('get', $url);
        $decode = json_decode($result, true);
        $length = count($decode['Data']);
        $sho= $decode['DefaultWatchlist']['CoinIs'];
        $coinContentList = [];
        $url_string = explode(',', $sho);
        
        // $urls = "https://min-api.cryptocompare.com/data/pricemultifull?fsyms=ETH&tsyms=BTC,USD,EUR";
        // $marketList = $this->curlToGetPriceApi('get', 'ETH');
        // $decodeS = json_decode($marketList, true);
        $marketList=[];
        $exists = '';
        $notExists = '';
        
        forEach($decode['Data'] as $key) {
            $model = new Coinlist();
              $CheckExisting = Coinlist::find()->where( [ 'Symbol' => $key['Symbol'] ] )->exists();
               if($CheckExisting){
                  if(!in_array($key['Id'], $leftCoinID, true)){
                    $model->CoinId = $key['Id'];
                    $model->Symbol = $key['Symbol'];
                    $model->CoinName = $key['CoinName'];
                    $model->Url = $key['Url'];
                    $model->ImageUrl = (isset($key['ImageUrl'])? trim($key['ImageUrl']):'');
                    $model->Name = $key['Name'];
                    $model->FullName = $key['FullName'];
                    $model->Algorithm = $key['Algorithm'];
                    $model->ProofType = $key['ProofType'];
                    $model->FullyPremined = $key['FullyPremined'];
                    $model->TotalCoinSupply = trim($key['TotalCoinSupply']);
                    $model->PreMinedValue = $key['PreMinedValue'];
                    $model->TotalCoinsFreeFloat = $key['TotalCoinsFreeFloat'];
                    $model->SortOrder = $key['SortOrder'];
                    $model->Sponsored = $key['Sponsored'];
                    $model->IsTrading = $key['IsTrading'];
                    $model->save(); 
                    $notExists=1;
                  }
                } else {
                  $errorCoinID = "57705, 180001, 620037";
                  $leftCoinID = explode(',', $errorCoinID);
                  if(!in_array($key['Id'], $leftCoinID)){
                      $model->CoinId = $key['Id'];
                      $model->Symbol = $key['Symbol'];
                      $model->CoinName = $key['CoinName'];
                      $model->Url = $key['Url'];
                      $model->ImageUrl = (isset($key['ImageUrl'])? trim($key['ImageUrl']):'');
                      $model->Name = $key['Name'];
                      $model->FullName = $key['FullName'];
                      $model->Algorithm = $key['Algorithm'];
                      $model->ProofType = $key['ProofType'];
                      $model->FullyPremined = $key['FullyPremined'];
                      $model->TotalCoinSupply = trim($key['TotalCoinSupply']);
                      $model->PreMinedValue = $key['PreMinedValue'];
                      $model->TotalCoinsFreeFloat = $key['TotalCoinsFreeFloat'];
                      $model->SortOrder = $key['SortOrder'];
                      $model->Sponsored = $key['Sponsored'];
                      $model->IsTrading = $key['IsTrading'];
                      $model->save(); 
                      $exists = 1;
                  }
                }
                
        }
        // $decodeS = json_decode($coinlistStatus, true);
        //  return $decodeS;
         if($exists!=''){
            return 'exits';
         }else if($notExists!=''){
           return 'nonExists';
         }else{
           return 'nothing';
         }
               
    }

    public function extraFields() {
        return ['coinlistinfos'];
    }
    public function actionStoreExchangeList(){
        $url = "https://min-api.cryptocompare.com/data/all/exchanges";
        $result = $this->curlToRestApi('get', $url);
        $decode = json_decode($result, true);
        $checkList = ($decode['Cryptsy']);
        foreach($checkList as $key => $value){
                foreach($value as $ls)
                {
                    $urls="https://min-api.cryptocompare.com/data/top/exchanges/full?fsym=".$key."&tsym=".$ls;
                    $results = $this->curlToRestApi('get', $urls);
                    $decodes = json_decode($results, true);
                    $counts = count($decodes['Data']['CoinInfo']);
                    $coinId='';
                    if($counts>0){
                        $coinId= $decodes['Data']['CoinInfo']['Id'];
                    }
                    $exchangeList = $decodes['Data']['Exchanges'];
                    foreach($exchangeList as $exlistAll){
                        
                            $models = Exchangelist::find()
                              ->where(['FROMSYMBOL' => $exlistAll['FROMSYMBOL'], 
                                'MARKET' => $exlistAll['MARKET'],
                                'TOSYMBOL' => $exlistAll['TOSYMBOL'] ])
                                ->one();
                        
                            if($models==null){
                                $models = new Exchangelist();
                            } 

                            $models->LiveCoinId = $coinId;                      
                            $models->TYPE = $exlistAll['TYPE'];
                            $models->MARKET = $exlistAll['MARKET'];
                            $models->FROMSYMBOL = $exlistAll['FROMSYMBOL'];
                            $models->TOSYMBOL = $exlistAll['TOSYMBOL'];
                            $models->FLAGS = $exlistAll['FLAGS'];
                            $models->PRICE = $exlistAll['PRICE'];
                            $models->LASTUPDATE = $exlistAll['LASTUPDATE'];
                            $models->LASTVOLUME = $exlistAll['LASTVOLUME'];
                            $models->LASTVOLUMETO = $exlistAll['LASTVOLUMETO'];
                            $models->LASTTRADEID = $exlistAll['LASTTRADEID'];
                            $models->VOLUME24HOUR = $exlistAll['VOLUME24HOUR'] ;
                            $models->VOLUME24HOURTO = $exlistAll['VOLUME24HOURTO'];
                            $models->OPEN24HOUR = $exlistAll['OPEN24HOUR'];
                            $models->HIGH24HOUR = $exlistAll['HIGH24HOUR'];
                            $models->LOW24HOUR = $exlistAll['LOW24HOUR'];
                            $models->CHANGE24HOUR = $exlistAll['CHANGE24HOUR'];
                            $models->CHANGEPCT24HOUR = $exlistAll['CHANGEPCT24HOUR'];
                            $models->CHANGEPCTDAY = $exlistAll['CHANGEPCTDAY'];
                            $models->CHANGEDAY = $exlistAll['CHANGEDAY'];
                            $models->save(false);
                    }
            }
        }
        return ('done');
    }
    public function getExchange($method, $url, $data = null)
    {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        $result = curl_exec($curl);
        curl_close($curl);
        return $result;
    }
    public function actionExchangeList(){
        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        $exchangeList = Exchangelist::find()->all();
        return ($exchangeList);
    }
   
    public function actionExchangeCoinList(){
        // \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        if(Yii::$app->request->post())
        {
            $coinInputSymbol = Yii::$app->request->post('coinInputSymbol');
            $data = Coinlistinfo::find()
            ->where(['CoinInputSymbol'=>$coinInputSymbol])
            ->all();
            return ($data);
        }   
        
    }
}