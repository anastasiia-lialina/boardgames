<?php

namespace app\models\search;

use app\models\game\GameSubscription;
use yii\base\Model;
use yii\data\ActiveDataProvider;

class GameSubscriptionSearch extends GameSubscription
{
    public function rules()
    {
        return [
            [['id', 'user_id', 'game_id'], 'integer'],
            [['created_at'], 'safe'],
            ['is_active', 'boolean'],
        ];
    }

    public function scenarios()
    {
        return Model::scenarios();
    }

    public function search($params, $userId)
    {
        $query = GameSubscription::find()->where(['user_id' => $userId])->joinWith(['game']);

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => 20,
            ],
            'sort' => [
                'defaultOrder' => [
                    'created_at' => SORT_DESC,
                ],
                'attributes' => [
                    'id',
                    'game_id',
                    'created_at',
                    'is_active',
                    'game.title' => [
                        'asc' => ['game.title' => SORT_ASC],
                        'desc' => ['game.title' => SORT_DESC],
                    ],
                ],
            ],
        ]);

        $this->load($params);

        if (!$this->validate()) {
            return $dataProvider;
        }

        $query->andFilterWhere([
            'id' => $this->id,
            'game_id' => $this->game_id,
            'is_active' => $this->is_active,
            'created_at' => $this->created_at,
        ]);

        $query->andFilterWhere(['like', 'game.title', $this->getAttribute('game.title')]);

        return $dataProvider;
    }
}