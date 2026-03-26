<?php

namespace app\services;

use app\exception\ServiceException;
use app\models\forms\Form;
use app\models\user\User;

class UserService extends BaseService
{
    /**
     * @throws \Exception
     */
    public function findUser(int $id): User
    {
        return $this->findModel(User::class, $id);
    }

    /**
     * Finds active user by username.
     */
    public function findByUsername(string $username): ?User
    {
        return User::findOne(['username' => $username, 'status' => User::STATUS_ACTIVE]);
    }

    public function findUserByEmail(string $email): ?User
    {
        return User::findOne(['email' => $email]);
    }

    /**
     * @throws ServiceException
     * @throws \yii\db\Exception
     * @throws \Exception
     */
    public function createUser(Form $form, string $role = User::ROLE_USER): User
    {
        $db = User::getDb();

        return $db->transaction(function ($db) use ($form, $role) {
            $user = new User();
            $this->load($user, $form);
            $user->setPassword($form->password ?? '');
            $user->generateAuthKey();

            if (!$user->save()) {
                throw new ServiceException($user);
            }

            $this->assignRole($user->id, $role);

            return $user;
        });
    }

    public function updateUser(int $id, Form $form): User
    {
        $db = User::getDb();

        return $db->transaction(function ($db) use ($id, $form) {
            $user = $this->findUser($id);

            $this->load($user, $form);
            if (!empty($form->getSafeAttributes()['password'])) {
                $user->setPassword($form->getSafeAttributes()['password']);
            }

            if (!$user->save()) {
                throw new ServiceException($user);
            }

            return $user;
        });
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

    /**
     * Назначение роли пользователю.
     */
    public function assignRole(int $userId, string $roleName): void
    {
        $db = \Yii::$app->authManager->db;

        $db->transaction(function ($db) use ($userId, $roleName) {
            $auth = \Yii::$app->authManager;
            $role = $auth->getRole($roleName);

            if (!$role) {
                throw new \Exception("Роль '{$roleName}' не найдена в системе.");
            }

            $auth->revokeAll($userId);
            $auth->assign($role, $userId);
        });
    }

    public function login(User $user, bool $rememberMe = false): bool
    {
        $duration = $rememberMe ? 3600 * 24 * 30 : 0;

        return \Yii::$app->user->login($user, $duration);
    }
}
