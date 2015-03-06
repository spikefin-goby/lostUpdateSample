<?php

namespace app\models;

use Yii;
use yii\db\ActiveRecord;
use yii\behaviors\TimestampBehavior;

/**
 * This is the model class for table "user_point_history".
 *
 * @property integer $id
 * @property integer $user_id
 * @property integer $change_point
 * @property integer $created_at
 * @property integer $updated_at
 */
class UserPointHistory extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'user_point_history';
    }

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            TimestampBehavior::className(),
        ];
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id', 'change_point', 'created_at', 'updated_at'], 'integer'],
            [['change_point'], 'required']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'user_id' => 'User ID',
            'change_point' => 'Change Point',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }

    /**
     * 履歴を登録する
     *
     * @param int $user_id
     * @param int $change_point
     */
    public static function register($user_id, $change_point)
    {
        $history = new self();
        $history->user_id = $user_id;
        $history->change_point = $change_point;
        $history->save();
    }
}
