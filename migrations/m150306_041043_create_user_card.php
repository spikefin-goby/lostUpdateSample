<?php

use yii\db\Schema;
use yii\db\Migration;

class m150306_041043_create_user_card extends Migration
{
    public function up()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            // http://stackoverflow.com/questions/766809/whats-the-difference-between-utf8-general-ci-and-utf8-unicode-ci
            $tableOptions = 'CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci ENGINE=InnoDB ROW_FORMAT=DYNAMIC';
        }

        $this->createTable('{{%user_card}}', [
            'id' => Schema::TYPE_BIGPK ,
            'user_id' => Schema::TYPE_INTEGER ,
            'card_rarity' => Schema::TYPE_STRING . '(32) NOT NULL',
            'created_at' => Schema::TYPE_INTEGER . ' NOT NULL',
            'updated_at' => Schema::TYPE_INTEGER . ' NOT NULL'
        ], $tableOptions);

        $this->createIndex('index_user_card', '{{%user_card}}', 'user_id', false);
    }

    public function down()
    {
        echo "m150306_041043_create_user_card cannot be reverted.\n";

        return false;
    }
}
