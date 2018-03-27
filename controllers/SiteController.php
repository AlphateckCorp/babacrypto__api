<?php

namespace app\controllers;

use Yii;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\web\Response;
use yii\filters\VerbFilter;
use app\models\LoginForm;
use app\models\ContactForm;
use app\models\Coinlist;
use app\models\Coinlistinfo;
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

    public function curlToRestApi($method, $url, $data = null)
    {

        $curl = curl_init();

        // switch $method
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

    public function actionStoreCoins(){
        
        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
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
                      $model->save(false); 
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

    public function actionFetchs(){

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
                $fordata = $datas->RAW->$listSymbolz;
                
                    
                    foreach($fordata as $ls){

                        $models = Coinlistinfo::find()
                        ->where(['CoinInputSymbol' => $datazz['Symbol'], 'TOSYMBOL' => $ls->TOSYMBOL ])
                        ->one();
                        if($models==null){
                            $models = new Coinlistinfo();
                        } 
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
}
