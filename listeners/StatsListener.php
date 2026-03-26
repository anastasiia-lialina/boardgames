<?php

namespace app\listeners;

use app\jobs\RefreshStatsJob;
use yii\base\Event;

/**
 * Слушатель событий для обновления статистики
 */
class StatsListener
{
    private const CACHE_KEY = 'stats_update_pending';
    public static function onRefreshStats(Event $event): void
    {
        // Проверяем, не планировали ли мы обновление в последние 30-60 секунд
        if (!\Yii::$app->cache->get(self::CACHE_KEY)) {
            \Yii::$app->cache->set(self::CACHE_KEY, true, 60);

            \Yii::$app->queue->delay(30)->push(new RefreshStatsJob());
        }
    }
}
