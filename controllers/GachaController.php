<?php

namespace app\controllers;

use app\models\services\GachaService;
use app\models\User;
use Yii;
use yii\base\Exception;

class GachaController extends BaseApiController
{

    public function actionUserInfo()
    {
        /** @var User $user */
        $user = Yii::$app->getUser()->getIdentity();
        return [
            'cards' => $user->getUserCards(),
        ];
    }


    /**
     * ガチャを引く
     * [ER図]
     *
     * [仕様]
     *  1. ガチャは1回 10point を使用する
     *  2. point残高が無ければ、引けない
     *  3. [N,R,SR,SSR,UR]のうちから抽選（今回カードのオブジェクトは作らず、文字列で。。）
     *
     * [振る舞い]
     *  1. ポイント残高を調べる
     *  → 残高なければ例外
     *  2. カードレアリティ抽選
     *  3. ユーザカード所持テーブルに登録
     *  4. ポイントを減算し、ポイント履歴テーブルに登録
     *  5. 引いたレアリティと、現在所持しているカードをJSONでレスポンス
     *
     * [確認したい事]
     *  ・ 何も考えずに実装すると、ロストアップデートが起きる事を確認
     *  ・ ロストアップデート対策への道
     *
     */


    /**
     * ガチャを引く -- PART 1[bug]
     *
     */
    public function actionPlay()
    {
        /** @var User $user */
        $user = Yii::$app->getUser()->getIdentity();

        $service = new GachaService($user);
        try {
            $rarity = $service->play();
            $cards = $user->getUserCards();
        } catch (Exception $e) {
            throw $e;
        }

        return [
            'hit_card' => $rarity,
            'cards' => $cards,
        ];
    }

    /**
     * ガチャを引く -- PART 2
     *
     */
    public function actionPlayFix1()
    {
        /** @var User $user */
        $user = Yii::$app->getUser()->getIdentity();

        $service = new GachaService($user);
        try {
            $rarity = $service->playFix1();
            $cards = $user->getUserCards();
        } catch (Exception $e) {
            throw $e;
        }

        return [
            'hit_card' => $rarity,
            'cards' => $cards,
        ];
    }

    /**
     * ガチャを引く -- PART 3
     *
     */
    public function actionPlayFix2()
    {
        /** @var User $user */
        $user = Yii::$app->getUser()->getIdentity();

        $service = new GachaService($user);
        try {
            $rarity = $service->playFix2();
            $cards = $user->getUserCards();
        } catch (Exception $e) {
            throw $e;
        }

        return [
            'hit_card' => $rarity,
            'cards' => $cards,
        ];
    }
}
