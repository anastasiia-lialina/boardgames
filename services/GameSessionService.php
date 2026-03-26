<?php

namespace app\services;

use app\jobs\MassSessionsCancelledJob;
use app\models\forms\GameSessionForm;
use app\models\game\GameSession;
use yii\base\Event;
use yii\db\Exception;
use yii\db\Query;
use yii\web\NotFoundHttpException;

/**
 * Сервис для управления игровыми сессиями.
 */
class GameSessionService extends BaseService
{
    /**
     * Создание новой игровой сессии.
     *
     * @param int $organizerId ID организатора
     *
     * @return GameSession Созданная сессия
     *
     * @throws Exception
     */
    public function createSession(GameSessionForm $form, int $organizerId): GameSession
    {
        $session = new GameSession();
        $session->organizer_id = $organizerId;
        $this->load($session, $form);

        if ($session->save()) {
            Event::trigger(GameSession::class, GameSession::EVENT_SESSION_CREATED, new Event(['sender' => $session]));
            $this->refreshStats();
        }

        return $session;
    }

    /**
     * Обновление сессии.
     *
     * @param int $sessionId ID сессии
     *
     * @return GameSession Обновленная сессия
     *
     * @throws NotFoundHttpException
     * @throws \Exception
     */
    public function updateSession(int $sessionId, GameSessionForm $form): GameSession
    {
        $session = $this->findModel(GameSession::class, $sessionId);

        $oldStatus = $session->status;

        $this->load($session, $form);

        if ($session->save()) {
            if ($oldStatus !== $session->status) {
                Event::trigger(GameSession::class, GameSession::EVENT_STATUS_CHANGED, new Event(['sender' => $session]));
                $this->refreshStats();
            }
        }

        return $session;
    }

    /**
     * Удаление сессии.
     *
     * @param int $sessionId ID сессии
     *
     * @return bool Успешность операции
     *
     * @throws \Exception
     */
    public function deleteSession(int $sessionId): bool
    {
        $session = $this->findModel(GameSession::class, $sessionId);

        if (!$session->delete()) {
            return false;
        }

        $this->refreshStats();

        return true;
    }

    /**
     * Отменяет запланированные сессии, которые должны были начаться до сегодняшнего дня.
     *
     * @return int Количество обновлённых записей
     *
     * @throws \Exception|\Throwable
     */
    public function updateExpiredSessions(): int
    {
        $db = GameSession::getDb();

        return $db->transaction(function ($db) {
            $now = date('Y-m-d H:i:s');

            $query = $this->getStalePlannedQuery()->select('id');
            $command = $query->createCommand($db);

            $params = $command->params;
            $command->setSql($command->getSql() . ' FOR UPDATE');
            $command->bindValues($params);

            $sessionIds = $command->queryColumn();

            if (empty($sessionIds)) {
                return 0;
            }

            $logRows = array_map(fn($id) => [
                (int) $id,
                GameSession::STATUS_PLANNED,
                GameSession::STATUS_CANCELLED,
                $now,
            ], $sessionIds);

            $db->createCommand()
                ->batchInsert(
                    '{{%game_session_log}}',
                    ['session_id', 'old_status', 'new_status', 'changed_at'],
                    $logRows
                )->execute()
            ;

            $count = $db->createCommand()
                ->update(
                    GameSession::tableName(),
                    ['status' => GameSession::STATUS_CANCELLED],
                    ['id' => $sessionIds]
                )->execute()
            ;

            \Yii::$app->queue->push(new MassSessionsCancelledJob([
                'sessionIds' => array_map('intval', $sessionIds),
            ]));

            return $count;
        });
    }

    /**
     * Поиск количества просроченных сессий.
     */
    public function findStalePlannedCount(): int
    {
        return (int) $this->getStalePlannedQuery()->count();
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
