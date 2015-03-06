<?php
/**
 * Created by PhpStorm.
 * User: hirose-s
 * Date: 2015/03/05
 * Time: 13:41
 */

namespace app\models\services;

use app\models\User;

class UserService {

    protected $user;

    public function __construct(User $user)
    {
        $this->user = $user;
    }


    public function register()
    {
        $connection = \Yii::$app->db;
        $transaction = $connection->beginTransaction();
        try {
            $this->user->create();
            $transaction->commit();
        } catch(\Exception $e) {
            $transaction->rollBack();
            throw $e;
        }
    }
}
