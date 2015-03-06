<?php
/**
 * Created by PhpStorm.
 * User: hirose-s
 * Date: 2015/03/05
 * Time: 13:41
 */

namespace app\models;


use app\models\exceptions\PointUnderFlowException;

class PointPayment extends Payment
{

    private $_userStatus = null;


    /**
     * ポイント支払い処理及び履歴登録
     *
     * @inheritdoc
     * @throws PointUnderFlowException
     */
    public function charge()
    {
        if (!$this->isEnough()) {
            throw new PointUnderFlowException;
        }

        $userStatus = $this->getUserStatus();
        $userStatus->point -= $this->cost;
        $userStatus->save();

        // 履歴登録
        UserPointHistory::register($this->user->getId(), $this->cost * -1);
    }

    /**
     * 支払い能力があるかを判定
     *
     * @return boolean
     */
    public function isEnough()
    {
        return $this->getUserStatus()->isEnoughPoint($this->cost);
    }

    /**
     * @return UserStatus|null
     */
    private function getUserStatus()
    {
        if ($this->_userStatus === null) {
            $this->_userStatus = $this->user->getUserStatus();
        }

        return $this->_userStatus;
    }
}
