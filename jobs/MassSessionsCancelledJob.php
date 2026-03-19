<?php

namespace app\jobs;

use app\models\game\GameSession;
use yii\base\BaseObject;
use yii\base\Event;
use yii\queue\JobInterface;

/**
 * Job для массового обновления статуса сессии.
 */
class MassSessionsCancelledJob extends BaseObject implements JobInterface
{
    public array $sessionIds = [];

    public function execute($queue): void
    {
        if (empty($this->sessionIds)) {
            return;
        }

        $count = count($this->sessionIds);
        \Yii::info("Начало массового уведомления об отмене сессий: {$count}", 'queue');

        // Используем each() для экономии памяти и with() для исключения N+1
        $query = GameSession::find()
            ->where(['id' => $this->sessionIds])
            ->with(['game', 'organizer'])
        ;

        foreach ($query->each(100) as $session) {
            // Триггерим событие — Listener подхватит и положит конкретные уведомления в очередь
            Event::trigger(GameSession::class, GameSession::EVENT_STATUS_CHANGED, new Event([
                'sender' => $session,
            ]));
        }

        \Yii::info('Массовое уведомление завершено', 'queue');
    }
}
