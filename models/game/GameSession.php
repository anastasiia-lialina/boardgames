<?php

namespace app\models\game;

use app\behaviors\StatusLogBehavior;
use app\models\user\User;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "game_sessions".
 *
 * @property int $id
 * @property int $game_id Ид игры
 * @property int $organizer_id Ид организатора
 * @property string $scheduled_at Дата и время проведения
 * @property int $max_participants Максимальное количество участников
 * @property string $status Статус сессии
 * @property string $created_at Дата добавления
 * @property Game $game
 * @property null|string $statusLabel
 */
class GameSession extends ActiveRecord
{
    public const STATUS_PLANNED = 'planned';
    public const STATUS_ACTIVE = 'active';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_CANCELLED = 'cancelled';

    public const MIN_PARTICIPANTS = 2;
    public const MAX_PARTICIPANTS = 20;

    public const EVENT_SESSION_CREATED = 'sessionCreated';
    public const EVENT_STATUS_CHANGED = 'sessionStatusChanged';

    public static function tableName(): string
    {
        return '{{%game_sessions}}';
    }

    public function rules()
    {
        return [
            [['game_id', 'organizer_id', 'scheduled_at', 'max_participants'], 'required'],
            [['game_id', 'organizer_id', 'max_participants'], 'integer'],
            ['status', 'in', 'range' => self::allowedStatuses()],
        ];
    }

    public function attributeLabels(): array
    {
        return [
            'id' => \Yii::t('app', 'ID'),
            'game_id' => \Yii::t('app', 'Game'),
            'organizer_id' => \Yii::t('app', 'Organizer'),
            'scheduled_at' => \Yii::t('app', 'Scheduled At'),
            'max_participants' => \Yii::t('app', 'Max Participants'),
            'status' => \Yii::t('app', 'Status'),
            'created_at' => \Yii::t('app', 'Created At'),
            'organizerUsername' => \Yii::t('app', 'Organizer'),
            'gameTitle' => \Yii::t('app', 'Game'),
        ];
    }

    public function behaviors()
    {
        return [
            StatusLogBehavior::class,
        ];
    }

    /**
     * Returns an array of all allowed statuses for a GameSession.
     *
     * @return string[] array of allowed statuses
     */
    public static function allowedStatuses(): array
    {
        return [
            self::STATUS_PLANNED,
            self::STATUS_ACTIVE,
            self::STATUS_COMPLETED,
            self::STATUS_CANCELLED,
        ];
    }

    /**
     * Gets query for [[Game]].
     */
    public function getGame(): ActiveQuery
    {
        return $this->hasOne(Game::class, ['id' => 'game_id']);
    }

    public function getOrganizer(): ActiveQuery
    {
        return $this->hasOne(User::class, ['id' => 'organizer_id']);
    }

    /**
     * @throws \Exception
     */
    public function getStatusLabel()
    {
        return ArrayHelper::getValue(self::getStatusLabels(), $this->status, \Yii::t('app', 'Planned'));
    }

    public static function getStatusLabels(): array
    {
        return [
            self::STATUS_PLANNED => \Yii::t('app', 'Planned'),
            self::STATUS_ACTIVE => \Yii::t('app', 'Active'),
            self::STATUS_COMPLETED => \Yii::t('app', 'Completed'),
            self::STATUS_CANCELLED => \Yii::t('app', 'Cancelled'),
        ];
    }
}
