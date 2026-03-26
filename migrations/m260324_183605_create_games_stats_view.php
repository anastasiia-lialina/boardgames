<?php

use yii\db\Migration;

class m260324_183605_create_games_stats_view extends Migration
{
    /**
     * {@inheritdoc}
     *
     */
    public function safeUp()
    {
        $this->createIndex('idx_game_sessions_status', 'game_sessions', 'status');
        $this->createIndex('idx_game_subscriptions_active', 'game_subscriptions', 'is_active');
        $this->createIndex('idx_reviews_approved', 'reviews', 'is_approved');

        $viewSql = "
            CREATE MATERIALIZED VIEW mv_games_stats AS
            WITH
            -- Агрегация активности по дням
            g_daily AS (SELECT created_at::date as day, COUNT(*) as cnt FROM games WHERE created_at > CURRENT_DATE - INTERVAL '30 days' GROUP BY 1),
            s_daily AS (SELECT created_at::date as day, COUNT(*) as cnt FROM game_sessions WHERE created_at > CURRENT_DATE - INTERVAL '30 days' GROUP BY 1),
            sub_daily AS (SELECT created_at::date as day, COUNT(*) as cnt FROM game_subscriptions WHERE created_at > CURRENT_DATE - INTERVAL '30 days' GROUP BY 1),
            r_daily AS (SELECT created_at::date as day, COUNT(*) as cnt FROM reviews WHERE created_at > CURRENT_DATE - INTERVAL '30 days' GROUP BY 1),

            daily_stats AS (
                SELECT
                    COALESCE(g.day, s.day, sub.day, r.day) as day,
                    COALESCE(g.cnt, 0) as new_games,
                    COALESCE(s.cnt, 0) as new_sessions,
                    COALESCE(sub.cnt, 0) as new_subscriptions,
                    COALESCE(r.cnt, 0) as new_reviews
                FROM g_daily g
                FULL OUTER JOIN s_daily s ON s.day = g.day
                FULL OUTER JOIN sub_daily sub ON sub.day = COALESCE(g.day, s.day)
                FULL OUTER JOIN r_daily r ON r.day = COALESCE(g.day, s.day, sub.day)
            )
            
            SELECT
                (SELECT COUNT(*) FROM games) AS total_games,
                s.total_sessions,
                s.completed_sessions,
                s.cancelled_sessions,
                (SELECT COUNT(*) FROM game_subscriptions) as total_subscriptions,
                r.total_reviews,
                r.avg_rating,
                
                -- Распределение рейтинга
                (SELECT json_agg(t) FROM (
                    SELECT rating, COUNT(*) as count,
                    ROUND(COUNT(*) * 100.0 / SUM(COUNT(*)) OVER(), 2) as percent
                    FROM reviews GROUP BY rating ORDER BY rating DESC
                ) t) as reviews_distribution,
                
                -- Популярные игры по сессиям (Берем статус из ПОСЛЕДНЕЙ сессии игры)
                (SELECT json_agg(t) FROM (
                    SELECT
                        g.title,
                        COUNT(gs.id) as sessions_count,
                        (SELECT status FROM game_sessions WHERE game_id = g.id ORDER BY created_at DESC LIMIT 1) as status
                    FROM games g
                    LEFT JOIN game_sessions gs ON gs.game_id = g.id
                    GROUP BY g.id, g.title
                    ORDER BY sessions_count DESC LIMIT 5
                ) t) as popular_games_sessions,
                
                -- Популярные игры по подпискам
                (SELECT json_agg(t) FROM (
                    SELECT g.title, COUNT(sub.id) as total_subs, COUNT(*) FILTER (WHERE sub.is_active = true) as active_subs
                    FROM games g
                    INNER JOIN game_subscriptions sub ON sub.game_id = g.id
                    GROUP BY g.id, g.title
                    ORDER BY total_subs DESC LIMIT 5
                ) t) as popular_games_subscriptions,
                
                -- Активность
                (SELECT json_agg(ds ORDER BY day DESC) FROM daily_stats ds) as recent_activity
                
            FROM
                (SELECT
                    COUNT(*) as total_sessions,
                    COUNT(*) FILTER (WHERE status = 'completed') as completed_sessions,
                    COUNT(*) FILTER (WHERE status = 'cancelled') as cancelled_sessions
                FROM game_sessions) s,
                (SELECT
                    COUNT(*) as total_reviews,
                    COALESCE(AVG(rating) FILTER (WHERE is_approved = true), 0) as avg_rating
                FROM reviews) r;
        ";

        $this->execute($viewSql);

        $this->execute("CREATE UNIQUE INDEX idx_mv_games_stats_unique ON mv_games_stats ((1))");
    }

    public function safeDown()
    {
        $this->execute("DROP MATERIALIZED VIEW IF EXISTS mv_games_stats CASCADE");
        $this->dropIndex('idx_game_sessions_status', 'game_sessions');
        $this->dropIndex('idx_game_subscriptions_active', 'game_subscriptions');
        $this->dropIndex('idx_reviews_approved', 'reviews');
    }
}
