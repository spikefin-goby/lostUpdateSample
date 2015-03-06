<?php
namespace app\models\services;

use app\helpers\Math;
use app\models\PointPayment;
use app\models\User;


class GachaService {

    const GACHA_POINT_PER_ONCE = 10;
    protected $user;

    public function __construct(User $user)
    {
        $this->user = $user;
    }

    /**
     * レアリティ抽選
     *
     * @return string
     */
    protected function draw()
    {
        $rateSet = [
            'N' => 80,
            'R' => 10,
            'SR' => 5,
            'SSR' => 4,
            'UR' => 1
        ];

        return Math::dice($rateSet);
    }


    public function play()
    {
        // 支払いクラスを生成
        /** @var PointPayment $payment */
        $payment = PaymentService::get($this->user, 'point', self::GACHA_POINT_PER_ONCE);

        $connection = \Yii::$app->db;
        $transaction = $connection->beginTransaction();
        try {
            // 抽選 & カード付与
            $rarity = $this->draw();
            $this->user->addCard($rarity);

            // ポイント減算
            $payment->charge();

            $transaction->commit();
        } catch(\Exception $e) {
            $transaction->rollBack();
            throw $e;
        }

        return $rarity;
    }


    public function playFix1()
    {
        // [相違点]支払いクラスを生成
        /** @var PointPayment $payment */
        $payment = PaymentService::get($this->user, 'point-bug-fix', self::GACHA_POINT_PER_ONCE);

        $connection = \Yii::$app->db;
        $transaction = $connection->beginTransaction();
        try {
            // 抽選 & カード付与
            $rarity = $this->draw();
            $this->user->addCard($rarity);

            // ポイント減算
            $payment->charge();

            $transaction->commit();
        } catch(\Exception $e) {
            $transaction->rollBack();
            throw $e;
        }

        return $rarity;
    }



    public function playFix2()
    {
        // [相違点]支払いクラスを生成
        /** @var PointPayment $payment */
        $payment = PaymentService::get($this->user, 'point-bug-fix', self::GACHA_POINT_PER_ONCE);

        $connection = \Yii::$app->db;
        $transaction = $connection->beginTransaction();
        try {

            /**
             * [相違点] 悲観的ロック
             *
             * [ポイント]
             * ・ユーザの何かが変動する場合は、かならずこのメソッドを経由することで、並列実行に対処する
             * ・送金システムやトレードのような、自分と相手が登場する場合は、ロックを取る順番に気をつける
             *   ・自分のロックを取って、相手のロックを取ってしまうと、デッドロックが発生する
             *   ・A -> B, B -> A
             *   ・Aは、Aをロック、次にBをロックしようとする
             *   ・Bは、Bをロック、次にAをロックしようとする
             *   ・Aは、Bをロックしようとするけど、Bによってロックされている
             *   ・Bは、Aをロックしようとするけど、Aによってロックされている
             *   ・[解決策] ロックを取る順序に規則性を持たせる[IDの若い順とか]
             *   ・Aは、Aをロック、次に B をロックしようとする
             *   ・Bは、Aをロックしようとするけど、できないので待ち
             *   ・Aは、Bをロック＆処理完了
             *   ・Bは、Aをロック＆処理完了
             */
            $this->user->lock();

            // 抽選 & カード付与
            $rarity = $this->draw();
            $this->user->addCard($rarity);

            // ポイント減算
            $payment->charge();

            $transaction->commit();
        } catch(\Exception $e) {
            $transaction->rollBack();
            throw $e;
        }

        return $rarity;
    }
}
