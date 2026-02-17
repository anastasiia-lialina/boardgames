<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%game_sessions}}`.
 */
class m260209_112321_create_game_sessions_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp(): void
    {
        $this->createTable('{{%game_sessions}}', [
            'id' => $this->primaryKey(),
            'game_id' => $this->integer()->notNull()->comment('Ид игры'),
            'organizer_id' => $this->integer()->notNull()->comment('Ид организатора'),
            'scheduled_at' => $this->dateTime()->notNull()->comment('Дата и время проведения'),
            'max_participants' => $this->integer()->notNull()->comment('Максимальное количество участников'),
            'status' => $this->string(20)->notNull()->defaultValue('planned')->comment('Статус сессии'),
            'created_at' => $this->dateTime()->notNull()->defaultExpression('CURRENT_TIMESTAMP')->comment('Дата добавления'),
        ]);
        $this->addCommentOnTable('{{%game_sessions}}', 'Игровые встречи');


        $this->addForeignKey(
            'fk-sessions-game_id',
            '{{%game_sessions}}',
            'game_id',
            '{{%games}}',
            'id',
            'CASCADE',
            'CASCADE'
        );

        $this->createIndex('idx_game_sessions_game_id', '{{%game_sessions}}', 'game_id');
        $this->createIndex('idx_game_sessions_scheduled_at', '{{%game_sessions}}', 'scheduled_at');
        $this->createIndex('idx_game_sessions_status', '{{%game_sessions}}', 'status');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown(): void
    {
        $this->dropForeignKey('fk-sessions-game_id', '{{%game_sessions}}');
        $this->dropIndex('idx_game_sessions_game_id', '{{%game_sessions}}');
        $this->dropIndex('idx_game_sessions_scheduled_at', '{{%game_sessions}}');
        $this->dropIndex('idx_game_sessions_status', '{{%game_sessions}}');
        $this->dropTable('{{%game_sessions}}');
    }
}
