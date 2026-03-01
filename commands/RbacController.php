<?php

namespace app\commands;

use app\models\user\User;
use app\rbac\OrganizerRule;
use Yii;
use yii\console\Controller;

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
        $auth->removeAll(); // Полная очистка перед обновлением

        // --- RULES---
        $rule = new OrganizerRule();
        $auth->add($rule);

        // --- PERMISSIONS

        // Отзывы
        $createReview = $auth->createPermission('createReview');
        $createReview->description = 'Создавать отзывы';
        $auth->add($createReview);

        $manageReviews = $auth->createPermission('manageReviews');
        $manageReviews->description = 'Модерировать отзывы (одобрение/отклонение)';
        $auth->add($manageReviews);

        // Игровые сессии
        $createSession = $auth->createPermission('createSession');
        $createSession->description = 'Создавать игровые сессии';
        $auth->add($createSession);

        $updateSession = $auth->createPermission('updateSession');
        $updateSession->description = 'Редактировать любую сессию';
        $auth->add($updateSession);

        $deleteSession = $auth->createPermission('deleteSession');
        $deleteSession->description = 'Удалять любую сессию';
        $auth->add($deleteSession);

        // Игровые сессии с правилом
        $updateOwnSession = $auth->createPermission('updateOwnSession');
        $updateOwnSession->description = 'Редактировать свою сессию';
        $updateOwnSession->ruleName = $rule->name;
        $auth->add($updateOwnSession);

        $deleteOwnSession = $auth->createPermission('deleteOwnSession');
        $deleteOwnSession->description = 'Удалять свою сессию';
        $deleteOwnSession->ruleName = $rule->name;
        $auth->add($deleteOwnSession);

        // полные разрешения
        $manageSessions = $auth->createPermission('manageSessions');
        $manageSessions->description = 'Полное управление всеми сессиями';
        $auth->add($manageSessions);

        $manageGames = $auth->createPermission('manageGames');
        $manageGames->description = 'Управлять справочником игр';
        $auth->add($manageGames);

        // --- ИЕРАРХИЯ ПРАВ ---
        $auth->addChild($updateOwnSession, $updateSession);
        $auth->addChild($deleteOwnSession, $deleteSession);


        $auth->addChild($updateSession, $manageSessions);
        $auth->addChild($deleteSession, $manageSessions);
        $auth->addChild($createSession, $manageSessions);

        // --- РОЛИ ---

        // USER
        $user = $auth->createRole('user');
        $user->description = 'Пользователь';
        $auth->add($user);
        $auth->addChild($user, $createReview);
        $auth->addChild($user, $createSession);
        $auth->addChild($user, $updateOwnSession);
        $auth->addChild($user, $deleteOwnSession);

        // MODERATOR
        $moderator = $auth->createRole('moderator');
        $moderator->description = 'Модератор';
        $auth->add($moderator);
        $auth->addChild($moderator, $user);
        $auth->addChild($moderator, $manageReviews);
        $auth->addChild($moderator, $updateSession);
        $auth->addChild($moderator, $deleteSession);

        // ADMIN
        $admin = $auth->createRole('admin');
        $admin->description = 'Администратор';
        $auth->add($admin);
        $auth->addChild($admin, $moderator);
        $auth->addChild($admin, $manageSessions);
        $auth->addChild($admin, $manageGames);

        // GUEST
        $guest = $auth->createRole('guest');
        $guest->description = 'Гость';
        $auth->add($guest);

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
