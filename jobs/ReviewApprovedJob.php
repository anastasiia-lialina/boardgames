<?php

declare(strict_types=1);

namespace app\jobs;

use app\notifications\ReviewApprovedNotification;
use yii\base\BaseObject;
use yii\queue\RetryableJobInterface;

class ReviewApprovedJob extends BaseObject implements RetryableJobInterface
{
    public int $gameId;
    public string $gameName;

    public bool $type;

    public int $userId;

    public int $maxAttempts = 3;

    public int $ttr = 300;

    public function execute($queue): void
    {
        \Yii::info("Уведомление об одобренном отзыве для User {$this->userId} ---", 'queue');

        ReviewApprovedNotification::create($this->gameId, [
            'userId' => $this->userId,
            'gameName' => $this->gameName,
        ])->send();

        \Yii::info('Уведомление об отзыве отправлено', 'queue');
    }

    public function getTtr(): int
    {
        return $this->ttr;
    }

    public function getDelay($attempt, $exception): float|int
    {
        return 60 * pow(2, $attempt - 1);
    }

    public function canRetry($attempt, $error): bool
    {
        return $attempt < $this->maxAttempts;
    }
}
