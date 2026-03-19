<?php

namespace app\bootstrap;

use app\listeners\GameSessionNotificationListener;
use app\listeners\ReviewNotificationListener;
use app\models\game\GameSession;
use app\models\user\Review;
use yii\base\BootstrapInterface;
use yii\base\Event;

class EventBootstrap implements BootstrapInterface
{
    public function bootstrap($app)
    {
        Event::on(Review::class, Review::EVENT_MODERATION_NEEDED, [ReviewNotificationListener::class, 'onModerationNeeded']);
        Event::on(Review::class, Review::EVENT_APPROVED, [ReviewNotificationListener::class, 'onApproved']);
        Event::on(Review::class, Review::EVENT_REJECTED, [ReviewNotificationListener::class, 'onRejected']);

        Event::on(GameSession::class, GameSession::EVENT_SESSION_CREATED, [GameSessionNotificationListener::class, 'onSessionChanged']);
        Event::on(GameSession::class, GameSession::EVENT_STATUS_CHANGED, [GameSessionNotificationListener::class, 'onSessionChanged']);
    }
}
