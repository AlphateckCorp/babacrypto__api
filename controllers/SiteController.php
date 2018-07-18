<?php

namespace app\controllers;

use Yii;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\web\Response;
use yii\filters\VerbFilter;
use app\models\LoginForm;
use app\models\ContactForm;
use app\models\Currencies;
use app\models\Exchangelist;
use app\models\Coinlistinfo;
use app\helpers\CryptoCoins;
use app\models\Exchanges;

class SiteController extends Controller
{
    
    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'only' => ['logout'],
                'rules' => [
                    [
                        'actions' => ['logout'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'logout' => ['post'],
                ],
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function actions()
    {
        return [
            'error' => [
                'class' => 'yii\web\ErrorAction',
            ],
            'captcha' => [
                'class' => 'yii\captcha\CaptchaAction',
                'fixedVerifyCode' => YII_ENV_TEST ? 'testme' : null,
            ],
        ];
    }

    /**
     * Displays homepage.
     *
     * @return string
     */
    public function actionIndex()
    {
        return $this->render('index');
    }

    // NOTE: commented not used now

    // public function curlToRestApi($method, $url, $data = null)
    // {

    //     $curl = curl_init();

    //     // switch $method
    //     switch ($method) {
    //         case 'POST':
    //             curl_setopt($curl, CURLOPT_POST, 1);

    //             if($data !== null) {
    //                 curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
    //             }
    //             break;
    //             // logic for other methods of interest
    //             // .
    //             // .
    //             // .

    //         default:
    //             if ($data !== null){
    //                 $url = sprintf("%s?%s", $url, http_build_query($data));
    //             }
    //     }

    //     // Authentication [Optional]
    //     // curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
    //     // curl_setopt($curl, CURLOPT_USERPWD, "username:password");
    //     curl_setopt($curl, CURLOPT_URL, $url);
    //     curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);

    //     $result = curl_exec($curl);

    //     curl_close($curl);

    //     return $result;
    // }

    /**
     * Login action.
     *
     * @return Response|string
     */
    public function actionLogin()
    {
        if (!Yii::$app->user->isGuest) {
            return $this->goHome();
        }

        $model = new LoginForm();
        if ($model->load(Yii::$app->request->post()) && $model->login()) {
            return $this->goBack();
        }

        $model->password = '';
        return $this->render('login', [
            'model' => $model,
        ]);
    }

    /**
     * Logout action.
     *
     * @return Response
     */
    public function actionLogout()
    {
        Yii::$app->user->logout();

        return $this->goHome();
    }

    /**
     * Displays contact page.
     *
     * @return Response|string
     */
    public function actionContact()
    {
        $model = new ContactForm();
        if ($model->load(Yii::$app->request->post()) && $model->contact(Yii::$app->params['adminEmail'])) {
            Yii::$app->session->setFlash('contactFormSubmitted');

            return $this->refresh();
        }
        return $this->render('contact', [
            'model' => $model,
        ]);
    }

    /**
     * Displays about page.
     *
     * @return string
     */
    public function actionAbout()
    {
        return $this->render('about');
    }

    public function actionStoreCoins() {
        
        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        // $url = 'https://min-api.cryptocompare.com/data/all/coinlist';
        // $result = $this->curlToRestApi('get', $url);
        // $decode = json_decode($result, true);
        $cryptoCoins = new CryptoCoins();
        $decode = $cryptoCoins->getList();
        $length = count($decode['Data']);
        // $sho= $decode['DefaultWatchlist']['CoinIs'];
        $coinContentList = [];
        // $url_string = explode(',', $sho);
        
        // $urls = "https://min-api.cryptocompare.com/data/pricemultifull?fsyms=ETH&tsyms=BTC,USD,EUR";
        // $marketList = $this->curlToGetPriceApi('get', 'ETH');
        // $decodeS = json_decode($marketList, true);
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
                    // $model->Url = $key['Url'];
                    // $model->ImageUrl = (isset($key['ImageUrl'])? trim($key['ImageUrl']):'');
                    // echo Yii::$app->basePath.'/web/upload';exit;
                    // $imageUrl = (isset($key['ImageUrl'])? trim($key['ImageUrl']):'');
                    // if(!empty($imageUrl)) {
                    //     $ext = pathinfo('https://www.cryptocompare.com'.$imageUrl,PATHINFO_EXTENSION);
                    //     if(!file_exists(Yii::$app->basePath.'/web/uploads/'.$key['Name'].'.'.$ext)) {
                    //         file_put_contents(Yii::$app->basePath.'/web/uploads/'.$key['Name'].'.'.$ext, file_get_contents('https://www.cryptocompare.com'.$imageUrl) );
                    //     }
                    // }
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
                    // $model->IsTrading = $key['IsTrading'];
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
            
            // print_r($model);
            /*

            $model = new Currencies();
              $CheckExisting = Currencies::find()->where( [ 'Symbol' => $key['Symbol'] ] )->exists();
               if($CheckExisting){
                  if(!in_array($key['Id'], $leftCoinID, true)){
                    $model->CoinId = $key['Id'];
                    $model->Symbol = $key['Symbol'];
                    $model->CoinName = $key['CoinName'];
                    // $model->Url = $key['Url'];
                    // $model->ImageUrl = (isset($key['ImageUrl'])? trim($key['ImageUrl']):'');
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
                    //   $model->Url = $key['Url'];
                    //   $model->ImageUrl = (isset($key['ImageUrl'])? trim($key['ImageUrl']):'');
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
                      $model->save(false); 
                      $exists = 1;
                  }
                }
                */
                
        }
        
            // exit;
        // $decodeS = json_decode($coinlistStatus, true);
        //  return $decodeS;
        //  if($exists!=''){
        //     return 'exits';
        //  }else if($notExists!=''){
        //    return 'nonExists';
        //  }else{
        //    return 'nothing';
        //  }
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
                if(isset($datas->RAW)) {
                    $fordata = $datas->RAW->$listSymbolz;
                 
                    foreach($fordata as $ls){
                        $models = Coinlistinfo::find()
                        ->where(['CoinlistId' => $datazz['id'], ])
                        ->one();
                        if($models==null){
                            $models = new Coinlistinfo();
                        }
                        
                        $transaction = Coinlistinfo::getDb()->beginTransaction();
                        try {
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
                            $models->LASTMARKET = $ls->LASTMARKET;
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
        $decode = $cryptoCoins->getExchanges();
        $checkList = ($decode['Cryptsy']);
        foreach($checkList as $key => $value){
                foreach($value as $ls)
                {
                    $cryptoCoins = new CryptoCoins();
                    $decodes = $cryptoCoins->getTopExchanges($key, $ls);
                    // $urls="https://min-api.cryptocompare.com/data/top/exchanges/full?fsym=".$key."&tsym=".$ls;
                    // $results = $this->curlToRestApi('get', $urls);
                    // $decodes = json_decode($results, true);
                    $counts = count($decodes['Data']['CoinInfo']);
                    $coinId='';
                    if($counts>0){
                        $coinId= $decodes['Data']['CoinInfo']['Id'];
                    }
                    $exchangeList = $decodes['Data']['Exchanges'];
                    foreach($exchangeList as $exlistAll) {
                        $currenciesModel = Currencies::find()->where( [ 'Name' => $exlistAll['FROMSYMBOL'] ] )->one();

                        if(empty($currenciesModel)) {
                            return print_r($exlistAll);
                        }
                        $models = Exchangelist::find()
                            ->where(['FROMSYMBOL' => $currenciesModel->id, //TODO: handle FROMSYMBOl
                            // 'MARKET' => $exlistAll['MARKET'],
                            'TOSYMBOL' => $exlistAll['TOSYMBOL'] ])
                            ->one();
                    
                        if($models==null){
                            $models = new Exchangelist();
                        } 
                        

                        // if($models->isNewRecord) {
                        //     $exchangemodel = new Exchanges();
                        // }else {
                            $exchangemodel = Exchanges::find()
                            ->where(['MARKET' =>  $exlistAll['MARKET'] ])
                            ->one();

                            if($exchangemodel==null){
                                $exchangemodel = new Exchanges();
                            } 
                        // }

                        // return print_r($exchangemodel->MARKET);exit;
                        $transaction1 = Exchanges::getDb()->beginTransaction();
                        try {
                            $exchangemodel->MARKET = $exlistAll['MARKET'];
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

                        $transaction = Exchangelist::getDb()->beginTransaction();
                        try {
                            $models->LiveCoinId = $coinId;                      
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
        return ('done');
    }

    //NOTE: commented not used now
    
    // public function curlToGetPriceApi($method, $symbol, $endpoint, $data = null)
    // {           
    //     \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        
    //     // $url = "https://min-api.cryptocompare.com/data/pricemultifull?fsyms=".$symbol."&tsyms=BTC,USD,EUR";
    //     $url = "https://min-api.cryptocompare.com/data/pricemultifull?fsyms=".$symbol."&tsyms=".$endpoint;
    //     $curl = curl_init();
    //     curl_setopt($curl, CURLOPT_URL, $url);
    //     curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);

    //     $result = curl_exec($curl);
        
    //     curl_close($curl);
    //     return $result;
        
    // }

    // public function actionFetchs(){

    //     \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
    //     $url = 'https://min-api.cryptocompare.com/data/all/coinlist';
    //     $result = $this->curlToRestApi('get', $url);
    //     $decode = json_decode($result, true);
    //     $length = count($decode['Data']);
    //     $sho= $decode['DefaultWatchlist']['CoinIs'];
    //     $coinContentList = [];
    //     $url_string = explode(',', $sho);
    //     $data = Currencies::find()->all();
    //     $listSymbols = [];
    //     $staticListSymbol = "USD,EUR,ETH";
        
        
    //     foreach($data as $datazz){  
    //         if(in_array($datazz['CoinId'], $url_string)){
    //             // $listSymbols[] = $datazz['Symbol'];
    //             $listSymbolz = $datazz['Symbol'];
                        
    //             $datas = json_decode($this->curlToGetPriceApi('get', $datazz->Symbol, $staticListSymbol));
    //             $fordata = $datas->RAW->$listSymbolz;
                
    //                 foreach($fordata as $ls){

    //                     $models = Coinlistinfo::find()
    //                     ->where(['CoinInputSymbol' => $datazz['Symbol'], 'TOSYMBOL' => $ls->TOSYMBOL ]) //TODO: check CoinInputSymbol
    //                     ->one();
    //                     if($models==null){
    //                         $models = new Coinlistinfo();
    //                     } 
    //                     $models->CoinlistId = $datazz['id'];
    //                     $models->LiveCoinId = $datazz['CoinId'];
    //                     // $models->CoinInputSymbol = $datazz['Symbol'];
    //                     $models->TYPE = $ls->TYPE;
    //                     $models->MARKET = $ls->MARKET;
    //                     // $models->FROMSYMBOL = $ls->FROMSYMBOL;
    //                     $models->TOSYMBOL = $ls->TOSYMBOL;
    //                     $models->FLAGS = $ls->FLAGS;
    //                     $models->PRICE = $ls->PRICE;
    //                     $models->LASTUPDATE = $ls->LASTUPDATE;
    //                     $models->LASTVOLUME = $ls->LASTVOLUME;
    //                     $models->LASTVOLUMETO = $ls->LASTVOLUMETO;
    //                     $models->LASTTRADEID = $ls->LASTTRADEID;
    //                     $models->VOLUMEDAY = $ls->VOLUMEDAY;
    //                     $models->VOLUMEDAYTO = $ls->VOLUMEDAYTO;
    //                     $models->VOLUME24HOUR = $ls->VOLUME24HOUR;
    //                     $models->VOLUME24HOURTO = $ls->VOLUME24HOURTO;
    //                     $models->OPENDAY = $ls->OPENDAY;
    //                     $models->HIGHDAY = $ls->HIGHDAY;
    //                     $models->LOWDAY = $ls->LOWDAY;
    //                     $models->OPEN24HOUR = $ls->OPEN24HOUR;
    //                     $models->HIGH24HOUR = $ls->HIGH24HOUR;
    //                     $models->LOW24HOUR = $ls->LOW24HOUR;
    //                     $models->LASTMARKET = $ls->LASTMARKET;
    //                     $models->CHANGE24HOUR = $ls->CHANGE24HOUR;
    //                     $models->CHANGEPCT24HOUR = $ls->CHANGEPCT24HOUR;
    //                     $models->CHANGEPCTDAY = $ls->CHANGEPCTDAY;
    //                     $models->SUPPLY = $ls->SUPPLY;
    //                     $models->MKTCAP = $ls->MKTCAP;
    //                     $models->TOTALVOLUME24H = $ls->TOTALVOLUME24H;
    //                     $models->TOTALVOLUME24HTO = $ls->TOTALVOLUME24HTO;                       
    //                     $models->save(); 
    //             }
    //         }
    //     }
        

        

    //     return $datas;
    // }
}
