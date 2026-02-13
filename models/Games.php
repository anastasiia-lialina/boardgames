<?php

namespace app\models;

use DateTime;
use Yii;
use yii\behaviors\TimestampBehavior;
use yii\data\ActiveDataProvider;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;
use yii\db\Expression;

/**
 * This is the model class for table "games".
 *
 * @property int $id
 * @property string $title Название
 * @property string|null $description Описание
 * @property int $players_min Минимальное количество игроков
 * @property int $players_max Максимальное количество игроков
 * @property int $duration_min Минимальное время игры(мин.)
 * @property float $complexity Сложность
 * @property int $year Год выпуска
 * @property string $created_at Дата добавления
 *
 * @property GameSessions[] $gameSessions
 * @property Reviews[] $reviews
 */
class Games extends ActiveRecord
{
    const MIN_PLAYERS = 1;
    const MAX_PLAYERS = 20;
    const MIN_DURATION = 5;
    const MIN_COMPLEXITY = 1.0;
    const MAX_COMPLEXITY = 5.0;
    const MIN_YEAR = 1900;

    /**
     * {@inheritdoc}
     */
    public static function tableName(): string
    {
        return '{{%games}}';
    }

    public function behaviors(): array
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
    public function rules()
    {
        return [
            [['title', 'players_min', 'players_max', 'duration_min', 'complexity', 'year'], 'required'],

            [['players_min', 'players_max', 'duration_min', 'year'], 'integer'],
            ['complexity', 'number'],

            ['title', 'string', 'max' => 200, 'min' => 3],
            ['description', 'string'],

            ['players_min', 'integer', 'min' => self::MIN_PLAYERS, 'max' => self::MAX_PLAYERS],
            ['players_max', 'integer', 'min' => self::MIN_PLAYERS, 'max' => self::MAX_PLAYERS],
            ['players_min', 'compare', 'compareAttribute' => 'players_max', 'operator' => '<='],

            ['duration_min', 'integer', 'min' => self::MIN_DURATION],

            ['complexity', 'number', 'min' => self::MIN_COMPLEXITY, 'max' => self::MAX_COMPLEXITY],

            ['year', 'integer', 'min' => self::MIN_YEAR, 'max' => date('Y')],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels(): array
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'title' => Yii::t('app', 'Title'),
            'description' => Yii::t('app', 'Description'),
            'players_min' => Yii::t('app', 'Min. players'),
            'players_max' => Yii::t('app', 'Max. players'),
            'duration_min' => Yii::t('app', 'Duration (min.)'),
            'complexity' => Yii::t('app', 'Complexity'),
            'year' => Yii::t('app', 'Year'),
            'created_at' => Yii::t('app', 'Created At'),
        ];
    }

    /**
     * Получить средний рейтинг игры
     */
    public function getAverageRating(): float|int
    {
        $rating = Reviews::find()
            ->where(['game_id' => $this->id, 'is_approved' => true])
            ->average('rating');

        return $rating ? round($rating, 1) : 0;
    }

    /**
     * Получить количество одобренных отзывов
     */
    public function getReviewsCount(): int
    {
        return Reviews::find()
            ->where(['game_id' => $this->id, 'is_approved' => true])
            ->count();
    }

    /**
     * Получить все одобренные отзывы
     */
    public function getApprovedReviews(): ActiveQuery
    {
        return $this->hasMany(Reviews::class, ['game_id' => 'id'])
            ->where(['is_approved' => true])
            ->orderBy(['created_at' => SORT_DESC]);
    }

    /**
     * Получить все отзывы
     */
    public function getReviews(): ActiveQuery
    {
        return $this->hasMany(Reviews::class, ['game_id' => 'id']);
    }

    /**
     * Получить все сессии
     */
    public function getSessions(): ActiveQuery
    {
        return $this->hasMany(GameSessions::class, ['game_id' => 'id']);
    }

    /**
     * Получить предстоящие сессии
     */
    public function getUpcomingSessions(): ActiveQuery
    {
        return $this->hasMany(GameSessions::class, ['game_id' => 'id'])
            ->where(['>=', 'scheduled_at', date('Y-m-d H:i:s')])
            ->andWhere(['status' => GameSessions::STATUS_PLANNED])
            ->orderBy(['scheduled_at' => SORT_ASC]);
    }

    /**
    * Получить список одобренных отзывов отсортированный и с пагинацией
    */
    public function getReviewsDataProvider($pageSize = 2): ActiveDataProvider
    {
        return new ActiveDataProvider([
            'query' => $this->getApprovedReviews(),
            'pagination' => [
                'pageSize' => $pageSize,
            ],
            'sort' => [
                'defaultOrder' => [
                    'created_at' => SORT_DESC,
                ],
            ],
        ]);
    }

    /**
     * {@inheritdoc}
     * @return GamesQuery the active query used by this AR class.
     */
    public static function find(): GamesQuery
    {
        return new GamesQuery(get_called_class());
    }

}
