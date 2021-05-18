<?php

use yii\db\Migration;

/**
 * Class m210507_083319_mentor_create_skill_table
 */
class m210507_083319_mentor_create_skill_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }

        $this->createTable('{{%mentor_skill}}', [
            'id' => $this->primaryKey(),
            'name' => $this->string(),
            'sort' => $this->integer(),
            'created_at' => 'datetime DEFAULT NOW()',
            'updated_at' => 'datetime ON UPDATE NOW()',
        ], $tableOptions);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%mentor_skill}}');
    }
}
