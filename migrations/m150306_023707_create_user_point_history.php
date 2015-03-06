<?php

use yii\db\Schema;
use yii\db\Migration;

class m150306_023707_create_user_point_history extends Migration
{
    public function up()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            // http://stackoverflow.com/questions/766809/whats-the-difference-between-utf8-general-ci-and-utf8-unicode-ci
            $tableOptions = 'CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci ENGINE=InnoDB ROW_FORMAT=DYNAMIC';
        }

        $this->createTable('{{%user_point_history}}', [
            'id' => Schema::TYPE_BIGPK ,
            'user_id' => Schema::TYPE_INTEGER ,
            'change_point' => Schema::TYPE_INTEGER . ' NOT NULL DEFAULT 0',
            'created_at' => Schema::TYPE_INTEGER . ' NOT NULL',
            'updated_at' => Schema::TYPE_INTEGER . ' NOT NULL'
        ], $tableOptions);

        $this->createIndex('index_user_point_history', '{{%user_point_history}}', 'user_id', false);
        $this->createIndex('index_point_history', '{{%user_point_history}}', ['created_at', 'user_id'], false);
    }

    public function down()
    {
        echo "m150306_023707_create_user_point_history cannot be reverted.\n";

        return false;
    }
}
