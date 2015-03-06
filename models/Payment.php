<?php
/**
 * Created by PhpStorm.
 * User: hirose-s
 * Date: 2015/03/05
 * Time: 13:41
 */

namespace app\models;


abstract class Payment
{
    protected $user;
    protected $cost;

    public function __construct(User $user, $cost)
    {
        $this->user = $user;
        $this->cost = $cost;
    }

    /**
     * 支払い処理を行います
     *
     * @return  void
     */
    public abstract function charge();

    /**
     * 支払い能力があるかを判定
     *
     * @return boolean
     */
    public abstract function isEnough();

}
