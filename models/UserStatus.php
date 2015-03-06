<?php

namespace app\models;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "user_status".
 *
 * @property integer $user_id
 * @property integer $point
 * @property integer $created_at
 * @property integer $updated_at
 */
class UserStatus extends ActiveRecord
{

    const DEFAULT_POINT = 100;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'user_status';
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
            [['user_id', 'point', 'created_at', 'updated_at'], 'integer'],
            ['point', 'default', 'value' => self::DEFAULT_POINT]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'user_id' => 'User ID',
            'point' => 'Point',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }

    public function getUser()
    {
        return $this->hasOne(User::className(), ['id' => 'user_id']);
    }

    public function create()
    {
        $userPointHistory = new UserPointHistory();
        $userPointHistory->user_id = $this->user_id;
        $userPointHistory->change_point = self::DEFAULT_POINT;
        $userPointHistory->save();
        $this->save();
    }

    public function isEnoughPoint($point)
    {
        return ($this->point - $point >= 0);
    }

}
