<?php

namespace app\models\forms;

use app\models\game\GameSubscription;

class GameSubscriptionForm extends GameSubscription implements Form
{
    public $id;
    public $user_id;
    public $game_id;
    public $is_active;

    public function rules(): array
    {
        return [
            [['user_id', 'game_id'], 'required'],
            [['user_id', 'game_id'], 'integer'],
            [['is_active'], 'boolean'],
            [['user_id', 'game_id'], 'unique', 'targetAttribute' => ['user_id', 'game_id']],
            [['user_id'], 'exist', 'skipOnError' => true, 'targetClass' => \app\models\user\User::class, 'targetAttribute' => ['user_id' => 'id']],
            [['game_id'], 'exist', 'skipOnError' => true, 'targetClass' => \app\models\game\Game::class, 'targetAttribute' => ['game_id' => 'id']],
        ];
    }

    public function getSafeAttributes(): array
    {
        return [
            'user_id' => $this->user_id,
            'game_id' => $this->game_id,
            'is_active' => $this->is_active,
        ];
    }
}
