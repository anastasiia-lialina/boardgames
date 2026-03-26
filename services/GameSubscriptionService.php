<?php

namespace app\services;

use app\exception\ServiceException;
use app\models\game\GameSubscription;
use app\models\search\GameSubscriptionSearch;
use yii\base\Exception;
use yii\data\ActiveDataProvider;
use yii\db\StaleObjectException;

/**
 * Сервис для управления подписками на игры.
 */
class GameSubscriptionService extends BaseService
{
    public function getSubscriptionProvider(array $params, ?int $userId = null): ActiveDataProvider
    {
        $searchModel = new GameSubscriptionSearch();

        return $searchModel->search($params, $userId);
    }

    public function findSubscription(int $userId, int $gameId): ?GameSubscription
    {
        return GameSubscription::findOne(['user_id' => $userId, 'game_id' => $gameId]);
    }

    public function findSubscriptionById(int $id): GameSubscription
    {
        return $this->findModel(GameSubscription::class, $id);
    }

    /**
     * Подписка пользователя на игру.
     *
     * @param int $userId ID пользователя
     * @param int $gameId ID игры
     *
     * @return bool Успешность операции
     */
    public function isSubscribed(int $userId, int $gameId): bool
    {
        $subscription = $this->findSubscription($userId, $gameId);

        return $subscription && GameSubscription::STATUS_ACTIVE === $subscription->is_active;
    }

    /**
     * @param int $userId
     * @param int $gameId
     * @return bool
     * @throws \Throwable
     */
    public function subscribe(int $userId, int $gameId): bool
    {
        $db = GameSubscription::getDb();

        return $db->transaction(function ($db) use ($userId, $gameId) {
            $existing = $this->findSubscription($userId, $gameId);

            if ($existing) {
                if ($existing->is_active) {
                    return true; // Уже подписан
                }

                $existing->is_active = GameSubscription::STATUS_ACTIVE;

                return $existing->save(false, ['is_active']);
            }

            $subscription = new GameSubscription();
            $subscription->user_id = $userId;
            $subscription->game_id = $gameId;
            $subscription->is_active = GameSubscription::STATUS_ACTIVE;

            if (!$subscription->save()) {
                throw new ServiceException($subscription);
            }

            $this->refreshStats();

            return true;
        });
    }

    /**
     * @param int $userId
     * @param int $gameId
     * @return bool
     * @throws \Throwable
     */
    public function unsubscribe(int $userId, int $gameId): bool
    {
        $db = GameSubscription::getDb();

        return $db->transaction(function ($db) use ($userId, $gameId) {
            $subscription = $this->findSubscription($userId, $gameId);

            if (!$subscription || !$subscription->is_active) {
                return true; // Уже не подписан
            }

            $subscription->is_active = GameSubscription::STATUS_INACTIVE;

            if (!$subscription->save(false, ['is_active'])) {
                throw new ServiceException($subscription);
            }
            $this->refreshStats();

            return true;
        });
    }

    /**
     * Переключение статуса подписки.
     *
     * @param int $id
     * @return bool Успешность операции
     *
     * @throws \Throwable
     */
    public function toggleSubscription(int $id): bool
    {
        $subscription = $this->findModel(GameSubscription::class, $id);

        $subscription->is_active = !$subscription->is_active;

        if (!$subscription->save(false, ['is_active'])) {
            throw new ServiceException($subscription);
        }

        $this->refreshStats();

        return true;
    }

    /**
     * @throws \Throwable
     * @throws StaleObjectException
     */
    public function deleteSubscription(int $id): bool
    {
        $subscription = $this->findSubscriptionById($id);

        if (!$subscription->delete()) {
            return false;
        }

        $this->refreshStats();
        return true;
    }
}
