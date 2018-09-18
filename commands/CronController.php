<?php
// namespace app\controllers;

// use yii\web\Controller;

namespace app\commands;
use yii\console\Controller;
use app\models\Currencies;
use app\models\Coinlistinfo;
use app\models\Exchangelist;
use app\helpers\CryptoCoins;
use app\models\Exchanges;

use Yii;
/**
 * Cron controller
 */
class CronController extends Controller {

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

    public function actionStoreCoins() {
        $cryptoCoins = new CryptoCoins();
        $decode = $cryptoCoins->getList();
        $length = count($decode['Data']);
        $coinContentList = [];

        $marketList=[];
        $exists = '';
        $notExists = '';

        forEach($decode['Data'] as $key) {
            $model = Currencies::find()->where( [ 'CoinId' => $key['Id'] ] )->one();
            $errorCoinID = "57705, 180001, 620037";
            $leftCoinID = explode(',', $errorCoinID);

            if(!in_array($key['Id'], $leftCoinID)){
                if($model==null){
                    $model = new Currencies();
                }
                $transaction = Currencies::getDb()->beginTransaction();
                try {
                    $model->CoinId = $key['Id'];
                    $model->Symbol = $key['Symbol'];
                    $model->CoinName = $key['CoinName'];
                    $imageUrl = (isset($key['ImageUrl'])? trim($key['ImageUrl']):'');
                    if(!empty($imageUrl)) {
                        $ext = pathinfo('https://www.cryptocompare.com'.$imageUrl,PATHINFO_EXTENSION);
                        if(!file_exists(Yii::$app->basePath.'/web/uploads/'.$key['Name'].'.'.$ext)) {
                            file_put_contents(Yii::$app->basePath.'/web/uploads/'.$key['Name'].'.'.$ext, file_get_contents('https://www.cryptocompare.com'.$imageUrl) );
                        }
                    }
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
                    // $model->IsTrading = $key['IsTrading'];!
                    $model->save();
                    // ...other DB operations...
                    $transaction->commit();
                } catch(\Exception $e) {
                    $transaction->rollBack();
                    throw $e;
                } catch(\Throwable $e) {
                    $transaction->rollBack();
                    throw $e;
                }
            }
        }
        return 'done';

    }

