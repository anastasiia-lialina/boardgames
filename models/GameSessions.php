<?php

namespace app\models;

use DateTime;
use Yii;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;

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
 * @property Games $games
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
    public function rules(): array
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

            ['scheduled_at', 'validateFutureDate'],

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
            'game_id' => Yii::t('app', 'Игра'),
            'organizer_id' => Yii::t('app', 'Организатор'),
            'scheduled_at' => Yii::t('app', 'Дата и время'),
            'max_participants' => Yii::t('app', 'Макс. участников'),
            'status' => Yii::t('app', 'Статус'),
            'created_at' => Yii::t('app', 'Создан'),
        ];
    }

    /**
     * Дата должна быть в будущем
     * @throws \DateMalformedStringException
     */
    public function validateFutureDate($attribute): void
    {
        $scheduled = new DateTime($this->$attribute);
        $now = new DateTime();

        if ($scheduled < $now) {
            $this->addError(
                $attribute,
                Yii::t('app','Дата проведения не может быть в прошлом.')
            );
        }
    }

    /**
     * Gets query for [[Games]].
     *
     * @return ActiveQuery|GamesQuery
     */
    public function getGames(): ActiveQuery|GamesQuery
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
     * Получить предстоящие сессии
     */
    public static function findUpcoming(): GameSessionsQuery
    {
        return static::find()
            ->where(['>=', 'scheduled_at', new \DateTime()])
            ->andWhere(['status' => GameSessions::STATUS_PLANNED])
            ->orderBy(['scheduled_at' => SORT_ASC]);
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
}
