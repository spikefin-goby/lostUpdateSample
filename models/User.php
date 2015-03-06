<?php

namespace app\models;

use app\helpers\Password;
use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use yii\filters\RateLimitInterface;
use yii\log\Logger;
use yii\web\IdentityInterface;


/**
 * This is the model class for table "user".
 *
 * @property integer $id
 * @property string $username
 * @property string $auth_key
 * @property string $password_hash
 * @property string $password_reset_token
 * @property string $access_token
 * @property string $email
 * @property integer $status
 * @property integer $created_at
 * @property integer $updated_at
 */
class User extends ActiveRecord implements IdentityInterface
{
    const STATUS_DELETED = 0;
    const STATUS_ACTIVE = 10;

    /** @var string Plain password. Used for model validation. */
    public $password;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'user';
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
            [['username'], 'required'],
            [['status', 'created_at', 'updated_at'], 'integer'],
            [['username', 'password_hash', 'password_reset_token', 'access_token', 'email'], 'string', 'max' => 255],
            [['auth_key'], 'string', 'max' => 32],
            [['access_token'], 'unique'],
            ['status', 'default', 'value' => self::STATUS_ACTIVE],
            ['status', 'in', 'range' => [self::STATUS_ACTIVE, self::STATUS_DELETED]],
        ];
    }

    public function beforeSave($insert)
    {
        if (parent::beforeSave($insert)) {
            if ($this->isNewRecord) {
                $this->generateAuthKey();
                $this->generateAccessToken();
                $this->generatePasswordResetToken();
            }

            if ($this->password) {
                $this->setPassword($this->password);
            }

            return true;
        }
        return false;
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'username' => 'Username',
            'auth_key' => 'Auth Key',
            'password_hash' => 'Password Hash',
            'password_reset_token' => 'Password Reset Token',
            'access_token' => 'Access Token',
            'email' => 'Email',
            'status' => 'Status',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }

    public static function findIdentity($id)
    {
        return static::findOne(['id' => $id, 'status' => self::STATUS_ACTIVE]);
    }

    public static function findIdentityByAccessToken($token, $type = null)
    {
        return static::findOne(['access_token' => $token, 'status' => self::STATUS_ACTIVE]);
    }

    public static function findByUsername($name)
    {
        return static::findOne(['username' => $name, 'status' => self::STATUS_ACTIVE]);
    }


    public function getId()
    {
        return $this->getPrimaryKey();
    }

    public function getAuthKey()
    {
        return $this->auth_key;
    }

    public function validateAuthKey($authKey)
    {
        return $this->getAuthKey() === $authKey;
    }

    /**
     * Generates "remember me" authentication key
     */
    public function generateAuthKey()
    {
        $this->auth_key = Yii::$app->security->generateRandomString();
    }

    /**
     * Generates new password reset token
     */
    public function generatePasswordResetToken()
    {
        $this->password_reset_token = Yii::$app->security->generateRandomString() . '_' . time();
    }

    /**
     * Removes password reset token
     */
    public function removePasswordResetToken()
    {
        $this->password_reset_token = null;
    }

    /**
     * Validates password
     *
     * @param string $password password to validate
     * @return boolean if password provided is valid for current user
     */
    public function validatePassword($password)
    {
        return Yii::$app->security->validatePassword($password, $this->password_hash);
    }
    /**
     * Generates password hash from password and sets it to the model
     *
     * @param string $password
     */
    public function setPassword($password)
    {
        $this->password_hash = Yii::$app->security->generatePasswordHash($password);
    }

    /**
     * アクセストークン文字列を生成する
     */
    public function generateAccessToken()
    {
        $this->access_token = Yii::$app->security->generateRandomString() . '_' . time();
    }


    /**
     * @return UserStatus
     */
    public function getUserStatus()
    {
        return $this->hasOne(UserStatus::className(), ['user_id' => 'id'])->one();
    }

    /**
     * @return UserCard[]
     */
    public function getUserCards()
    {
        return $this->hasMany(UserCard::className(), ['user_id' => 'id'])->all();
    }

    /**
     * 新規レコード作成
     *
     * @return bool
     */
    public function create()
    {
        if ($this->getIsNewRecord() == false) {
            throw new \RuntimeException('Calling "' . __CLASS__ . '::' . __METHOD__ . '" on existing user');
        }

        if ($this->password == null) {
            $this->password = Password::generate(8);
        }

        if ($this->save()) {

            $userStatus = new UserStatus();
            $userStatus->user_id = $this->getId();
            $userStatus->create();

            \Yii::getLogger()->log('User has been created', Logger::LEVEL_INFO);
            return true;
        }

        \Yii::getLogger()->log('An error occurred while creating user account', Logger::LEVEL_ERROR);
        return false;
    }


    public function addCard($rarity)
    {
        $card = new UserCard();
        $card->user_id = $this->getId();
        $card->card_rarity = $rarity;
        $card->save();
    }


    /**
     * user_idで行ロックを取得
     *
     */
    public function lock()
    {
        $db = static::getDb();
        if ($db->getTransaction() === null || !$db->getTransaction()->getIsActive()) {
            throw new \RuntimeException('this method need to call with transaction.');
        }

        $command = $db->createCommand('SELECT * FROM ' . self::tableName() . ' WHERE id = :id FOR UPDATE', ['id' => $this->getPrimaryKey()]);
        $command->queryOne();
    }
}
