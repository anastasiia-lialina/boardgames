<?php

namespace app\models\forms;

use app\models\game\Game;
use yii\base\Model;
use app\models\game\GameSession;
use DateTime;
use Yii;

class GameSessionForm extends GameSession
{
    public $id;
    public $game_id;
    public $scheduled_at;
    public $max_participants;
    public $status;

    public function rules(): array
    {
        return [
            [['game_id', 'scheduled_at', 'max_participants'], 'required'],

            [['game_id', 'organizer_id', 'max_participants'], 'integer'],

            ['scheduled_at', 'datetime', 'format' => 'php:Y-m-d H:i:s'],

            ['status', 'default', 'value' => self::STATUS_PLANNED],

            ['max_participants', 'integer', 'min' => self::MIN_PARTICIPANTS, 'max' => self::MAX_PARTICIPANTS],

            ['status', 'in', 'range' => self::allowedStatuses()],

            ['scheduled_at', 'validateScheduledAt'],

            [['game_id'], 'exist', 'skipOnError' => true, 'targetClass' => Game::class, 'targetAttribute' => ['game_id' => 'id']],
        ];
    }

    public function validateScheduledAt($attribute): void
    {
        $scheduled = new DateTime($this->$attribute);
        $now = new DateTime();

        switch (true) {
            case $this->status === self::STATUS_PLANNED && $scheduled <= $now:
                    $this->addError($attribute, Yii::t('app', 'The scheduled date must be in the future.'));
                    return;

            case $this->status === self::STATUS_ACTIVE && $scheduled > $now:
                    $this->addError($attribute, Yii::t('app', 'An active session must have already started.'));
                    return;

            case $this->status === self::STATUS_COMPLETED && $scheduled >= $now:
                    $this->addError($attribute, Yii::t('app', 'A completed session must be in the past.'));
                    return;
        };
    }
}