    public function actionStoreCoinMarket(){
        $cryptoCoins = new CryptoCoins();
        $decode = $cryptoCoins->getList();
        $length = count($decode['Data']);
        // $sho= $decode['DefaultWatchlist']['CoinIs'];
        $coinContentList = [];
        // $url_string = explode(',', $sho);
        $data = Currencies::find()->all();
        $listSymbols = [];
        $staticListSymbol = "USD,EUR,ETH";
        // Yii::$app->db->createCommand()->truncateTable('coinlistinfo')->execute();

        foreach($data as $datazz){
            // if(in_array($datazz['CoinId'], $url_string)){
                $listSymbolz = $datazz['Symbol'];
                // $datas = json_decode($this->curlToGetPriceApi('get', $datazz->Symbol, $staticListSymbol));
                $cryptoCoins = new CryptoCoins();
                $datas = $cryptoCoins->getPrice($datazz->Symbol, $staticListSymbol);
                if(isset($datas->RAW->$listSymbolz)) {
                    $fordata = $datas->RAW->$listSymbolz;

                    foreach($fordata as $ls){

                        $exchangesModel = Exchanges::find()->where(['MARKET' => $ls->LASTMARKET])->one();

                        if ($exchangesModel == null) {
                            $exchangesModel = new Exchanges;
                            $exchangesModel->MARKET = $ls->LASTMARKET;
                            $exchangesModel->save(false);
                        }

                        $currenciesModel = Currencies::find()->where(['Name' => $ls->TOSYMBOL])->one();

                        $models = Coinlistinfo::find()
                        ->where(['CoinlistId' => $datazz['id'],'TOSYMBOL' =>  $currenciesModel->id])
                        ->one();
                        if($models==null){
                            $models = new Coinlistinfo();
                        }

                        $transaction = Coinlistinfo::getDb()->beginTransaction();
                        try {
                            $models->CoinlistId = $datazz['id'];
                            // $models->LiveCoinId = $datazz['CoinId'];
                            // $models->CoinInputSymbol = $datazz['Symbol'];
                            $models->TYPE = $ls->TYPE;
                            $models->MARKET = $ls->MARKET;
                            // $models->FROMSYMBOL = $ls->FROMSYMBOL;
                            $models->TOSYMBOL = $currenciesModel->id;
                            $models->FLAGS = $ls->FLAGS;
                            $models->PRICE = $ls->PRICE;
                            $models->LASTUPDATE = $ls->LASTUPDATE;
                            $models->LASTVOLUME = $ls->LASTVOLUME;
                            $models->LASTVOLUMETO = $ls->LASTVOLUMETO;
                            $models->LASTTRADEID = $ls->LASTTRADEID;
                            $models->VOLUMEDAY = isset($ls->VOLUMEDAY) ? $ls->VOLUMEDAY : 0 ;
                            $models->VOLUMEDAYTO = isset($ls->VOLUMEDAYTO) ? $ls->VOLUMEDAYTO : 0 ;
                            $models->VOLUME24HOUR = $ls->VOLUME24HOUR;
                            $models->VOLUME24HOURTO = $ls->VOLUME24HOURTO;
                            $models->OPENDAY = isset($ls->OPENDAY) ? $ls->OPENDAY : 0 ;
                            $models->HIGHDAY = isset($ls->HIGHDAY) ? $ls->HIGHDAY : 0 ;
                            $models->LOWDAY = isset($ls->LOWDAY) ? $ls->LOWDAY : 0 ;
                            $models->OPEN24HOUR = $ls->OPEN24HOUR;
                            $models->HIGH24HOUR = $ls->HIGH24HOUR;
                            $models->LOW24HOUR = $ls->LOW24HOUR;
                            $models->LASTMARKET = $exchangesModel->id;
                            $models->CHANGE24HOUR = $ls->CHANGE24HOUR;
                            $models->CHANGEPCT24HOUR = $ls->CHANGEPCT24HOUR;
                            $models->CHANGEPCTDAY = $ls->CHANGEPCTDAY;
                            $models->SUPPLY = $ls->SUPPLY;
                            $models->MKTCAP = $ls->MKTCAP;
                            $models->TOTALVOLUME24H = $ls->TOTALVOLUME24H;
                            $models->TOTALVOLUME24HTO = $ls->TOTALVOLUME24HTO;
                            $models->save();
                            // ...other DB operations...
                            $transaction->commit();
                        } catch(\Exception $e) {
                            $transaction->rollBack();
                            throw $e;
                        } catch(\Throwable $e) {
                            $transaction->rollBack();
                            throw $e;
                        }
                    }
                }
            // }
        }

    }
        public function actionStoreExchangeList() {
            $cryptoCoins = new CryptoCoins();
            $exchanges = $cryptoCoins->getExchanges();
            foreach($exchanges as $marketName => $checkList) {
                $exchangemodel = $this->saveMarkets($marketName);
                foreach($checkList as $key => $value) {
                    if(!preg_match("/^0x/",$key)){
                        if (ctype_print($key)) {
                            $currenciesModel = Currencies::find()->where( [ 'Name' => $key ] )->one();
                            if($currenciesModel==null) {
                                $currenciesModel = new Currencies();
                                $currencyTransact = Currencies::getDb()->beginTransaction();
                                try {
                                    $currenciesModel->Name = $key;
                                    $currenciesModel->Symbol = $key;
                                    $currenciesModel->CoinName = $key;
                                    $currenciesModel->FullName = $key;
                                    $currenciesModel->save(false);
                                    // ...other DB operations...
                                    $currencyTransact->commit();
                                } catch(\Exception $error) {
                                    $currencyTransact->rollBack();
                                    throw $error;
                                } catch(\Throwable $error) {
                                    $currencyTransact->rollBack();
                                    throw $error;
                                }
                            }
                            foreach($value as $ls)
                            {
                                $cryptoCoins = new CryptoCoins();
                                $decodes = $cryptoCoins->getTopExchanges($key, $ls);
                                $exchangeList = $decodes['Data']['Exchanges'];
                                foreach($exchangeList as $exlistAll) {
                                    $models = Exchangelist::find()
                                        ->where(['FROMSYMBOL' => $currenciesModel->id,
                                        'MARKET' => $exchangemodel->id,
                                        'TOSYMBOL' => $exlistAll['TOSYMBOL'] ]) 
                                        ->one();

                                    if($models==null){
                                        $models = new Exchangelist();
                                    }

                                    $transaction = Exchangelist::getDb()->beginTransaction();
                                    try {
                                        // $models->LiveCoinId = $coinId;
                                        $models->TYPE = $exlistAll['TYPE'];
                                        $models->MARKET = $exchangemodel->id;
                                        $models->FROMSYMBOL = $currenciesModel->id;
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
                                        // ...other DB operations...
                                        $transaction->commit();
                                    } catch(\Exception $e) {
                                        $transaction->rollBack();
                                        throw $e;
                                    } catch(\Throwable $e) {
                                        $transaction->rollBack();
                                        throw $e;
                                    }
                                }
                            }
                        } 
                }
                }
            }
            return ('done');
        }

    function saveMarkets($marketName) {
        $exchangemodel = Exchanges::find()
        ->where(['MARKET' =>  $marketName ])
        ->one();

         if($exchangemodel==null){
             $exchangemodel = new Exchanges();
         }

        $transaction1 = Exchanges::getDb()->beginTransaction();
        try {
            $exchangemodel->MARKET = $marketName;
            $exchangemodel->save(false);
        // ...other DB operations...
        $transaction1->commit();
        } catch(\Exception $e) {
            $transaction1->rollBack();
            throw $e;
        } catch(\Throwable $e) {
            $transaction1->rollBack();
            throw $e;
        }
        return $exchangemodel;
    }
}
