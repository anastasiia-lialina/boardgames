<?php

declare(strict_types=1);

namespace app\jobs;

use Yii;
use yii\base\BaseObject;
use yii\queue\RetryableJobInterface;
use app\notifications\ReviewNotification;

class SendReviewNotificationJob extends BaseObject implements RetryableJobInterface
{
    public int $gameId;
    public string $gameName;

    public bool $isApproved;

    public int $userId;

    public int $maxAttempts = 3;

    public int $ttr = 300;

    public function execute($queue)
    {
        Yii::info("Уведомление об одобренном отзыве для User {$this->userId} ---", 'queue');

        ReviewNotification::create($this->gameId, [
            'userId'   => $this->userId,
            'gameName' => $this->gameName,
            'isApproved'   => $this->isApproved,
        ])->send();

        Yii::info("Уведомление об отзыве отправлено", 'queue');
    }

    public function getTtr()
    {
        return $this->ttr;
    }

    /**
     * @inheritdoc
     * Время до следующей попытки
     */
    public function getDelay($attempt, $exception)
    {
        return (60 * pow(2, $attempt - 1));
    }

    public function canRetry($attempt, $error)
    {
        return $attempt < $this->maxAttempts;
    }

}
