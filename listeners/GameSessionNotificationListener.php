<?php

namespace app\listeners;

use app\jobs\SendGameNotificationJob;
use app\models\game\GameSession;
use yii\base\Event;

/**
 * Обработчик событий для игровых сессий.
 */
class GameSessionNotificationListener
{
    public static function onSessionChanged(Event $event): void
    {
        /** @var GameSession $session */
        $session = $event->sender;

        \Yii::$app->queue->push(new SendGameNotificationJob([
            'sessionId' => $session->id,
            'gameId' => $session->game_id,
            'statusLabel' => $session->statusLabel,
            'sessionDate' => $session->scheduled_at,
        ]));
    }
}
