<?php

namespace app\models\user;

use app\models\game\Game;
use Yii;
use yii\behaviors\BlameableBehavior;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;
use yii\db\Expression;

/**
 * This is the model class for table "reviews".
 *
 * @property int $id
 * @property int $game_id Ид игры
 * @property int $user_id Ид пользователя оставившего отзыв
 * @property int $rating Рейтинг (1-5)
 * @property string|null $comment Отзыв
 * @property bool $is_approved Статус модерации
 * @property string $created_at Дата добавления
 *
 */
class Review extends ActiveRecord
{

    const MIN_RATING = 1;
    const MAX_RATING = 5;
    const MAX_COMMENT_LENGTH = 1000;

    /**
     * {@inheritdoc}
     */
    public static function tableName(): string
    {
        return '{{%reviews}}';
    }

    public function behaviors()
    {
        return [
            [
                'class' => TimestampBehavior::class,
                'updatedAtAttribute' => false,
                'value' => new Expression('NOW()'),
            ],
            'blameable' => [
                'class' => BlameableBehavior::class,
                'updatedByAttribute' => false,
                'createdByAttribute' => 'user_id',
            ]
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['game_id', 'rating'], 'required'],

            [['game_id', 'user_id', 'rating'], 'integer'],

            ['rating', 'integer', 'min' => self::MIN_RATING, 'max' => self::MAX_RATING],

            [['comment'], 'trim'],

            ['comment', 'string', 'max' => self::MAX_COMMENT_LENGTH],

            ['is_approved', 'boolean'],
            ['is_approved', 'default', 'value' => false],

            [
                ['user_id', 'game_id'],
                'unique',
                'targetAttribute' => ['user_id', 'game_id'],
                'message' => Yii::t('app', 'You have already left a review for this game.')
            ],

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
            'user_id' => Yii::t('app', 'User'),
            'rating' => Yii::t('app', 'Rating'),
            'comment' => Yii::t('app', 'Comment'),
            'is_approved' => Yii::t('app', 'Is Approved'),
            'created_at' => Yii::t('app', 'Created At'),
            'username' => Yii::t('app', 'User'),
            'gameTitle' => Yii::t('app', 'Game'),
        ];
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

    public function getUser(): ActiveQuery
    {
        return $this->hasOne(User::class, ['id' => 'user_id']);
    }
}
