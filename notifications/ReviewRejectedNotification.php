<?php

namespace app\notifications;

use webzop\notifications\Notification;

class ReviewRejectedNotification extends Notification
{
    public string $gameName;

    public function getTitle(): string
    {
        return \Yii::t('app', 'Your review for "{game}" was rejected by a moderator', [
            'game' => $this->gameName,
        ]);
    }

    public function getRoute(): array
    {
        return ['/game/view', 'id' => $this->key];
    }
}
