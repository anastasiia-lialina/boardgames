<?php

namespace app\notifications;

use webzop\notifications\Notification;

/**
 * Уведомление о том, что отзыв был опубликован.
 */
class ReviewApprovedNotification extends Notification
{
    public string $gameName;

    public function getTitle(): string
    {
        return \Yii::t('app', 'Your review for "{game}" has been approved and published', [
            'game' => $this->gameName,
        ]);
    }

    public function getRoute(): array
    {
        return ['/game/view', 'id' => $this->key];
    }
}
