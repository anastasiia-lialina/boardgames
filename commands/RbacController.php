<?php

namespace app\commands;

use app\models\User;
use Yii;
use yii\console\Controller;
use yii\rbac\DbManager;

/**
 * RBAC controller for initializing roles and permissions
 */
class RbacController extends Controller
{
    /**
     * Initialize RBAC roles and permissions
     */
    public function actionInit()
    {
        $auth = Yii::$app->authManager;
        $auth->removeAll(); // Remove old assignments

        // Create permissions
        $manageReviews = $auth->createPermission('manageReviews');
        $manageReviews->description = 'Управлять отзывами (одобрять/отклонять)';
        $auth->add($manageReviews);

        $createReview = $auth->createPermission('createReview');
        $createReview->description = 'Создавать отзывы';
        $auth->add($createReview);

        $createSession = $auth->createPermission('createSession');
        $createSession->description = 'Создавать игровые сессии';
        $auth->add($createSession);

        $updateOwnSession = $auth->createPermission('updateOwnSession');
        $updateOwnSession->description = 'Редактировать свои сессии';
        $auth->add($updateOwnSession);

        $deleteOwnSession = $auth->createPermission('deleteOwnSession');
        $deleteOwnSession->description = 'Удалять свои сессии';
        $auth->add($deleteOwnSession);

        $manageSessions = $auth->createPermission('manageSessions');
        $manageSessions->description = 'Управлять всеми сессиями';
        $auth->add($manageSessions);

        $manageGames = $auth->createPermission('manageGames');
        $manageGames->description = 'Управлять играми';
        $auth->add($manageGames);

        // Create roles
        $admin = $auth->createRole('admin');
        $admin->description = 'Администратор';
        $auth->add($admin);

        $moderator = $auth->createRole('moderator');
        $moderator->description = 'Модератор';
        $auth->add($moderator);

        $user = $auth->createRole('user');
        $user->description = 'Пользователь';
        $auth->add($user);

        $guest = $auth->createRole('guest');
        $guest->description = 'Гость';
        $auth->add($guest);

        // Привязка разрешений ролям
        // Админ
        $auth->addChild($admin, $manageReviews);
        $auth->addChild($admin, $manageSessions);
        $auth->addChild($admin, $manageGames);

        // Модератор
        $auth->addChild($moderator, $manageReviews);

        // User
        $auth->addChild($user, $createReview);
        $auth->addChild($user, $createSession);
        $auth->addChild($user, $updateOwnSession);
        $auth->addChild($user, $deleteOwnSession);

        $this->stdout("RBAC roles and permissions initialized successfully!\n");
    }

    /**
     * Assign role to user
     * @param string $roleName
     * @param int $userId
     */
    public function actionAssign($roleName, $userId)
    {
        $auth = Yii::$app->authManager;
        $role = $auth->getRole($roleName);
        $user = User::findOne($userId);

        if (!$role) {
            $this->stderr("Role '{$roleName}' not found!\n");
            return 1;
        }

        if (!$user) {
            $this->stderr("User with ID {$userId} not found!\n");
            return 1;
        }

        $auth->assign($role, $userId);
        $this->stdout("Role '{$roleName}' assigned to user #{$userId} ({$user->username})\n");

        return 0;
    }

    /**
     * Revoke role from user
     * @param string $roleName
     * @param int $userId
     */
    public function actionRevoke($roleName, $userId)
    {
        $auth = Yii::$app->authManager;
        $role = $auth->getRole($roleName);
        $user = User::findOne($userId);

        if (!$role) {
            $this->stderr("Role '{$roleName}' not found!\n");
            return 1;
        }

        if (!$user) {
            $this->stderr("User with ID {$userId} not found!\n");
            return 1;
        }

        $auth->revoke($role, $userId);
        $this->stdout("Role '{$roleName}' revoked from user #{$userId} ({$user->username})\n");

        return 0;
    }

    /**
     * List all roles
     */
    public function actionListRoles()
    {
        $auth = Yii::$app->authManager;
        $roles = $auth->getRoles();

        $this->stdout("\nAvailable roles:\n");
        foreach ($roles as $role) {
            $this->stdout("  - {$role->name}: {$role->description}\n");
        }
    }

    /**
     * List all permissions
     */
    public function actionListPermissions()
    {
        $auth = Yii::$app->authManager;
        $permissions = $auth->getPermissions();

        $this->stdout("\nAvailable permissions:\n");
        foreach ($permissions as $permission) {
            $this->stdout("  - {$permission->name}: {$permission->description}\n");
        }
    }
}