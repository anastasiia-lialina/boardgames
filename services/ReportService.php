<?php

namespace app\services;

use app\models\game\Game;
use app\models\game\GameSession;
use app\models\game\GameSubscription;
use app\models\user\Review;
use yii\base\Event;
use yii\db\Query;

/**
 * Сервис для отчетов
 */
class ReportService extends BaseService
{
    public const EVENT_REFRESH_STATS = 'refreshStats';

    private const MATERIALIZED_VIEW_NAME = 'mv_games_stats';

    private const DEFAULT_GAMES_STATS = [
        'total_games' => 0,
        'active_games' => 0,
        'total_sessions' => 0,
        'completed_sessions' => 0,
        'cancelled_sessions' => 0,
        'planned_sessions' => 0,
        'total_subscriptions' => 0,
        'active_subscriptions' => 0,
        'total_reviews' => 0,
        'approved_reviews' => 0,
        'avg_rating' => 0,
    ];

    /**
     * Получает общую статистику по играм
     */
    public function getGamesStats(): array
    {
        $stats = (new \yii\db\Query())
            ->from(self::MATERIALIZED_VIEW_NAME)
            ->one();

        return $stats ?: self::DEFAULT_GAMES_STATS;
    }

    public static function refresh(bool $concurrently = true): void
    {
        $concurrentlyStr = $concurrently ? "CONCURRENTLY" : "";
        $query = "REFRESH MATERIALIZED VIEW" . $concurrentlyStr . self::MATERIALIZED_VIEW_NAME;
        \Yii::$app->db->createCommand($query)->execute();
    }

    public function getExportData(): string
    {
        $stats = $this->getGamesStats();
        $stream = fopen('php://temp', 'r+');

        // Добавляем BOM для Excel (Кириллица)
        fprintf($stream, chr(0xEF) . chr(0xBB) . chr(0xBF));

        // Последовательно вызываем рендер каждого блока
        $this->writeGeneralStats($stream, $stats);
        $this->writePopularGames($stream, $stats['popular_games_sessions'] ?? []);
        $this->writeActivity($stream, $stats['recent_activity'] ?? []);

        rewind($stream);
        return stream_get_contents($stream);
    }

    private function writeGeneralStats($stream, array $stats): void
    {
        fputcsv($stream, [\Yii::t('app', 'General statistics for games')]);
        fputcsv($stream, [\Yii::t('app', 'Total games'), $stats['total_games']]);
        fputcsv($stream, [\Yii::t('app', 'Total sessions'), $stats['total_sessions']]);
        fputcsv($stream, [\Yii::t('app', 'Average rating'), number_format($stats['avg_rating'], 2)]);
        fputcsv($stream, []);
    }

    private function writePopularGames($stream, $data): void
    {
        $games = is_string($data) ? json_decode($data, true) : $data;
        if (empty($games)) {
            return;
        }

        fputcsv($stream, [\Yii::t('app', 'Popular games by sessions')]);
        fputcsv($stream, [
            \Yii::t('app', 'Title'),
            \Yii::t('app', 'Sessions'),
            \Yii::t('app', 'Status of last session'),
        ]);

        foreach ($games as $game) {
            $status = GameSession::getStatusLabels()[$game['status']] ?? "-";
            fputcsv($stream, [$game['title'], $game['sessions_count'], $status]);
        }
        fputcsv($stream, []);
    }

    private function writeActivity($stream, $data): void
    {
        $activity = is_string($data) ? json_decode($data, true) : $data;
        if (empty($activity)) {
            return;
        }

        fputcsv($stream, [\Yii::t('app', 'Activity for last 30 days')]);
        fputcsv($stream, [\Yii::t('app', 'Date'), \Yii::t('app', 'Games'), \Yii::t('app', 'Sessions')]);

        foreach ($activity as $day) {
            fputcsv($stream, [$day['day'], $day['new_games'], $day['new_sessions']]);
        }
    }
}
