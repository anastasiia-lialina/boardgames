<?php

namespace app\notifications;

use webzop\notifications\Notification;

/**
 * Уведомление о том, что был добавлен новый отзыв.
 */
class NewReviewModerationNotification extends Notification
{
    public string $reviewId;

    public function getTitle(): string
    {
        return \Yii::t('app', 'A new review has been added.');
    }

    public function getRoute(): array
    {
        return ['/review/view', 'id' => $this->reviewId];
    }
}
