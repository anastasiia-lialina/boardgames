<?php

namespace app\notifications;

use Yii;
use webzop\notifications\Notification;

class SessionNotification extends Notification
{
    public string $gameName;
    public string $sessionDate;
    public string $statusLabel;

    /**
     * @inheritdoc
     */
    public function getTitle()
    {
        return Yii::t('app', 'Game "{game}": session on {date} has been {status}', [
            'game' => $this->gameName,
            'date' => $this->sessionDate,
            'status' => $this->statusLabel
        ]);
    }

    /**
     * @inheritdoc
     */
    public function getRoute()
    {
        return ['/game-session/view', 'id' => $this->key];
    }
}