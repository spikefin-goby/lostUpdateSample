<?php
/**
 * Created by PhpStorm.
 * User: hirose-s
 * Date: 2015/03/05
 * Time: 13:41
 */

namespace app\models;


use app\models\exceptions\PointUnderFlowException;

class PointPaymentBugFix extends Payment
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

        /**
         * 以下を変更
         *
         * incrementalに減算
         * UPDATE `user_status` SET `point`=`point`+-1 WHERE `user_id`= :id;
         * ※ちなみに、この構文に対応しているフレームワークは結構少ない
         *
         * [問題点]
         * ・フレームワーク側の値と、永続化されている値が相違する可能性がある
         * ・例）支払い能力がないのに、支払えてしまう
         * @see yii\db\BaseActiveRecord::updateCounters
         * ・対策としては、SQLを撮り直す。。（これは面倒 and 負荷(qps))
         *
         */
        $userStatus->updateCounters(['point' => $this->cost * -1]);
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
