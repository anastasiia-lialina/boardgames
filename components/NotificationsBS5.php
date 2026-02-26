<?php

namespace app\components;

use Yii;
use yii\helpers\Html;
use webzop\notifications\widgets\Notifications;

/**
 * Bootstrap 5 компонент уведомлений
 */
class NotificationsBS5 extends Notifications
{
    protected function renderNavbarItem(): string
    {
        $count = self::getCountUnseen();

        $title = Html::tag('span', Yii::t('modules/notifications', 'Notifications'), ['class' => 'me-1']);

        $badge = Html::tag('span', $count, [
            'class' => 'badge rounded-pill bg-danger notifications-count',
            'data-count' => $count,
            'style' => $count > 0 ? '' : 'display: none;'
        ]);

        $hidden = Html::tag('div', '', ['class' => 'notifications-list d-none']);

        return $title . $badge . $hidden;
    }
}
