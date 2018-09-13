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

    /**decode
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

    public function actionStoreExchangeList() {
        $cryptoCoins = new CryptoCoins();
        $exchanges = $cryptoCoins->getExchanges();
        foreach($exchanges as $marketName => $checkList) {
            $exchangemodel = $this->saveMarkets($marketName);
            foreach($checkList as $key => $value) {
                $currenciesModel = Currencies::find()->where( [ 'Name' => $key ] )->one();

                if($currenciesModel==null) {
                    $currenciesModel = new Currencies();
                }
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
