<?php

namespace app\services;

use app\exception\ServiceException;
use app\models\forms\Form;
use app\models\game\Game;
use app\models\search\GameSearch;
use yii\data\ActiveDataProvider;

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

    public function createGame(Form $form): Game
    {
        $game = new Game();
        $this->load($game, $form);

        if (!$game->save()) {
            throw new ServiceException($game);
        }

        return $game;
    }

    /**
     * @throws ServiceException
     * @throws \Exception
     */
    public function updateGame(int $id, Form $form): Game
    {
        $game = $this->findModel(Game::class, $id);
        $this->load($game, $form);

        if (!$game->save()) {
            throw new ServiceException($game);
        }

        return $game;
    }

    /**
     * @throws \Exception
     */
    public function deleteGame(int $id): bool
    {
        $game = $this->findModel(Game::class, $id);

        return false !== $game->delete();
    }

    public function getGameWithSessions(int $id): Game
    {
        return Game::find()
            ->with(['sessions', 'reviews'])
            ->where(['id' => $id])
            ->one()
        ;
    }

    public function getPopularGames(int $limit = 10): array
    {
        return Game::find()
            ->joinWith(['sessions'])
            ->groupBy('{{%games}}.id')
            ->orderBy(['COUNT({{%game_sessions}}.id)' => SORT_DESC])
            ->limit($limit)
            ->all()
        ;
    }
}
