<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%game_session_log}}`.
 */
class m260216_120914_create_game_session_log_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('game_session_log', [
            'id' => $this->primaryKey(),
            'session_id' => $this->integer()->notNull(),
            'old_status' => $this->string(),
            'new_status' => $this->string(),
            'changed_at' => $this->dateTime()->notNull()->defaultExpression('CURRENT_TIMESTAMP'),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%game_session_log}}');
    }
}
