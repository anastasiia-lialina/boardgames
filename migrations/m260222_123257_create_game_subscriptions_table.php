<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%game_subscriptions}}`.
 */
class m260222_123257_create_game_subscriptions_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%game_subscriptions}}', [
            'id' => $this->primaryKey(),
            'user_id' => $this->integer()->notNull(),
            'game_id' => $this->integer()->notNull(),
            'created_at' => $this->dateTime()->notNull()->defaultExpression('CURRENT_TIMESTAMP'),
            'is_active' => $this->boolean()->notNull()->defaultValue(true),
        ]);

        // Внешние ключи
        $this->addForeignKey(
            'fk-game_subscriptions-user_id',
            '{{%game_subscriptions}}',
            'user_id',
            '{{%user}}',
            'id',
            'CASCADE',
            'CASCADE'
        );
        $this->addForeignKey(
            'fk-game_subscriptions-game_id',
            '{{%game_subscriptions}}',
            'game_id',
            '{{%games}}',
            'id',
            'CASCADE',
            'CASCADE'
        );

        $this->createIndex('idx-game_subscriptions-unique', '{{%game_subscriptions}}', ['user_id', 'game_id'], true);

        $this->createIndex('idx-game_subscriptions-user_id', '{{%game_subscriptions}}', 'user_id');
        $this->createIndex('idx-game_subscriptions-game_id', '{{%game_subscriptions}}', 'game_id');
        $this->createIndex('idx-game_subscriptions-is_active', '{{%game_subscriptions}}', 'is_active');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropForeignKey('fk-game_subscriptions-user_id', '{{%game_subscriptions}}');
        $this->dropForeignKey('fk-game_subscriptions-game_id', '{{%game_subscriptions}}');
        $this->dropTable('{{%game_subscriptions}}');
    }
}
