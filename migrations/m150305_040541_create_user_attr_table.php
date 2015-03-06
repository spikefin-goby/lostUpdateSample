<?php

use yii\db\Schema;
use yii\db\Migration;

class m150305_040541_create_user_attr_table extends Migration
{
    public function up()
    {

        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            // http://stackoverflow.com/questions/766809/whats-the-difference-between-utf8-general-ci-and-utf8-unicode-ci
            $tableOptions = 'CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci ENGINE=InnoDB ROW_FORMAT=DYNAMIC';
        }

        $this->createTable('{{%user_status}}', [
            'user_id' => Schema::TYPE_INTEGER . ' PRIMARY KEY',
            'point' => Schema::TYPE_INTEGER . ' NOT NULL DEFAULT 0',
            'created_at' => Schema::TYPE_INTEGER . ' NOT NULL',
            'updated_at' => Schema::TYPE_INTEGER . ' NOT NULL',
        ], $tableOptions);

    }

    public function down()
    {
        echo "m150305_040541_create_user_attr_table cannot be reverted.\n";

        return false;
    }
}
