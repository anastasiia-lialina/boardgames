<?php

namespace app\notifications;

use app\models\user\Review;
use Yii;
use webzop\notifications\Notification;

/**
 * Уведомление о том, что отзыв был опубликован
 **/
class ReviewNotification extends Notification
{
    public string $gameName;
    public bool $isApproved;
    public function getTitle()
    {
        if ($this->isApproved === true) {
            return Yii::t('app', 'Your review for "{game}" has been approved and published', [
                'game' => $this->gameName,
            ]);
        }

        return Yii::t('app', 'Your review for "{game}" was rejected by a moderator', [
            'game' => $this->gameName,
        ]);
    }

    /**
     * @inheritdoc
     */
    public function getRoute()
    {
        return ['/game/view', 'id' => $this->key];
    }
}