<?php
/**
 * Created by PhpStorm.
 * User: hirose-s
 * Date: 2015/03/05
 * Time: 13:41
 */

namespace app\models\services;

use app\models\Payment;
use app\models\PointPayment;
use app\models\PointPaymentBugFix;
use app\models\User;

class PaymentService
{

    /**
     * payment_typeに応じた支払いクラスを返します
     *
     * @param User $user
     * @param string $payment_type
     * @param int $cost
     * @return Payment
     */
    public static function get(User $user, $payment_type, $cost)
    {

        switch ($payment_type) {
            case 'point':
                return new PointPayment($user, $cost);
            case 'point-bug-fix':
                return new PointPaymentBugFix($user, $cost);
            default:
                throw new \InvalidArgumentException(sprintf('invalid payment_type: [%s]', $payment_type));

        }
    }
}
