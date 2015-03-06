<?php
/**
 * Created by PhpStorm.
 * User: hirose-s
 * Date: 2015/03/06
 * Time: 15:09
 */

namespace app\helpers;


class Math {

    /**
     * サイコロを使って、どの事象が起こるか決める
     *
     * NOTE: 全ての確率が 0 のとき、最後の事象が起こる
     *
     * @param array $list
     * @return string 与えられた事象リストのうち、起こった事象のキーを返す
     */
    public static function dice(array $list)
    {
        $weights = array_values($list);
        $max = max(1, array_sum($weights) * 1000);
        $n = mt_rand(1, $max);

        $key = null;
        foreach ($list as $k => $v) {
            $key = $k;
            $v *= 1000;
            if ($v >= $n) break;
            $n -= $v;
        }
        return $key;
    }
}
