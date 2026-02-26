<?php

/** @var yii\web\View $this */
/** @var string $content */

use app\assets\AppAsset;
use app\components\NotificationsBS5;
use app\widgets\Alert;
use webzop\notifications\widgets\Notifications;
use yii\bootstrap5\Breadcrumbs;
use yii\bootstrap5\Html;
use yii\bootstrap5\Nav;
use yii\bootstrap5\NavBar;

AppAsset::register($this);

$this->registerCsrfMetaTags();
$this->registerMetaTag(['charset' => Yii::$app->charset], 'charset');
$this->registerMetaTag(['name' => 'viewport', 'content' => 'width=device-width, initial-scale=1, shrink-to-fit=no']);
$this->registerMetaTag(['name' => 'description', 'content' => $this->params['meta_description'] ?? '']);
$this->registerMetaTag(['name' => 'keywords', 'content' => $this->params['meta_keywords'] ?? '']);
$this->registerLinkTag(['rel' => 'icon', 'type' => 'image/x-icon', 'href' => Yii::getAlias('@web/favicon.ico')]);
?>
<?php $this->beginPage() ?>
<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>" class="h-100">
<head>
    <title><?= Html::encode($this->title) ?></title>
    <?php $this->head() ?>
</head>
<body class="d-flex flex-column h-100">
<?php $this->beginBody() ?>

<header id="header">
    <?php
    if (Yii::$app->user->isGuest) {
        $menuItems[] = ['label' => Yii::t('app', 'Signup'), 'url' => ['/site/signup']];
        $menuItems[] = ['label' => Yii::t('app', 'Login'), 'url' => ['/site/login']];
    } else {
        // Проверяем права доступа
        $canCreateSession = Yii::$app->user->can('createSession');
        $canCreateReview = Yii::$app->user->can('createReview');
        $canManageReviews = Yii::$app->user->can('manageReviews');
        $canManageGames = Yii::$app->user->can('manageGames');

        if ($canCreateSession) {
            $menuItems[] = ['label' => Yii::t('app', 'Create Session'), 'url' => ['/game-session/create']];
        }

        if ($canManageReviews) {
            $menuItems[] = ['label' => Yii::t('app', 'Review Moderation'), 'url' => ['/admin/index']];
        }

        if ($canManageGames) {
            $menuItems[] = ['label' => Yii::t('app', 'Games'), 'url' => ['/game/index']];
        }

        $menuItems[] = [
                'label' => NotificationsBS5::widget(['id' => 'notifications']),
                'url' => ['/notifications/default/index'],
                'encode' => false,
                'options' => [
                        'id' => 'notifications',
                        'class' => 'nav-item',
                ],
                'linkOptions' => [
                        'class' => 'nav-link d-flex align-items-center',
                ],
        ];

        $menuItems[] = ['label' => Yii::t('app', 'Выход'), 'url' => ['/site/logout'], 'linkOptions' => ['data-method' => 'post']];
    }



NavBar::begin([
    'brandLabel' => Yii::$app->name,
    'brandUrl' => Yii::$app->homeUrl,
    'options' => ['class' => 'navbar-expand-md navbar-dark bg-dark fixed-top'],
]);
echo Nav::widget([
    'options' => ['class' => 'navbar-nav'],
        'items' => $menuItems,
]);

NavBar::end();
?>








</header>

<main id="main" class="flex-shrink-0" role="main">
    <div class="container">
        <?php if (!empty($this->params['breadcrumbs'])): ?>
            <?= Breadcrumbs::widget(['links' => $this->params['breadcrumbs']]) ?>
        <?php endif ?>
        <?= Alert::widget() ?>
        <?= $content ?>
    </div>
</main>

<footer id="footer" class="mt-auto py-3 bg-light">
    <div class="container">
        <div class="row text-muted">
            <div class="col-md-6 text-center text-md-start">&copy; My Company <?= date('Y') ?></div>
            <div class="col-md-6 text-center text-md-end"><?= Yii::powered() ?></div>
        </div>
    </div>
</footer>

<?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage() ?>
