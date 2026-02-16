<?php

namespace app\models\search;

use app\models\game\Games;
use yii\base\Model;
use yii\data\ActiveDataProvider;

/**
 * SearchGame represents the model behind the search form of `app\models\game\Game`.
 */
class GamesSearch extends Games
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'players_min', 'players_max', 'duration_min', 'year'], 'integer'],
            [['title', 'description', 'created_at'], 'safe'],
            [['complexity'], 'number'],
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
        $query = Games::find();

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        $this->load($params, $formName);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        // grid filtering conditions
        $query->andFilterWhere([
            'id' => $this->id,
            'players_min' => $this->players_min,
            'players_max' => $this->players_max,
            'duration_min' => $this->duration_min,
            'complexity' => $this->complexity,
            'year' => $this->year,
            'created_at' => $this->created_at,
        ]);

        $query->andFilterWhere(['ilike', 'title', $this->title])
            ->andFilterWhere(['ilike', 'description', $this->description]);

        return $dataProvider;
    }
}
