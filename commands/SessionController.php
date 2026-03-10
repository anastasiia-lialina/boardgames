<?php

namespace app\commands;

use app\services\GameSessionService;
use Throwable;
use yii\console\Controller;
use yii\console\ExitCode;
use yii\helpers\BaseConsole;

/**
 * Управление игровыми сессиями
 */
class SessionController extends Controller
{
    public function __construct(
        $id,
        $module,
        private readonly GameSessionService $gameSessionService,
        $config = []
    ) {
        parent::__construct($id, $module, $config);
    }

    /**
     * Отмена просроченных запланированных сессий (которые должны были начаться до сегодняшнего для)
     */
    public function actionUpdateStatus(): int
    {
        try {
            $count = $this->gameSessionService->updateExpiredSessions();

            if ($count > 0) {
                $this->stdout("Успешно отменено просроченных сессий: $count\n", BaseConsole::FG_GREEN);
            } else {
                $this->stdout("Нет просроченных сессий для отмены\n", BaseConsole::FG_YELLOW);
            }
        } catch (Throwable $e) {
            $this->stderr("Ошибка при выполнении: " . $e->getMessage() . "\n", BaseConsole::FG_RED);
            return ExitCode::UNSPECIFIED_ERROR;
        }
        return ExitCode::OK;
    }

    /**
     * Просмотр сессий, которые подлежат отмене (для отладки)
     */
    public function actionCheckStale(): void
    {
        $count = $this->gameSessionService->findStalePlannedCount();

        if ($count > 0) {
            $this->stdout("Найдено просроченных сессий: $count\n", BaseConsole::FG_CYAN);
            $this->stdout("Запустите: php yii session/update-status\n", BaseConsole::FG_YELLOW);
        } else {
            $this->stdout("Просроченных сессий нет.\n", BaseConsole::FG_GREEN);
        }
    }
}
