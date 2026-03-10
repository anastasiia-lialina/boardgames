<?php

namespace app\services;

use app\jobs\SendGameNotificationJob;
use app\models\forms\GameSessionForm;
use app\models\game\GameSession;
use Yii;
use yii\base\Exception;
use yii\db\Expression;
use yii\db\Query;
use yii\db\StaleObjectException;
use yii\web\NotFoundHttpException;

/**
 * Сервис для управления игровыми сессиями
 */
class GameSessionService extends BaseService
{
    /**
     * Создание новой игровой сессии
     *
     * @param GameSessionForm $form
     * @param int $organizerId ID организатора
     * @return GameSession Созданная сессия
     * @throws \yii\db\Exception
     */
    public function createSession(GameSessionForm $form, int $organizerId): GameSession
    {
        $session = new GameSession();
        $session->organizer_id = $organizerId;
        $this->load($session, $form);

        if ($session->save()) {
            $this->notifySubscribers($session);
        }

        return $session;
    }

    /**
     * Обновление сессии
     *
     * @param int $sessionId ID сессии
     * @param GameSessionForm $form
     * @return GameSession Обновленная сессия
     * @throws NotFoundHttpException
     * @throws \yii\db\Exception
     */
    public function updateSession(int $sessionId, GameSessionForm $form): GameSession
    {
        $session = $this->findModel($sessionId);

        $oldStatus = $session->status;

        $this->load($session, $form);

        if ($session->save()) {
            if ($oldStatus !== $session->status) {
                $this->notifySubscribers($session);
            }
        }

        return $session;
    }

    /**
     * Удаление сессии
     *
     * @param int $sessionId ID сессии
     * @return bool Успешность операции
     * @throws NotFoundHttpException если сессия не найдена
     * @throws \Throwable
     * @throws StaleObjectException
     */
    public function deleteSession(int $sessionId): bool
    {
        $session = $this->findModel($sessionId);
        return $session->delete();
    }

    /**
     * Обновление статуса сессии
     *
     * @param int $sessionId ID сессии
     * @param string $newStatus Новый статус
     * @return bool Успешность операции
     * @throws Exception При ошибке
     */
    public function updateStatus(int $sessionId, string $newStatus): bool
    {
        $session = GameSession::findOne($sessionId);

        if (!$session) {
            throw new Exception(Yii::t('app', 'Session not found.'));
        }

        $oldStatus = $session->status;
        $session->status = $newStatus;

        if (!$session->save()) {
            throw new Exception(Yii::t('app', 'Failed to update session status.'));
        }

        $this->handleStatusChange($session, $oldStatus, $newStatus);

        return true;
    }

    /**
     * Отмена сессии
     *
     * @param int $sessionId ID сессии
     * @return bool Успешность операции
     * @throws Exception При ошибке
     */
    public function cancelSession(int $sessionId): bool
    {
        return $this->updateStatus($sessionId, GameSession::STATUS_CANCELLED);
    }

    /**
     * Завершение сессии
     *
     * @param int $sessionId ID сессии
     * @return bool Успешность операции
     * @throws Exception При ошибке
     */
    public function completeSession(int $sessionId): bool
    {
        return $this->updateStatus($sessionId, GameSession::STATUS_COMPLETED);
    }

    /**
     * Получение сессии по ID
     *
     * @param int $sessionId ID сессии
     * @return GameSession
     * @throws Exception Если сессия не найдена
     */
    public function getSessionById(int $sessionId): GameSession
    {
        $session = GameSession::findOne($sessionId);

        if (!$session) {
            throw new Exception(Yii::t('app', 'Session not found.'));
        }

        return $session;
    }

    /**
     * Получение всех сессий для игры
     *
     * @param int $gameId ID игры
     * @return GameSession[]
     */
    public function getSessionsByGame(int $gameId): array
    {
        return GameSession::find()
            ->where(['game_id' => $gameId])
            ->orderBy(['scheduled_at' => SORT_DESC])
            ->all();
    }

    /**
     * Получение сессий пользователя
     *
     * @param int $userId ID пользователя
     * @param ?string $status Статус
     * @return GameSession[]
     */
    public function getUserSessions(int $userId, ?string $status = null): array
    {
        $query = GameSession::find()
            ->where(['organizer_id' => $userId])
            ->orderBy(['scheduled_at' => SORT_DESC]);

        if ($status) {
            $query->andWhere(['status' => $status]);
        }

        return $query->all();
    }

    /**
     * Отправка уведомлений подписчикам игры
     *
     * @param GameSession $session Сессия
     */
    private function notifySubscribers(GameSession $session): void
    {
        Yii::$app->queue->push(new SendGameNotificationJob([
            'sessionId' => $session->id,
            'gameId' => $session->game_id,
            'statusLabel' => $session->statusLabel,
            'sessionDate' => $session->scheduled_at,
        ]));
    }

    /**
     * Обработка изменения статуса сессии
     *
     * @param GameSession $session Сессия
     * @param string $oldStatus Старый статус
     * @param string $newStatus Новый статус
     */
    private function handleStatusChange(GameSession $session, string $oldStatus, string $newStatus): void
    {
        if ($newStatus !== $oldStatus) {
            $this->notifySubscribers($session);
        }
    }

    /**
     * Отменяет запланированные сессии, которые должны были начаться до сегодняшнего дня
     *
     * @return int Количество обновлённых записей
     * @throws \Throwable
     */
    public function updateExpiredSessions(): int
    {
        $db = GameSession::getDb();
        $now = date('Y-m-d H:i:s');

        return $db->transaction(function ($db) use ($now) {
            $query = $this->getStalePlannedQuery();
            $condition = $query->where;

            // Лог
            $selectSql = (new Query())
                ->select([
                    'session_id' => 'id',
                    'old_status' => 'status',
                    'new_status' => new Expression(':newStatus', [':newStatus' => GameSession::STATUS_CANCELLED]),
                    'changed_at' => new Expression(':now', [':now' => $now]),
                ])
                ->from(GameSession::tableName())
                ->where($condition)
                ->createCommand()->rawSql;

            $db->createCommand("INSERT INTO {{%game_session_log}} (session_id, old_status, new_status, changed_at) $selectSql")
                ->execute();

            // Обновление статуса
            return $db->createCommand()
                ->update(GameSession::tableName(), ['status' => GameSession::STATUS_CANCELLED], $condition)
                ->execute();
        });
    }

    public function findModel($id): ?GameSession
    {
        if (($model = GameSession::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }

    /**
     * Loads data from GameSessionForm into GameSession
     *
     * @param GameSession $session The GameSession object to load data into
     * @param GameSessionForm $form The GameSessionForm object containing the data to load
     */

    /**
     * Поиск количества просроченных сессий
     */
    public function findStalePlannedCount(): int
    {
        return (int)$this->getStalePlannedQuery()->count();
    }

    private function getStalePlannedQuery(): Query
    {
        return GameSession::find()->where([
            'and',
            ['status' => GameSession::STATUS_PLANNED],
            ['<', 'scheduled_at', date('Y-m-d H:i:s')],
        ]);
    }
}
