<?php

namespace app\models\game;

use app\models\user\User;
use Yii;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;
use yii\behaviors\TimestampBehavior;
use yii\db\Exception;
use yii\db\Expression;

/**
 * Модель подписки пользователя на игру
 *
 * @property int $id
 * @property int $user_id
 * @property int $game_id
 * @property string $created_at
 * @property bool $is_active
 *
 * @property User $user
 * @property Game $game
 */
class GameSubscription extends ActiveRecord
{
    const STATUS_ACTIVE = 1;
    const STATUS_INACTIVE = 0;

    public static function tableName()
    {
        return '{{%game_subscriptions}}';
    }

    public function behaviors()
    {
        return [
            'timestamp' => [
                'class' => TimestampBehavior::class,
                'createdAtAttribute' => 'created_at',
                'updatedAtAttribute' => false,
                'value' => new Expression('CURRENT_TIMESTAMP'),
            ],
        ];
    }

    public function rules()
    {
        return [
            [['user_id', 'game_id'], 'required'],
            [['user_id', 'game_id'], 'integer'],
            [['is_active'], 'boolean'],
            [['user_id', 'game_id'], 'unique', 'targetAttribute' => ['user_id', 'game_id']],
            [['user_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::class, 'targetAttribute' => ['user_id' => 'id']],
            [['game_id'], 'exist', 'skipOnError' => true, 'targetClass' => Game::class, 'targetAttribute' => ['game_id' => 'id']],
        ];
    }

    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'user_id' => Yii::t('app', 'User'),
            'game_id' => Yii::t('app', 'Game'),
            'created_at' => Yii::t('app', 'Created At'),
            'is_active' => Yii::t('app', 'Status'),
            'game.title' => Yii::t('app', 'Game'),
        ];
    }

    /**
     * Получить пользователя
     */
    public function getUser()
    {
        return $this->hasOne(User::class, ['id' => 'user_id']);
    }

    /**
     * Получить игру
     */
    public function getGame(): ActiveQuery
    {
        return $this->hasOne(Game::class, ['id' => 'game_id']);
    }

    /**
     * Подписаться на игру
     * @param int $userId
     * @param int $gameId
     * @return bool
     * @throws Exception
     */
    public static function subscribe(int $userId, int $gameId): bool
    {
        // Проверяем, не подписан ли уже пользователь
        $existing = self::find()
            ->where(['user_id' => $userId, 'game_id' => $gameId])
            ->one();

        if ($existing) {
            // Если подписка неактивна — активируем её
            if (!$existing->is_active) {
                $existing->is_active = true;
                return $existing->save(false, ['is_active']);
            }
            return true; // Уже подписан
        }

        $subscription = new self();
        $subscription->user_id = $userId;
        $subscription->game_id = $gameId;
        $subscription->is_active = true;

        return $subscription->save();
    }

    /**
     * Отписаться от игры
     * @param int $userId
     * @param int $gameId
     * @return bool
     * @throws Exception
     */
    public static function unsubscribe(int $userId, int $gameId): bool
    {
        $subscription = self::find()
            ->where(['user_id' => $userId, 'game_id' => $gameId])
            ->one();

        if ($subscription) {
            $subscription->is_active = false;
            return $subscription->save(false, ['is_active']);
        }

        return true;
    }

    /**
     * Получить всех активных подписчиков игры
     * @param int $gameId
     * @return array Массив ID пользователей
     */
    public static function getSubscribers(int $gameId): array
    {
        return self::find()
            ->select('user_id')
            ->where(['game_id' => $gameId, 'is_active' => true])
            ->column();
    }

    /**
     * Проверить, подписан ли пользователь на игру
     * @param int $userId
     * @param int $gameId
     * @return bool
     */
    public static function isSubscribed(int $userId, int $gameId): bool
    {
        return self::find()
            ->where(['user_id' => $userId, 'game_id' => $gameId, 'is_active' => true])
            ->exists();
    }
}