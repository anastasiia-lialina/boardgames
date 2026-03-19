<?php

namespace app\models\game;

use app\models\user\User;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;
use yii\db\Expression;

/**
 * Модель подписки пользователя на игру.
 *
 * @property int $id
 * @property int $user_id
 * @property int $game_id
 * @property string $created_at
 * @property bool $is_active
 * @property User $user
 * @property Game $game
 */
class GameSubscription extends ActiveRecord
{
    public const STATUS_ACTIVE = true;
    public const STATUS_INACTIVE = false;

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
            'id' => \Yii::t('app', 'ID'),
            'user_id' => \Yii::t('app', 'User'),
            'game_id' => \Yii::t('app', 'Game'),
            'created_at' => \Yii::t('app', 'Created At'),
            'is_active' => \Yii::t('app', 'Status'),
            'game.title' => \Yii::t('app', 'Game'),
        ];
    }

    /**
     * Получить пользователя.
     */
    public function getUser()
    {
        return $this->hasOne(User::class, ['id' => 'user_id']);
    }

    /**
     * Получить игру.
     */
    public function getGame(): ActiveQuery
    {
        return $this->hasOne(Game::class, ['id' => 'game_id']);
    }
}
