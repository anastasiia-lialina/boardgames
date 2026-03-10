<?php

namespace app\services;

use app\models\game\GameSubscription;
use app\models\search\GameSubscriptionSearch;
use yii\base\Exception;
use yii\data\ActiveDataProvider;

/**
 * Сервис для управления подписками на игры
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
     * Подписка пользователя на игру
     *
     * @param int $userId ID пользователя
     * @param int $gameId ID игры
     * @return bool Успешность операции
     * @throws \yii\base\Exception При ошибке
     * @throws Exception
     */
    public function isSubscribed(int $userId, int $gameId): bool
    {
        $subscription = $this->findSubscription($userId, $gameId);
        return $subscription && $subscription->is_active === GameSubscription::STATUS_ACTIVE;
    }

    public function subscribe(int $userId, int $gameId): bool
    {
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
            throw new Exception($this->formatValidationErrors($subscription));
        }

        return true;
    }

    public function unsubscribe(int $userId, int $gameId): bool
    {
        $subscription = $this->findSubscription($userId, $gameId);

        if (!$subscription || !$subscription->is_active) {
            return true; // Уже не подписан
        }

        $subscription->is_active = GameSubscription::STATUS_INACTIVE;
        return $subscription->save(false, ['is_active']);
    }

    public function deleteSubscription(int $id): bool
    {
        $subscription = $this->findSubscriptionById($id);
        return $subscription->delete() !== false;
    }

    public function getUserSubscriptions(int $userId): array
    {
        return GameSubscription::find()
            ->with(['game'])
            ->where(['user_id' => $userId, 'is_active' => GameSubscription::STATUS_ACTIVE])
            ->all();
    }

    public function getGameSubscribers(int $gameId): array
    {
        return GameSubscription::find()
            ->with(['user'])
            ->where(['game_id' => $gameId, 'is_active' => GameSubscription::STATUS_ACTIVE])
            ->all();
    }

    public function getSubscriptionStats(int $gameId): array
    {
        $total = GameSubscription::find()
            ->where(['game_id' => $gameId])
            ->count();

        $active = GameSubscription::find()
            ->where(['game_id' => $gameId, 'is_active' => GameSubscription::STATUS_ACTIVE])
            ->count();

        return [
            'total_subscriptions' => $total,
            'active_subscriptions' => $active,
            'inactive_subscriptions' => $total - $active,
        ];
    }
}
