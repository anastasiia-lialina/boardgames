<?php

namespace app\models;

use Yii;
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
 * @property Games $games
 */
class Reviews extends ActiveRecord
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
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function rules(): array
    {
        return [
            [['game_id', 'user_id', 'rating'], 'required'],

            [['game_id', 'user_id', 'rating'], 'integer'],

            ['rating', 'integer', 'min' => self::MIN_RATING, 'max' => self::MAX_RATING],

            ['comment', 'string', 'max' => self::MAX_COMMENT_LENGTH],

            ['is_approved', 'boolean'],
            ['is_approved', 'default', 'value' => false],

            [
                ['user_id', 'game_id'],
                'unique',
                'targetAttribute' => ['user_id', 'game_id'],
                'message' => Yii::t('app', 'Вы уже оставляли отзыв к этой игре.')
            ],

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
            'user_id' => Yii::t('app', 'Пльзователь'),
            'rating' => Yii::t('app', 'Рейтинг'),
            'comment' => Yii::t('app', 'Комментарий'),
            'is_approved' => Yii::t('app', 'Одобрен'),
            'created_at' => Yii::t('app', 'Дата'),
        ];
    }

    /**
     * Gets query for [[Game]].
     *
     * @return ActiveQuery|GamesQuery
     */
    public function getGame(): ActiveQuery|GamesQuery
    {
        return $this->hasOne(Games::class, ['id' => 'game_id']);
    }

    /**
     * {@inheritdoc}
     * @return ReviewsQuery the active query used by this AR class.
     */
    public static function find(): ReviewsQuery
    {
        return new ReviewsQuery(get_called_class());
    }

    public function beforeSave($insert)
    {
        if (!parent::beforeSave($insert)) {
            return false;
        }

        // Если это новая запись, проверяем на дубликат
        if ($insert) {
            $existing = Review::find()
                ->where(['user_id' => $this->user_id, 'game_id' => $this->game_id])
                ->exists();

            if ($existing) {
                $this->addError('user_id', Yii::t('app', 'Вы уже оставляли отзыв к этой игре.'));
                return false;
            }
        }

        return true;
    }

}
