<?php

namespace app\models\game;

use app\behaviors\StatusLogBehavior;
use app\jobs\SendGameNotificationJob;
use app\models\user\User;
use app\notifications\SessionNotification;
use DateTime;
use Yii;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;
use yii\db\Expression;
use yii\db\Query;
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
 * @property Game $game
 */
class GameSession extends ActiveRecord
{
    public const STATUS_PLANNED = 'planned';
    public const STATUS_ACTIVE = 'active';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_CANCELLED = 'cancelled';

    public const MIN_PARTICIPANTS = 2;
    public const MAX_PARTICIPANTS = 20;

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

            ['status', 'default', 'value' => self::STATUS_PLANNED],

            ['max_participants', 'integer', 'min' => self::MIN_PARTICIPANTS, 'max' => self::MAX_PARTICIPANTS],

            ['status', 'in', 'range' => [
                self::STATUS_PLANNED,
                self::STATUS_ACTIVE,
                self::STATUS_COMPLETED,
                self::STATUS_CANCELLED,
            ]],

            ['status', 'validateStatus'],

            ['scheduled_at', 'validateScheduledAt'],

            [['game_id'], 'exist', 'skipOnError' => true, 'targetClass' => Game::class, 'targetAttribute' => ['game_id' => 'id']],
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
            'organizerUsername' => Yii::t('app', 'Organizer'),
            'gameTitle' => Yii::t('app', 'Game'),
        ];
    }

    public function behaviors()
    {
        return [
            StatusLogBehavior::class,
        ];
    }

    /**
     * Валидация статуса: при создании только 'planned'
     */
    public function validateStatus($attribute): void
    {
        if ($this->isNewRecord) {
            $this->status = self::STATUS_PLANNED;
        } else {
            // проверяем допустимые значения
            $allowedStatuses = [
                self::STATUS_PLANNED,
                self::STATUS_ACTIVE,
                self::STATUS_COMPLETED,
                self::STATUS_CANCELLED,
            ];

            if (!in_array($this->status, $allowedStatuses)) {
                $this->addError($attribute, Yii::t('app', 'Invalid status.'));
            }
        }
    }

    /**
     * Валидация даты в зависимости от статуса
     * @throws \Exception
     */
    public function validateScheduledAt($attribute): void
    {
        if (!$this->scheduled_at) {
            return;
        }
        $scheduled = new DateTime($this->scheduled_at);
        $now = new DateTime();

        match ($this->status) {
            self::STATUS_PLANNED => (function() use ($scheduled, $now, $attribute) {
                if ($scheduled <= $now) {
                    $this->addError($attribute, Yii::t('app', 'The scheduled date must be in the future.'));
                }
            })(),

            self::STATUS_ACTIVE => (function() use ($scheduled, $now, $attribute) {
                if ($scheduled > $now) {
                    $this->addError($attribute, Yii::t('app', 'An active session must have already started.'));
                }
            })(),

            self::STATUS_COMPLETED => (function() use ($scheduled, $now, $attribute) {
                if ($scheduled >= $now) {
                    $this->addError($attribute, Yii::t('app', 'A completed session must be in the past.'));
                }
            })(),

            default => null, // Для STATUS_CANCELLED и прочих ничего не делаем
        };
    }

    /**
     * Gets query for [[Game]].
     *
     * @return ActiveQuery
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
        return ArrayHelper::getValue($this->getStatusLabels(), $this->status);
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

    /**
     * Получить все сессии по статусу
     */
    public static function findByStatus(string $status): ActiveQuery
    {
        return static::find()
            ->where(['status' => $status])
            ->orderBy(['scheduled_at' => SORT_DESC]);
    }

    /**
     * Поиск количества просроченных сессий
     */
    public static function findStalePlannedCount(): int
    {
        return (int)self::find()->where(self::getStaleCondition())->count();
    }

    /**
     * Условие для поиска просроченных сессий
     */
    private static function getStaleCondition(): array
    {
        return [
            'and',
            ['status' => self::STATUS_PLANNED],
            ['<', 'scheduled_at', date('Y-m-d 00:00:00')],
        ];
    }

    /**
     * Отменяет запланированные сессии, которые должны были начаться до сегодняшнего дня
     *
     * @return int Количество обновлённых записей
     * @throws \Throwable
     */
    public static function updateExpiredSessions(): int
    {
        $db = self::getDb();
        $condition = self::getStaleCondition();
        $now = date('Y-m-d H:i:s');


        return $db->transaction(function ($db) use ($condition, $now) {

            // Берем данные для вставки в лог
            $dataToLog = (new Query())
                ->select([
                    'session_id' => 'id',              // берем ID из game_session
                    'old_status' => 'status',          // берем текущий статус
                    'new_status' => new Expression(':new', [':new' => self::STATUS_CANCELLED]),
                    'changed_at' => new Expression(':time', [':time' => $now]),
                ])
                ->from(self::tableName())
                ->where($condition);

            // Копируем данные о сессии в лог
            $db->createCommand()
                ->insert('game_session_log', $dataToLog)
                ->execute();

            // Обновляем записи сессии
            return $db->createCommand()
                ->update(self::tableName(), ['status' => self::STATUS_CANCELLED], $condition)
                ->execute();
        });
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

    /**
     * @inheritdoc
     */
    public function afterSave($insert, $changedAttributes)
    {
        parent::afterSave($insert, $changedAttributes);

        Yii::$app->queue->push(new SendGameNotificationJob([
            'sessionId' => $this->id,
            'gameId' => $this->game_id,
            'statusLabel' => $this->statusLabel,
            'sessionDate' => $this->scheduled_at,
        ]));
    }
}
