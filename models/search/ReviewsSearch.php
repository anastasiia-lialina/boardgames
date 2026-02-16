<?php

namespace app\models\search;

use app\models\user\Reviews;
use yii\base\Model;
use yii\data\ActiveDataProvider;

/**
 * SearchReviews represents the model behind the search form of `app\models\user\Reviews`.
 */
class ReviewsSearch extends Reviews
{
    public $gameTitle;
    public $username;
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'game_id', 'user_id', 'rating'], 'integer'],
            [['comment', 'created_at', 'gameTitle', 'sername'], 'safe'],
            [['is_approved'], 'boolean'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function scenarios()
    {
        return Model::scenarios();
    }

    /**
     * Creates data provider instance with search query applied
     *
     * @param array $params
     * @param string|null $formName Form name to be used into `->load()` method.
     *
     * @return ActiveDataProvider
     */
    public function search($params, $formName = null)
    {
        $query = Reviews::find()->joinWith(['game', 'user']);

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' =>  20,
            ],
            'sort' => [
                'defaultOrder' => [
                    'created_at' => SORT_DESC,
                ],
                'attributes' => [
                    'id',
                    'game_id',
                    'user_id',
                    'rating',
                    'is_approved',
                    'created_at',
                    'gameTitle' => [
                        'asc' => ['games.title' => SORT_ASC],
                        'desc' => ['games.title' => SORT_DESC],
                    ],
                    'username' => [
                        'asc' => ['user.username' => SORT_ASC],
                        'desc' => ['user.username' => SORT_DESC],
                    ],
                ]
            ],
        ]);

        $this->load($params, $formName);

        if (!$this->validate()) {
            return $dataProvider;
        }

        // grid filtering conditions
        $query->andFilterWhere([
            'id' => $this->id,
            'game_id' => $this->game_id,
            'user_id' => $this->user_id,
            'rating' => $this->rating,
            'is_approved' => $this->is_approved,
            'created_at' => $this->created_at,
            'games.title' => $this->gameTitle,
            'user.username' => $this->username,
        ]);

        $query->andFilterWhere(['ilike', 'comment', $this->comment]);
        $query->andFilterWhere(['ilike', 'games.title', $this->gameTitle]);
        $query->andFilterWhere(['ilike', 'user.username', $this->username]);

        return $dataProvider;
    }

    /**
     * Получить одобренные отзывы для конкретной игры
     *
     * @param int $gameId
     * @param int $pageSize
     * @return ActiveDataProvider
     */
    public function getApprovedReviewsForGame(int $gameId, int $pageSize = 2): ActiveDataProvider
    {
        $query = self::find()
            ->where(['game_id' => $gameId, 'is_approved' => true])
            ->orderBy(['created_at' => SORT_DESC]);

        return new ActiveDataProvider([
            'query' => $query,
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
}
