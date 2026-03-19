<?php

namespace app\listeners;

use app\jobs\NewReviewModerationJob;
use app\jobs\ReviewApprovedJob;
use app\jobs\ReviewRejectedJob;
use app\models\user\Review;
use yii\base\Event;
use yii\db\Query;

/**
 * Обработчик событий для уведомлений об отзывах.
 */
class ReviewNotificationListener
{
    public static function onModerationNeeded(Event $event): void
    {
        /** @var Review $review */
        $review = $event->sender;

        $moderatorIds = (new Query())
            ->select('user_id')
            ->from(\Yii::$app->authManager->assignmentTable)
            ->where(['item_name' => ['admin', 'moderator']])
            ->column()
        ;

        foreach ($moderatorIds as $userId) {
            \Yii::$app->queue->push(new NewReviewModerationJob([
                'reviewId' => $review->id,
                'userId' => $userId,
            ]));
        }
    }

    public static function onApproved(Event $event): void
    {
        /** @var Review $review */
        $review = $event->sender;

        \Yii::$app->queue->push(new ReviewApprovedJob([
            'gameId' => $review->game_id,
            'gameName' => $review->game->title,
            'userId' => $review->user_id,
        ]));
    }

    public static function onRejected(Event $event): void
    {
        /** @var Review $review */
        $review = $event->sender;

        \Yii::$app->queue->push(new ReviewRejectedJob([
            'gameId' => $review->game_id,
            'gameName' => $review->game->title,
            'userId' => $review->user_id,
        ]));
    }
}
