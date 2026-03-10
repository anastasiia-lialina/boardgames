<?php

namespace app\models\forms;

use app\models\user\Review;
use Yii;

class ReviewForm extends Review
{
    public $id;
    public $game_id;
    public $user_id;
    public $rating;
    public $comment;

    public function rules(): array
    {
        return [
            [['game_id', 'user_id', 'rating', 'comment'], 'required'],
            ['game_id', 'integer'],
            ['rating', 'integer', 'min' => 1, 'max' => 5],
            ['comment', 'string', 'min' => 10, 'max' => 1000],
            [
                ['user_id', 'game_id'],
                'unique',
                'targetAttribute' => ['user_id', 'game_id'],
                'message' => Yii::t('app', 'You have already left a review for this game.'),
            ],
        ];
    }
}
