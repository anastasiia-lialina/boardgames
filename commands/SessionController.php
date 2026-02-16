<?php

namespace app\commands;

use yii\console\Controller;
use app\models\GameSessions;
use Yii;
use yii\helpers\Console;

/**
 * Session controller for managing game sessions
 */
class SessionController extends Controller
{
    /**
     * Обновить статусы прошедших сессий
     * Крон каждый час:
     *    0 * * * * /path/to/php yii session/update-status
     *
     */
    public function actionUpdateStatus(): int
    {
        $count = GameSessions::updateExpiredSessions();

        if ($count > 0) {
            $this->stdout("Обновлено статусов сессий: $count\n", Console::FG_GREEN);
            Yii::info("Updated $count session statuses", 'application');
        } else {
            $this->stdout("Нет сессий для обновления\n", Console::FG_YELLOW);
        }

        return 0;
    }

    /**
     * Показать сессии с устаревшим статусом (для отладки)
     * @return void
     */
    public function actionCheckStale(): void
    {
        $now = date('Y-m-d H:i:s');

        // Запланированные сессии, которые уже должны были начаться
        $stalePlanned = GameSessions::find()
            ->where(['status' => GameSessions::STATUS_PLANNED])
            ->andWhere(['<=', 'scheduled_at', $now])
            ->count();

        // Активные сессии, которые уже должны были завершиться
        $staleActive = GameSessions::find()
            ->where(['status' => GameSessions::STATUS_ACTIVE])
            ->andWhere(['<', 'scheduled_at', $now])
            ->count();

        $this->stdout("Сессии со статусом 'Запланировано', но дата уже наступила: $stalePlanned\n");
        $this->stdout("Сессии со статусом 'Активно', но дата уже прошла: $staleActive\n");

        if ($stalePlanned + $staleActive > 0) {
            $this->stdout("\nЗапустите: php yii session/update-status\n", Console::FG_GREEN);
        }
    }
}