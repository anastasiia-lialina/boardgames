<?php

namespace app\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\models\GameSessions;
use yii\helpers\ArrayHelper;

/**
 * SearchGameSessions represents the model behind the search form of `app\models\GameSessions`.
 */
class SearchGameSessions extends GameSessions
{
    public $gameTitle;
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'game_id', 'organizer_id', 'max_participants'], 'integer'],
            [['scheduled_at', 'status', 'created_at', 'gameTitle'], 'safe'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function scenarios()
    {
        // bypass scenarios() implementation in the parent class
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
        $query = GameSessions::find()->joinWith(['game']);

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => [
                'defaultOrder' => [
                    'scheduled_at' => SORT_DESC,
                ],
                'attributes' => [
                    'id',
                    'game_id',
                    'organizer_id',
                    'scheduled_at',
                    'max_participants',
                    'status',
                    'created_at',
                    'gameTitle' => [
                        'asc' => ['games.title' => SORT_ASC],
                        'desc' => ['games.title' => SORT_DESC],
                    ],
                ],
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
            'organizer_id' => $this->organizer_id,
            'scheduled_at' => $this->scheduled_at,
            'max_participants' => $this->max_participants,
            'created_at' => $this->created_at,
        ]);

        $query->andFilterWhere(['ilike', 'status', $this->status]);
        // Фильтрация по названию игры
        $query->andFilterWhere(['like', 'games.title', $this->gameTitle]);

        return $dataProvider;
    }

    /**
     * Получить предстоящие сессии для конкретной игры
     *
     * @param int $gameId
     * @param int $pageSize
     * @return ActiveDataProvider
     */
    public function getUpcomingSessionsForGame($gameId, $pageSize = 2)
    {
        $query = self::find()
            ->where(['>=', 'scheduled_at', date('Y-m-d H:i:s')])
            ->andWhere(['status' => self::STATUS_PLANNED])
            ->andWhere(['game_id' => $gameId])
            ->orderBy(['scheduled_at' => SORT_ASC]);

        return new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => $pageSize,
            ],
            'sort' => [
                'defaultOrder' => [
                    'scheduled_at' => SORT_ASC,
                ],
            ],
        ]);
    }
}
