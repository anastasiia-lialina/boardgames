<?php

namespace app\models\forms;

use app\models\game\Game;

class GameForm extends Game implements Form
{
    public $id;
    public $title;
    public $description;
    public $players_min;
    public $players_max;
    public $duration_min;
    public $complexity;
    public $year;

    public function rules(): array
    {
        return [
            [['title', 'players_min', 'players_max', 'duration_min', 'complexity', 'year'], 'required'],

            [['players_min', 'players_max', 'duration_min', 'year'], 'integer'],
            ['complexity', 'number'],

            [['title', 'description'], 'trim'],

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

    public function getSafeAttributes(): array
    {
        return [
            'title' => $this->title,
            'description' => $this->description,
            'players_min' => $this->players_min,
            'players_max' => $this->players_max,
            'duration_min' => $this->duration_min,
            'complexity' => $this->complexity,
            'year' => $this->year,
        ];
    }
}
