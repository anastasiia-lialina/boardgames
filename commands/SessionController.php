<?php

namespace app\commands;

use app\models\game\GameSessions;
use yii\console\Controller;
use yii\console\ExitCode;
use yii\helpers\Console;

/**
 * Управление игровыми сессиями
 */
class SessionController extends Controller
{
    /**
     * Отмена просроченных запланированных сессий (которые должны были начаться до сегодняшнего для)
     */
    public function actionUpdateStatus(): int
    {
        try {
            $count = GameSessions::updateExpiredSessions();

            if ($count > 0) {
                $this->stdout("Успешно отменено просроченных сессий: $count\n", Console::FG_GREEN);
            } else {
                $this->stdout("Нет просроченных сессий для отмены\n", Console::FG_YELLOW);
            }
        } catch (\Exception $e) {
            $this->stderr("Ошибка при выполнении: " . $e->getMessage() . "\n", Console::FG_RED);
            return ExitCode::UNSPECIFIED_ERROR;
        }
        return ExitCode::OK;
    }

    /**
     * Просмотр сессий, которые подлежат отмене (для отладки)
     */
    public function actionCheckStale(): void
    {
        $count = GameSessions::findStalePlannedCount();

        if ($count > 0) {
            $this->stdout("Найдено просроченных сессий: $count\n", Console::FG_CYAN);
            $this->stdout("Запустите: php yii session/update-status\n", Console::FG_YELLOW);
        } else {
            $this->stdout("Просроченных сессий нет.\n", Console::FG_GREEN);
        }
    }
}
