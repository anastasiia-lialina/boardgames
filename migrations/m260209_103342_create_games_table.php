<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%game}}`.
 */
class m260209_103342_create_game_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp(): void
    {
        $this->createTable('{{%game}}', [
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
        $this->addCommentOnTable('{{%game}}','Настольные игры');

        $this->createIndex('idx_game_players', '{{%game}}', ['players_min', 'players_max']);
        $this->createIndex('idx_game_complexity', '{{%game}}', 'complexity');
        $this->createIndex('idx_game_year', '{{%game}}', 'year');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown(): void
    {
        $this->dropIndex('idx_game_players', '{{%game}}');
        $this->dropIndex('idx_game_complexity', '{{%game}}');
        $this->dropIndex('idx_game_year', '{{%game}}');
        $this->dropTable('{{%game}}');
    }
}
