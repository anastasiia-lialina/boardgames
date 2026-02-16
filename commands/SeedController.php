<?php

namespace app\commands;

use app\models\game\GameSession;
use app\models\game\Game;
use app\models\user\Review;
use app\models\user\User;
use Yii;
use yii\console\Controller;

/**
 * Controller для генерации тестовых данных
 */
class SeedController extends Controller
{
    /**
     * Генерация юзеров
     */
    public function actionUsers()
    {
        $usersData = [
            [
                'username' => 'admin',
                'email' => 'admin@example.com',
                'password' => 'test',
                'role' => 'admin',
            ],
            [
                'username' => 'moderator',
                'email' => 'moderator@example.com',
                'password' => 'test',
                'role' => 'moderator',
            ],
            [
                'username' => 'user1',
                'email' => 'user1@example.com',
                'password' => 'test',
                'role' => 'user',
            ],
            [
                'username' => 'user2',
                'email' => 'user2@example.com',
                'password' => 'test',
                'role' => 'user',
            ],
        ];

        $created = 0;
        foreach ($usersData as $data) {
            // Проверяем, не существует ли пользователь
            $existing = User::findByUsername($data['username']);
            if ($existing) {
                $this->stdout("Пользователь {$data['username']} уже существует\n");
                continue;
            }

            $user = new User();
            $user->username = $data['username'];
            $user->email = $data['email'];
            $user->setPassword($data['password']);
            $user->generateAuthKey();
            $user->status = User::STATUS_ACTIVE;

            if ($user->save()) {
                $created++;
                $this->stdout("Создан пользователь: {$user->username} ({$data['role']})\n");

                // Назначаем роль
                $auth = Yii::$app->authManager;
                $role = $auth->getRole($data['role']);
                if ($role) {
                    $auth->assign($role, $user->id);
                    $this->stdout(" Роль '{$data['role']}' назначена\n");
                }
            } else {
                $this->stderr("Ошибка создания: {$data['username']}\n");
                $this->stderr(implode(', ', $user->getFirstErrors()) . "\n");
            }
        }

        $this->stdout("\n Создано пользователей: $created из " . count($usersData) . "\n");
    }

    /**
     * Генерация игр
     */
    public function actionGames($count = 10)
    {
        $gamesData = [
            [
                'title' => 'Катан',
                'description' => 'Всемирно известная экономическая настольная стратегия для 3–4 игроков, где участники заселяют остров, строя дороги, поселения и города из ресурсов (дерево, глина, руда, зерно, шерсть). Побеждает тот, кто первым наберет 10 победных очков, развиваясь, торгуясь и защищаясь от разбойников. ',
                'players_min' => 3,
                'players_max' => 4,
                'duration_min' => 60,
                'complexity' => 2.5,
                'year' => 1995,
            ],
            [
                'title' => 'Каркассон',
                'description' => 'Стратегическая игра о средневековой Франции. Строим города, дороги и монастыри.',
                'players_min' => 2,
                'players_max' => 5,
                'duration_min' => 35,
                'complexity' => 1.8,
                'year' => 2000,
            ],
            [
                'title' => '7 чудес',
                'description' => 'Стратегическая карточная игра о развитии цивилизации через 3 эпохи.',
                'players_min' => 3,
                'players_max' => 7,
                'duration_min' => 30,
                'complexity' => 2.3,
                'year' => 2010,
            ],
            [
                'title' => 'Кодекс',
                'description' => 'Абстрактная стратегическая игра с красивыми деревянными компонентами.',
                'players_min' => 2,
                'players_max' => 4,
                'duration_min' => 30,
                'complexity' => 2.0,
                'year' => 2004,
            ],
            [
                'title' => 'Декстериа',
                'description' => 'Динамичная игра на ловкость и реакцию с мешочками.',
                'players_min' => 2,
                'players_max' => 6,
                'duration_min' => 15,
                'complexity' => 1.5,
                'year' => 2010,
            ],
            [
                'title' => 'Азул',
                'description' => 'Стратегическая игра о создании мозаики из разноцветных плиток.',
                'players_min' => 2,
                'players_max' => 4,
                'duration_min' => 30,
                'complexity' => 2.0,
                'year' => 2017,
            ],
            [
                'title' => 'Сквозь века',
                'description' => 'Глубокая стратегическая игра о развитии цивилизации от каменного века до будущего.',
                'players_min' => 2,
                'players_max' => 5,
                'duration_min' => 120,
                'complexity' => 4.0,
                'year' => 2014,
            ],
            [
                'title' => 'Эволюция',
                'description' => 'Создайте уникальных животных с разными свойствами и выживите в дикой природе.',
                'players_min' => 2,
                'players_max' => 6,
                'duration_min' => 60,
                'complexity' => 2.2,
                'year' => 2010,
            ],
            [
                'title' => 'Диксит',
                'description' => 'Творческая игра на ассоциации с красивыми иллюстрациями.',
                'players_min' => 3,
                'players_max' => 6,
                'duration_min' => 30,
                'complexity' => 1.5,
                'year' => 2008,
            ],
            [
                'title' => 'Пандемия',
                'description' => 'Кооперативная игра, где игроки вместе борются с глобальными эпидемиями.',
                'players_min' => 2,
                'players_max' => 4,
                'duration_min' => 45,
                'complexity' => 2.5,
                'year' => 2008,
            ],
        ];

        $created = 0;
        foreach ($gamesData as $data) {
            $game = new Game();
            $game->attributes = $data;

            if ($game->save()) {
                $created++;
                $this->stdout("Создана игра: {$game->title}\n");
            } else {
                $this->stderr("Ошибка создания: {$game->title}\n");
                $this->stderr(implode(', ', $game->getFirstErrors()) . "\n");
            }
        }

        $this->stdout("\n Создано игр: $created из " . count($gamesData) . "\n");
    }

