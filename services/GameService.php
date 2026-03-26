<?php

namespace app\services;

use app\exception\ServiceException;
use app\models\forms\Form;
use app\models\game\Game;
use app\models\game\GameSession;
use app\models\search\GameSearch;
use yii\base\Event;
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

        $this->refreshStats();

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
     * @param int $id
     * @return bool
     * @throws \Throwable
     */
    public function deleteGame(int $id): bool
    {
        $db = Game::getDb();

        return $db->transaction(callback: function ($db) use ($id) {
            $game = $this->findModel(Game::class, $id);

            // Проверяем связанные сессии перед удалением
            $sessionCount = GameSession::find()
                ->where(['game_id' => $id])
                ->count();

            if ($sessionCount > 0) {
                throw new \Exception('Нельзя удалить игру с существующими сессиями');
            }

            if (!$game->delete()) {
                return false;
            }

            $this->refreshStats();

            return true;
        });
    }

    public function getGameWithSessions(int $id): Game
    {
        return Game::find()
            ->with(['sessions', 'reviews'])
            ->where(['id' => $id])
            ->one()
        ;
    }
}
