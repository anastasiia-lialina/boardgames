<?php

namespace app\notifications;

use webzop\notifications\Notification;

class SessionNotification extends Notification
{
    public string $gameName;
    public string $sessionDate;
    public string $statusLabel;

    public function getTitle()
    {
        return \Yii::t('app', 'Game "{game}": session on {date} has been {status}', [
            'game' => $this->gameName,
            'date' => $this->sessionDate,
            'status' => $this->statusLabel,
        ]);
    }

    public function getRoute()
    {
        return ['/game-session/view', 'id' => $this->key];
    }
}
