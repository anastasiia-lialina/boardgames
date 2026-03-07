<?php


namespace app\services;

use app\models\game\Game;
use app\models\game\GameSubscription;
use Exception;
use Throwable;
use Yii;
use yii\db\StaleObjectException;
use yii\web\NotFoundHttpException;

/**
 * Сервис для управления подписками на игры
 */
class GameSubscriptionService
{
    /**
     * Подписка пользователя на игру
     *
     * @param int $userId ID пользователя
     * @param int $gameId ID игры
     * @return bool Успешность операции
     * @throws \yii\base\Exception При ошибке
     * @throws Exception
     */
    public function subscribe(int $userId, int $gameId): bool
    {
        // Проверяем, не подписан ли уже пользователь
        $existing = $this->findSubscription($userId, $gameId);

        if ($existing) {
            // Если подписка неактивна — активируем её
            if (!$existing->is_active) {
                $existing->is_active = true;
                return $existing->save(false, ['is_active']);
            }
            return true; // Уже подписан
        }

        $subscription = new GameSubscription();
        $subscription->user_id = $userId;
        $subscription->game_id = $gameId;
        $subscription->is_active = true;

        return $subscription->save();
    }

    /**
     * Отписка пользователя от игры
     *
     * @param int $userId ID пользователя
     * @param int $gameId ID игры
     * @return bool Успешность операции
     * @throws \yii\base\Exception При ошибке
     * @throws Exception
     */
    public function unsubscribe(int $userId, int $gameId): bool
    {
        $subscription = $this->findSubscription($userId, $gameId);

        if ($subscription) {
            $subscription->is_active = false;
            return $subscription->save(false, ['is_active']);
        }

        return true;
    }

    /**
     * Получение подписки пользователя на игру
     *
     * @param int $userId ID пользователя
     * @param int $gameId ID игры
     * @return GameSubscription|null Подписка, если не найдена - null
     */
    public function findSubscription(int $userId, int $gameId): ?GameSubscription
    {
        return GameSubscription::find()
            ->where(['user_id' => $userId, 'game_id' => $gameId])
            ->one();
    }

    /**
     * Переключение статуса подписки
     *
     * @param int $id
     * @return bool Успешность операции
     * @throws \yii\db\Exception
     */
    public function toggleSubscription(int $id): bool
    {
        $subscription = GameSubscription::findOne($id);

        if ($subscription) {
            $subscription->is_active = !$subscription->is_active;

            return $subscription->save(false, ['is_active']);
        }

        throw new Exception('The requested subscription does not exist.');
    }

    /**
     * Получение всех подписок пользователя
     *
     * @param int $userId ID пользователя
     * @param bool $onlyActive Только активные (по умолчанию: да)
     * @return GameSubscription[]
     */
    public function getUserSubscriptions(int $userId, bool $onlyActive = true): array
    {
        $query = GameSubscription::find()
            ->where(['user_id' => $userId])
            ->joinWith(['game'])
            ->orderBy(['created_at' => SORT_DESC]);

        if ($onlyActive) {
            $query->andWhere(['is_active' => true]);
        }

        return $query->all();
    }

    /**
     * Проверка подписки пользователя на игру
     *
     * @param int $userId ID пользователя
     * @param int $gameId ID игры
     * @return bool
     */
    public function isSubscribed(int $userId, int $gameId): bool
    {
        return GameSubscription::find()
            ->where(['user_id' => $userId, 'game_id' => $gameId, 'is_active' => true])
            ->exists();
    }

    /**
     * Получение количества подписчиков игры
     *
     * @param int $gameId ID игры
     * @return int
     */
    public function getSubscriberCount(int $gameId): int
    {
        return GameSubscription::find()
            ->where(['game_id' => $gameId, 'is_active' => true])
            ->count();
    }

    /**
     * Получение всех подписчиков игры
     *
     * @param int $gameId ID игры
     * @return array Массив ID пользователей
     */
    public function getSubscribers(int $gameId): array
    {
        return GameSubscription::find()
            ->select('user_id')
            ->where(['game_id' => $gameId, 'is_active' => true])
            ->column();
    }

    /**
     * @throws StaleObjectException
     * @throws Throwable
     */
    public function delete($id): bool|int
    {
        $subscription = GameSubscription::findOne($id);

        if ($subscription === null) {
            return true;
        }

        return $subscription->delete();
    }
}