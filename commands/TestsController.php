<?php
// namespace app\controllers;
 
// use yii\web\Controller;

namespace app\commands;
use yii\console\Controller;
use app\models\Currencies;
use app\models\Coinlistinfo;
use app\models\Exchangelist;

use Yii;
/**
 * Test controller
 */
class TestsController extends Controller {
 
    public function actionIndex() {
        echo "cron service runnning";
    }
 
    public function actionMake() {
        $rootyii = realpath(dirname(__FILE__)).'/../';
        $filename = date('H:i:s'). '.txt';
        $folder = $rootyii.'/cronjob/'.$filename;
        $f = fopen($folder, 'w');
        $fw = fwrite($f, 'now :'.$filename);
        fclose($f);
    }
    public function actionStoreCoinMarket(){
        $url = 'https://www.cryptocompare.com/api/data/coinlist';
        $result = $this->curlToRestApi('get', $url);
        $decode = json_decode($result, true);
        $length = count($decode['Data']);
        $sho= $decode['DefaultWatchlist']['CoinIs'];
        $coinContentList = [];
        $url_string = explode(',', $sho);
        $data = Currencies::find()->all();
        $listSymbols = [];
        $staticListSymbol = "USD,EUR,ETH";
        // Yii::$app->db->createCommand()->truncateTable('coinlistinfo')->execute();
        
        foreach($data as $datazz){  
            if(in_array($datazz['CoinId'], $url_string)){
                $listSymbolz = $datazz['Symbol'];
                $datas = json_decode($this->curlToGetPriceApi('get', $datazz->Symbol, $staticListSymbol));
                $fordata = $datas->RAW->$listSymbolz;
                 
                foreach($fordata as $ls){
                    $models = Coinlistinfo::find()
                    ->where(['CoinlistId' => $datazz['id'], ]) //DONE:
                    ->one();
                    if($models==null){
                        $models = new Coinlistinfo();
                    } 
                    $models->CoinlistId = $datazz['id'];
                    $models->LiveCoinId = $datazz['CoinId'];
                    // $models->CoinInputSymbol = $datazz['Symbol'];
                    $models->TYPE = $ls->TYPE;
                    $models->MARKET = $ls->MARKET;
                    // $models->FROMSYMBOL = $ls->FROMSYMBOL;
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
       
    }

    public function curlToRestApi($method, $url, $data = null)
    {

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);

        $result = curl_exec($curl);
        curl_close($curl);
        return $result;
    }
 
    public function curlToGetPriceApi($method, $symbol, $endpoint, $data = null)
    {           
        // $url = "https://min-api.cryptocompare.com/data/pricemultifull?fsyms=".$symbol."&tsyms=BTC,USD,EUR";
        $url = "https://min-api.cryptocompare.com/data/pricemultifull?fsyms=".$symbol."&tsyms=".$endpoint;
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);

        $result = curl_exec($curl);
        
        curl_close($curl);
        return $result;
        
    }

    public function actionStoreExchange(){
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
                              ->where(['FROMSYMBOL' => $exlistAll['FROMSYMBOL'],  //TODO: handle FROMSYMBOL
                                'MARKET' => $exlistAll['MARKET'],
                                'TOSYMBOL' => $exlistAll['TOSYMBOL'] ])
                                ->one();
                        
                            if($models==null){
                                $models = new Exchangelist();
                            } 

                            $models->LiveCoinId = $coinId;                      
                            $models->TYPE = $exlistAll['TYPE'];
                            $models->MARKET = $exlistAll['MARKET'];
                            // $models->FROMSYMBOL = $exlistAll['FROMSYMBOL'];
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
}