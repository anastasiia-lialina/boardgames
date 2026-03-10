<?php

namespace app\services;

use app\jobs\SendReviewNotificationJob;
use app\models\forms\ReviewForm;
use app\models\user\Review;
use app\models\user\User;
use Yii;
use yii\base\Exception;

/**
 * Сервис для управления отзывами
 */
class ReviewService
{
    /**
     * Создание нового отзыва
     * @throws Exception При ошибках валидации
     * @throws \Exception
     */
    public function createReview(ReviewForm $form): bool
    {
        $review = new Review();
        $review->user_id = $form->user_id;
        $review->game_id = $form->game_id;
        $review->rating = $form->rating;
        $review->comment = $form->comment;

        if ($review->save()) {
            $this->queueModerationNotification($review);
            return true;
        }

        return false;
    }

    /**
     * Одобрение отзыва
     * @throws Exception
     * @throws \Exception
     */
    public function approveReview(int $reviewId): bool
    {
        $review = Review::findOne($reviewId);

        if (!$review) {
            throw new \Exception(Yii::t('app', 'Review not found.'));
        }

        $review->is_approved = true;

        if (!$review->save(false, ['is_approved'])) {
            throw new \Exception(Yii::t('app', 'Failed to approve review.'));
        }

        $this->queueApprovalNotification($review);

        return true;
    }

    /**
     * Отклонение отзыва
     * @throws Exception|\Throwable
     */
    public function rejectReview(int $reviewId): bool
    {
        $review = Review::findOne($reviewId);

        if (!$review) {
            throw new \Exception(Yii::t('app', 'Review not found.'));
        }

        if (!$review->delete()) {
            throw new \Exception(Yii::t('app', 'Failed to reject review.'));
        }

        $this->queueRejectionNotification($review);

        return true;
    }

    /**
     * Получить средний рейтинг игры
     */
    public function getAverageRating($gameId): float
    {
        $key = "game:{$gameId}:rating";

        return Yii::$app->cache->getOrSet($key, function () use ($gameId) {
            return Review::find()
                ->where(['game_id' => $gameId, 'is_approved' => true])
                ->average('rating') ?? 0.0;
        }, 300);
    }

    /**
     * Получить количество одобренных отзывов
     */
    public function getReviewsCount(int $gameId): int
    {
        return Review::find()
            ->where(['game_id' => $gameId, 'is_approved' => true])
            ->count();
    }

    /**
     * Отправка уведомления о новом отзыве на модерацию
     */
    private function queueModerationNotification(Review $review): void
    {
        // Находим всех модераторов и админов
        $moderators = User::find()
            ->where(['or',
                ['role' => 'admin'],
                ['role' => 'moderator'],
            ])
            ->column();

        foreach ($moderators as $userId) {
            Yii::$app->queue->push(new SendReviewNotificationJob([
                'reviewId' => $review->id,
                'userId' => $userId,
                'type' => 'moderation_needed',
            ]));
        }
    }

    /**
     * Отправка уведомления об одобрении отзыва
     */
    private function queueApprovalNotification(Review $review): void
    {
        Yii::$app->queue->push(new SendReviewNotificationJob([
            'reviewId' => $review->id,
            'userId' => $review->user_id,
            'type' => 'review_approved',
        ]));
    }

    /**
     * Отправка уведомления об отклонении отзыва
     */
    private function queueRejectionNotification(Review $review): void
    {
        Yii::$app->queue->push(new SendReviewNotificationJob([
            'reviewId' => $review->id,
            'userId' => $review->user_id,
            'type' => 'review_rejected',
        ]));
    }

    /**
     * Форматирование ошибок валидации
     */
    public function deleteReview(int $reviewId): bool
    {
        $review = Review::findOne($reviewId);

        if (!$review) {
            throw new \Exception(Yii::t('app', 'Review not found.'));
        }

        return $review->delete() !== false;
    }

    private function formatValidationErrors(array $errors): string
    {
        $messages = [];
        foreach ($errors as $attribute => $errorList) {
            $label = Review::instance()->getAttributeLabel($attribute);
            foreach ($errorList as $error) {
                $messages[] = Yii::t('app', '{attribute}: {error}', [
                    'attribute' => $label,
                    'error' => $error,
                ]);
            }
        }
        return implode('; ', $messages);
    }

}
