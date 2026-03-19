<?php

namespace app\services;

use app\exception\ServiceException;
use app\models\forms\ReviewForm;
use app\models\user\Review;
use yii\base\Event;
use yii\base\Exception;

/**
 * Сервис для управления отзывами.
 */
class ReviewService extends BaseService
{
    /**
     * Создание нового отзыва.
     *
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
            Event::trigger(Review::class, Review::EVENT_MODERATION_NEEDED, new Event([
                'sender' => $review,
            ]));

            return true;
        }

        return false;
    }

    /**
     * @throws ServiceException
     * @throws \Exception
     */
    public function updateReview(int $id, array $data): Review
    {
        $review = $this->findModel(Review::class, $id);
        $review->load($data);

        if (!$review->save()) {
            throw new ServiceException($review);
        }

        return $review;
    }

    /**
     * Одобрение отзыва.
     *
     * @throws Exception
     * @throws \Exception
     */
    public function approveReview(int $reviewId): bool
    {
        $review = Review::findOne($reviewId);

        if (!$review) {
            throw new \Exception(\Yii::t('app', 'Review not found.'));
        }

        $review->is_approved = true;

        if (!$review->save(false, ['is_approved'])) {
            throw new \Exception(\Yii::t('app', 'Failed to approve review.'));
        }

        Event::trigger(Review::class, Review::EVENT_APPROVED, new Event([
            'sender' => $review,
        ]));

        return true;
    }

    /**
     * Отклонение отзыва.
     *
     * @throws Exception|\Throwable
     */
    public function rejectReview(int $reviewId): bool
    {
        $review = Review::findOne($reviewId);

        if (!$review) {
            throw new \Exception(\Yii::t('app', 'Review not found.'));
        }

        if (!$review->delete()) {
            throw new \Exception(\Yii::t('app', 'Failed to reject review.'));
        }

        Event::trigger(Review::class, Review::EVENT_REJECTED, new Event([
            'sender' => $review,
        ]));

        return true;
    }

    /**
     * Получить средний рейтинг игры.
     */
    public function getAverageRating(int $gameId): float
    {
        $key = "game:{$gameId}:rating";

        return \Yii::$app->cache->getOrSet($key, function () use ($gameId) {
            return Review::find()
                ->where(['game_id' => $gameId, 'is_approved' => true])
                ->average('rating') ?? 0.0
            ;
        }, 300);
    }

    /**
     * Получить количество одобренных отзывов.
     */
    public function getReviewsCount(int $gameId): int
    {
        return Review::find()
            ->where(['game_id' => $gameId, 'is_approved' => true])
            ->count()
        ;
    }
}
