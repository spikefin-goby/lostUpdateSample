<?php

namespace app\controllers;

use app\helpers\Password;
use app\models\services\UserService;
use app\models\User;
use Yii;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\filters\VerbFilter;
use app\models\LoginForm;
use app\models\ContactForm;

class SiteController extends Controller
{
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

    public function actionIndex()
    {
        return $this->render('index');
    }

    public function actionLogin()
    {

        Yii::info('testset');
        if (!\Yii::$app->user->isGuest) {
            return $this->goHome();
        }

        $model = new LoginForm();
        if ($model->load(Yii::$app->request->post()) && $model->login()) {
            return $this->goBack();
        } else {
            return $this->render('login', [
                'model' => $model,
            ]);
        }
    }

    public function actionLogout()
    {
        Yii::$app->user->logout();

        return $this->goHome();
    }

    public function actionContact()
    {
        $model = new ContactForm();
        if ($model->load(Yii::$app->request->post()) && $model->contact(Yii::$app->params['adminEmail'])) {
            Yii::$app->session->setFlash('contactFormSubmitted');

            return $this->refresh();
        } else {
            return $this->render('contact', [
                'model' => $model,
            ]);
        }
    }

    public function actionAbout()
    {
        return $this->render('about');
    }

    public function actionSay($message = 'Hello')
    {
        return $this->render('say', ['message' => $message]);
    }

    public function actionRegister()
    {
        $newUser = new User();
        $newUser->username = 'test' . Password::generate(10);
        $newUser->password = 1111;

        $userService = new UserService($newUser);
        $userService->register();

        return $this->render('say', ['message' => 'ok']);
    }


    public function actionBug()
    {
        $connection = \Yii::$app->db;
        $transaction = $connection->beginTransaction();
        try {
            /** @var User $user */
            $user = User::findOne(2500);
            $userStatus = $user->getUserStatus();
            $userStatus->point -= 1;
            usleep(rand(100000, 1000000));
            $userStatus->save();

            $transaction->commit();
        } catch(\Exception $e) {
            $transaction->rollBack();
            throw $e;
        }

        return $this->render('say', ['message' => 'ok']);
    }

    public function actionBugFix1()
    {

        $connection = \Yii::$app->db;
        $transaction = $connection->beginTransaction();
        try {
            /** @var User $user */
            $user = User::findOne(2500);
            $userStatus = $user->getUserStatus();
            usleep(rand(100000, 1000000));

            /**
             * ここを変える[フレームワークで対応してくれているのはかなり少ない]
             * UPDATE `user_status` SET `point`=`point`+-1 WHERE `user_id`=2500;
             * しかしながら、これでもフレームワークがdirty readをしている可能性がある
             * その対策はbugfix2
             *
             * @see yii\db\BaseActiveRecord::updateCounters
             */
            $userStatus->updateCounters(['point' => -1]);
            $viewPoint = $userStatus->point;

            $transaction->commit();
        } catch(\Exception $e) {
            $viewPoint = 0;
            $transaction->rollBack();
        }

        return $this->render('say', ['message' => $viewPoint]);
    }

    public function actionBugFix2()
    {

        $connection = \Yii::$app->db;
        $transaction = $connection->beginTransaction();
        try {
            /** @var User $user */
            $user = User::findOne(2500);
            $user->lock();
            $userStatus = $user->getUserStatus();
            usleep(rand(100000, 1000000));

            $userStatus->updateCounters(['point' => -1]);
            $viewPoint = $userStatus->point;

            $transaction->commit();
        } catch(\Exception $e) {
            $viewPoint = 0;
            $transaction->rollBack();
        }

        return $this->render('say', ['message' => $viewPoint]);
    }
}
