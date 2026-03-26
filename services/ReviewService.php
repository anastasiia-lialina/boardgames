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
    private const RATING_CACHE_KEY_PATTERN = 'game:%d:rating';

    /**
     * Создание нового отзыва.
     *
     * @param ReviewForm $form
     * @return bool
     * @throws \Throwable
     */
    public function createReview(ReviewForm $form): bool
    {
        $db = Review::getDb();

        return $db->transaction(function ($db) use ($form) {
            $review = new Review();
            $review->user_id = $form->user_id;
            $review->game_id = $form->game_id;
            $review->rating = $form->rating;
            $review->comment = $form->comment;

            if ($review->save()) {
                Event::trigger(Review::class, Review::EVENT_MODERATION_NEEDED, new Event([
                    'sender' => $review,
                ]));
                $this->refreshStats();

                $this->deleteRatingCache($form->game_id);

                return true;
            }

            return false;
        });
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

        $this->refreshStats();

        return $review;
    }

    /**
     * Одобрение отзыва.
     *
     * @param int $reviewId
     * @return bool
     * @throws \Throwable
     */
    public function approveReview(int $reviewId): bool
    {
        $db = Review::getDb();

        return $db->transaction(function ($db) use ($reviewId) {
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
            $this->refreshStats();

            $this->deleteRatingCache($review->game_id);

            return true;
        });
    }

    /**
     * Отклонение отзыва.
     *
     * @throws Exception|\Throwable
     */
    public function rejectReview(int $reviewId): bool
    {
        $db = Review::getDb();

        return $db->transaction(function ($db) use ($reviewId) {
            $review = Review::findOne($reviewId);

            if (!$review) {
                throw new \Exception(\Yii::t('app', 'Review not found.'));
            }

            $gameId = $review->game_id;

            if (!$review->delete()) {
                throw new \Exception(\Yii::t('app', 'Failed to reject review.'));
            }

            Event::trigger(Review::class, Review::EVENT_REJECTED, new Event([
                'sender' => $review,
            ]));
            $this->refreshStats();

            $this->deleteRatingCache($gameId);

            return true;
        });
    }

    /**
     * Получить средний рейтинг игры.
     */
    public function getAverageRating(int $gameId): float
    {
        $key = $this->getRatingCacheKey($gameId);

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

    /**
     * Сбрасывает кэш рейтинга игры
     * @param int $gameId
     * @return void
     */
    private function deleteRatingCache(int $gameId): void
    {

        \Yii::$app->cache->delete($this->getRatingCacheKey($gameId));
    }

    /**
     * @param int $gameId
     * @return string
     */
    private function getRatingCacheKey(int $gameId): string
    {
        return str_replace('%d', $gameId, self::RATING_CACHE_KEY_PATTERN);
    }
}
