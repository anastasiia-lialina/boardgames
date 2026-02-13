<?php

namespace app\models;

use DateTime;
use Yii;
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
 *
 * @property Games $game
 */
class GameSessions extends ActiveRecord
{

    const STATUS_PLANNED = 'planned';
    const STATUS_ACTIVE = 'active';
    const STATUS_COMPLETED = 'completed';
    const STATUS_CANCELLED = 'cancelled';

    const MIN_PARTICIPANTS = 2;
    const MAX_PARTICIPANTS = 20;

    /**
     * {@inheritdoc}
     */
    public static function tableName(): string
    {
        return '{{%game_sessions}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['game_id', 'organizer_id', 'scheduled_at', 'max_participants'], 'required'],

            [['game_id', 'organizer_id', 'max_participants'], 'integer'],

            ['scheduled_at', 'datetime'],

            ['max_participants', 'integer', 'min' => self::MIN_PARTICIPANTS, 'max' => self::MAX_PARTICIPANTS],

            ['status', 'in', 'range' => [
                self::STATUS_PLANNED,
                self::STATUS_ACTIVE,
                self::STATUS_COMPLETED,
                self::STATUS_CANCELLED
            ]],

            ['status', 'validateStatus'],

            ['scheduled_at', 'validateScheduledAt'],

            [['game_id'], 'exist', 'skipOnError' => true, 'targetClass' => Games::class, 'targetAttribute' => ['game_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels(): array
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'game_id' => Yii::t('app', 'Game'),
            'organizer_id' => Yii::t('app', 'Organizer'),
            'scheduled_at' => Yii::t('app', 'Scheduled At'),
            'max_participants' => Yii::t('app', 'Max Participants'),
            'status' => Yii::t('app', 'Status'),
            'created_at' => Yii::t('app', 'Created At'),
        ];
    }

    /**
     * Валидация статуса: при создании только 'planned'
     */
    public function validateStatus($attribute)
    {
        if ($this->isNewRecord) {
            $this->status = self::STATUS_PLANNED;
        } else {
            // проверяем допустимые значения
            $allowedStatuses = [
                self::STATUS_PLANNED,
                self::STATUS_ACTIVE,
                self::STATUS_COMPLETED,
                self::STATUS_CANCELLED
            ];

            if (!in_array($this->status, $allowedStatuses)) {
                $this->addError($attribute, Yii::t('app', 'Invalid status.'));
            }
        }
    }

    /**
     * Валидация даты в зависимости от статуса
     */
    public function validateScheduledAt($attribute)
    {
        if (!$this->scheduled_at) {
            return;
        }
        $scheduled = new \DateTime($this->scheduled_at);
        $now = new \DateTime();

        switch ($this->status) {
            case self::STATUS_PLANNED:
                // Запланированная сессия
                if ($scheduled <= $now) {
                    $this->addError($attribute, Yii::t('app', 'The scheduled date must be in the future.'));
                }
                break;

            case self::STATUS_ACTIVE:
                // сессия уже началась
                if ($scheduled > $now) {
                    $this->addError($attribute, Yii::t('app', 'An active session must have already started.'));
                }
                break;

            case self::STATUS_COMPLETED:
                // сессия уже закончилась
                if ($scheduled >= $now) {
                    $this->addError($attribute, Yii::t('app', 'A completed session must be in the past.'));
                }
                break;

            case self::STATUS_CANCELLED:
                // дата может быть любой
                break;
        }
    }

    /**
     * Gets query for [[Games]].
     *
     * @return ActiveQuery|GamesQuery
     */
    public function getGame(): ActiveQuery|GamesQuery
    {
        return $this->hasOne(Games::class, ['id' => 'game_id']);
    }

    /**
     * {@inheritdoc}
     * @return GameSessionsQuery the active query used by this AR class.
     */
    public static function find(): GameSessionsQuery
    {
        return new GameSessionsQuery(get_called_class());
    }

    /**
     * Получить все сессии по статусу
     */
    public static function findByStatus($status): GameSessionsQuery
    {
        return static::find()
            ->where(['status' => $status])
            ->orderBy(['scheduled_at' => SORT_DESC]);
    }

    public function getStatusLabels(): array
    {
        return [
            self::STATUS_PLANNED => Yii::t('app', 'Planned'),
            self::STATUS_ACTIVE => Yii::t('app', 'Active'),
            self::STATUS_COMPLETED => Yii::t('app', 'Completed'),
            self::STATUS_CANCELLED => Yii::t('app', 'Cancelled'),
        ];
    }

    public function getStatusLabel()
    {
        return ArrayHelper::getValue($this->getStatusLabels(), $this->status);
    }

    public function beforeSave($insert)
    {
        if (parent::beforeSave($insert)) {
            if ($this->scheduled_at) {
                $date = DateTime::createFromFormat('d.m.Y H:i', $this->scheduled_at);
                if ($date) {
                    $this->scheduled_at = $date->format('Y-m-d H:i:s');
                }
            }
            return true;
        }
        return false;
    }

}
