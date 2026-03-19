<?php

declare(strict_types=1);

namespace app\jobs;

use app\notifications\NewReviewModerationNotification;
use yii\base\BaseObject;
use yii\queue\RetryableJobInterface;

class NewReviewModerationJob extends BaseObject implements RetryableJobInterface
{
    private const KEY_PATTERN = 'new-review-%d-user-%d';
    public int $reviewId;

    public int $userId;

    public int $maxAttempts = 3;

    public int $ttr = 300;

    /**
     * @param mixed $queue
     *
     * @throws \Exception
     */
    public function execute($queue): void
    {
        \Yii::info("Уведомление о новом отзыве {$this->reviewId} для User {$this->userId} ---", 'queue');

        $key = sprintf(self::KEY_PATTERN, $this->reviewId, $this->userId);

        NewReviewModerationNotification::create($key, [
            'reviewId' => $this->reviewId,
            'userId' => $this->userId,
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
