<?php

namespace app\services;

use app\models\game\Game;
use app\models\search\GameSearch;
use yii\data\ActiveDataProvider;
use yii\db\Exception;

class GameService extends BaseService
{
    public function getGameProvider(array $params): ActiveDataProvider
    {
        $searchModel = new GameSearch();
        return $searchModel->search($params);
    }

    public function getGameSearchModel(): GameSearch
    {
        return new GameSearch();
    }

    public function findGame(int $id): Game
    {
        return $this->findModel(Game::class, $id);
    }

    public function createGame(array $data): Game
    {
        $game = new Game();
        $game->load($data);

        if (!$game->save()) {
            throw new Exception($this->formatValidationErrors($game));
        }

        return $game;
    }

    public function updateGame(int $id, array $data): Game
    {
        $game = $this->findGame($id);
        $game->load($data);

        if (!$game->save()) {
            throw new Exception($this->formatValidationErrors($game));
        }

        return $game;
    }

    public function deleteGame(int $id): bool
    {
        $game = $this->findGame($id);
        return $game->delete() !== false;
    }

    public function getGameWithSessions(int $id): Game
    {
        return Game::find()
            ->with(['sessions', 'reviews'])
            ->where(['id' => $id])
            ->one();
    }

    public function getPopularGames(int $limit = 10): array
    {
        return Game::find()
            ->joinWith(['sessions'])
            ->groupBy('{{%games}}.id')
            ->orderBy(['COUNT({{%game_sessions}}.id)' => SORT_DESC])
            ->limit($limit)
            ->all();
    }
}
