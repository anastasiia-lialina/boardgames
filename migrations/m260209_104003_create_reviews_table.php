<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%reviews}}`.
 */
class m260209_104003_create_reviews_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp(): void
    {
        $this->createTable('{{%reviews}}', [
            'id' => $this->primaryKey(),
            'game_id' => $this->integer()->notNull()->comment('Ид игры'),
            'user_id' => $this->integer()->notNull()->comment('Ид пользователя оставившего отзыв'),
            'rating' => $this->integer()->notNull()->comment('Рейтинг (1-5)'),
            'comment' => $this->text()->comment('Отзыв'),
            'is_approved' => $this->boolean()->notNull()->defaultValue(false)->comment('Статус модерации'),
            'created_at' => $this->dateTime()->notNull()->defaultExpression('CURRENT_TIMESTAMP')->comment('Дата добавления'),
        ]);
        $this->addCommentOnTable('{{%reviews}}','Отзывы');

        // Внешние ключи
        $this->addForeignKey(
            'fk-reviews-game_id',
            '{{%reviews}}',
            'game_id',
            '{{%games}}',
            'id',
            'CASCADE',
            'CASCADE'
        );

        $this->createIndex('idx_reviews_game_id', '{{%reviews}}', 'game_id');
        $this->createIndex('idx_reviews_user_id', '{{%reviews}}', 'user_id');
        $this->createIndex('idx_reviews_approved', '{{%reviews}}', 'is_approved');

        $this->createIndex('unique_reviews_user_game', '{{%reviews}}', ['user_id', 'game_id'], true);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown(): void
    {
        $this->dropIndex('idx_reviews_game_id', '{{%reviews}}');
        $this->dropIndex('idx_reviews_user_id', '{{%reviews}}');
        $this->dropIndex('idx_reviews_approved', '{{%reviews}}');
        $this->dropIndex('unique_reviews_user_game', '{{%reviews}}');
        $this->dropForeignKey('fk-reviews-game_id', '{{%reviews}}');
        $this->dropTable('{{%reviews}}');
    }
}