    /**
     * Генерация отзывов
     */
    public function actionReviews($count = 20)
    {
        $games = Game::find()->all();
        $userIds = $this->getUserIds();

        if (empty($games)) {
            $this->stderr("Нет игр в базе. Сначала создайте игры: yii seed/games\n");
            return 1;
        }

        $reviewsData = [
            ['rating' => 5, 'comment' => 'Отличная игра! Играем всей семьёй каждые выходные.'],
            ['rating' => 4, 'comment' => 'Очень интересная механика, но иногда затягивается.'],
            ['rating' => 5, 'comment' => 'Лучшая настолка из всех, что у нас есть!'],
            ['rating' => 3, 'comment' => 'Неплохо, но не для всех компаний.'],
            ['rating' => 5, 'comment' => 'Покупали на подарок — все в восторге!'],
            ['rating' => 4, 'comment' => 'Качественные компоненты, интересные правила.'],
            ['rating' => 2, 'comment' => 'Не наш формат, слишком много правил.'],
            ['rating' => 5, 'comment' => 'Идеально для вечеринок!'],
            ['rating' => 4, 'comment' => 'Затягивает с головой, время летит незаметно.'],
            ['rating' => 5, 'comment' => 'Рекомендую всем любителям стратегий.'],
        ];

        $created = 0;
        $approved = 0;

        for ($i = 0; $i < $count; $i++) {
            $game = $games[array_rand($games)];
            $reviewData = $reviewsData[array_rand($reviewsData)];

            $review = new Review();
            $review->detachBehavior('blameable');
            $review->game_id = $game->id;
            $review->user_id = $userIds[array_rand($userIds)];
            $review->rating = $reviewData['rating'];
            $review->comment = $reviewData['comment'];
            $review->is_approved = (rand(0, 10) > 3); // 70% одобрено

            if ($review->save()) {
                $created++;
                if ($review->is_approved) {
                    $approved++;
                }
                $this->stdout("Отзыв #{$review->id} к \"{$game->title}\" ({$review->rating}) " . ($review->is_approved ? '[одобрен]' : '[на модерации]') . "\n");
            } else {
                $this->stderr("Ошибка создания отзыва\n");
            }
        }

        $this->stdout("\n Создано отзывов: $created (одобрено: $approved)\n");
    }

    /**
     * Генерация игровых сессий
     */
    public function actionSessions($count = 5)
    {
        $games = Game::find()->all();

        if (empty($games)) {
            $this->stderr("Нет игр в базе. Сначала создайте игры: yii seed/games\n");
            return 1;
        }

        $created = 0;

        $userIds = $this->getUserIds();

        for ($i = 0; $i < $count; $i++) {
            $game = $games[array_rand($games)];

            $session = new GameSession();
            $session->game_id = $game->id;
            $session->organizer_id = $userIds[array_rand($userIds)];
            $session->scheduled_at = (new \DateTime())->modify('+5 days')->format('d.m.Y H:i');;
            $session->max_participants = rand(3, 8);
            $session->status = GameSession::STATUS_PLANNED;

            if ($session->save()) {
                $created++;
                $date = Yii::$app->formatter->asDate($session->scheduled_at, 'php:d.m.Y');
                $this->stdout("Сессия #{$session->id}: \"{$game->title}\" на $date ({$session->max_participants} игроков)\n");
            } else {
                $this->stderr("Ошибка создания сессии\n");
                $this->stderr(implode(', ', $session->getFirstErrors()) . "\n");
            }
        }

        $this->stdout("\n Создано сессий: $created\n");
    }

    /**
     * Генерация всех тестовых данных
     */
    public function actionAll()
    {
        $this->stdout("=== Генерация тестовых данных ===\n\n");

        $this->actionUsers();
        $this->stdout("\n");

        $this->actionGames();
        $this->stdout("\n");

        $this->actionReviews();
        $this->stdout("\n");

        $this->actionSessions();

        $this->stdout("\n=== Готово! ===\n");

        $this->stdout("\nТестовые пользователи:\n");
        $this->stdout("  - admin / admin123 (администратор)\n");
        $this->stdout("  - moderator / moderator123 (модератор)\n");
        $this->stdout("  - user1 / user123 (обычный пользователь)\n");
        $this->stdout("  - user2 / user123 (обычный пользователь)\n");
    }

    public function getUserIds(): array
    {
        return \yii\helpers\ArrayHelper::getColumn(
            User::find()
                ->select('id')
                ->asArray()
                ->all(), 'id');
    }
}