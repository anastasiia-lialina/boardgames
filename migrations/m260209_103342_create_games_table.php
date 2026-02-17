<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%games}}`.
 */
class m260209_103342_create_games_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp(): void
    {
        $this->createTable('{{%games}}', [
            'id' => $this->primaryKey(),
            'title' => $this->string(200)->notNull()->comment('Название'),
            'description' => $this->text()->comment('Описание'),
            'players_min' => $this->integer()->notNull()->comment('Минимальное количество игроков'),
            'players_max' => $this->integer()->notNull()->comment('Максимальное количество игроков'),
            'duration_min' => $this->integer()->notNull()->comment('Минимальное время игры(мин.)'),
            'complexity' => $this->decimal(2, 1)->notNull()->comment('Сложность'),
            'year' => $this->integer()->notNull()->comment('Год выпуска'),
            'created_at' => $this->dateTime()->notNull()->defaultExpression('CURRENT_TIMESTAMP')->comment('Дата добавления'),
        ]);
        $this->addCommentOnTable('{{%games}}', 'Настольные игры');

        $this->createIndex('idx_games_players', '{{%games}}', ['players_min', 'players_max']);
        $this->createIndex('idx_games_complexity', '{{%games}}', 'complexity');
        $this->createIndex('idx_games_year', '{{%games}}', 'year');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown(): void
    {
        $this->dropIndex('idx_games_players', '{{%games}}');
        $this->dropIndex('idx_games_complexity', '{{%games}}');
        $this->dropIndex('idx_games_year', '{{%games}}');
        $this->dropTable('{{%games}}');
    }
}
