<?php

namespace app\services;

use app\models\user\User;
use yii\base\Exception;

class UserService extends BaseService
{
    public function findUser(int $id): User
    {
        return $this->findModel(User::class, $id);
    }

    public function findUserByUsername(string $username): ?User
    {
        return User::findOne(['username' => $username]);
    }

    public function findUserByEmail(string $email): ?User
    {
        return User::findOne(['email' => $email]);
    }

    public function createUser(array $data): User
    {
        $user = new User();
        $user->load($data);
        $user->setPassword($data['User']['password'] ?? '');
        $user->generateAuthKey();

        if (!$user->save()) {
            throw new Exception($this->formatValidationErrors($user));
        }

        return $user;
    }

    public function updateUser(int $id, array $data): User
    {
        $user = $this->findUser($id);
        $user->load($data);

        if (!empty($data['User']['password'])) {
            $user->setPassword($data['User']['password']);
        }

        if (!$user->save()) {
            throw new Exception($this->formatValidationErrors($user));
        }

        return $user;
    }

    public function deactivateUser(int $id): bool
    {
        $user = $this->findUser($id);
        $user->status = User::STATUS_DELETED;
        return $user->save(false, ['status']);
    }

    public function activateUser(int $id): bool
    {
        $user = $this->findUser($id);
        $user->status = User::STATUS_ACTIVE;
        return $user->save(false, ['status']);
    }

    public function getUserStats(int $userId): array
    {
        $user = $this->findUser($userId);

        return [
            'reviews_count' => $user->getReviews()->count(),
            'sessions_organized' => $user->getGameSessions()->count(),
            'subscriptions' => $user->getGameSubscriptions()->count(),
        ];
    }
}
