<?php
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\LinkPager;

$this->title = Yii::t('modules/notifications', 'Notifications');
?>
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h3 mb-0 text-gray-800"><?= Html::encode($this->title) ?></h1>
                <?= Html::a(Yii::t('modules/notifications', 'Mark all as read'), ['read-all'], [
                    'class' => 'btn btn-outline-primary btn-sm rounded-pill',
                    'data-method' => 'post'
                ]) ?>
                <?= Html::a(Yii::t('modules/notifications', 'Delete all'), ['delete-all'], [
                    'class' => 'btn btn-outline-primary btn-sm rounded-pill',
                    'data-method' => 'post'
                ]) ?>
            </div>

            <div class="card shadow-sm border-0 rounded-4">
                <div class="list-group list-group-flush rounded-4">
                    <?php if (empty($notifications)): ?>
                        <div class="p-5 text-center text-muted">
                            <?= Yii::t('modules/notifications', 'There are no notifications to show') ?>
                        </div>
                    <?php else: ?>
                        <ul class="list-group shadow">
                            <?php foreach ($notifications as $notification): ?>
                                <li class="list-group-item d-flex align-items-center <?= !$notification['read'] ? 'active' : '' ?>">

                                    <div class="flex-grow-1 position-relative">
                                        <?php if ($notification['read']): ?>
                                            <a href="<?= $notification['url'] ?>"
                                               class="text-decoration-none text-secondary">
                                                <?= Html::encode($notification['message']) ?>
                                            </a>
                                            <div class="small mt-1 text-muted">
                                                <?= $notification['timeago'] ?>
                                            </div>
                                        <?php else: ?>
                                            <a href="<?= $notification['url'] ?>"
                                               class="text-decoration-none text-light fw-bold">
                                                <?= Html::encode($notification['message']) ?>
                                            </a>
                                            <div class="small mt-1 text-light">
                                                <?= $notification['timeago'] ?>
                                            </div>
                                            <?= Html::a(Yii::t('modules/notifications', 'Mark as read'), ['read', 'id' => $notification['id']], ['class' => 'btn btn-sm btn-primary end-0']) ?>
                                        <?php endif; ?>
                                    </div>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>
                </div>
            </div>

            <div class="mt-4 d-flex justify-content-center">
                <?= LinkPager::widget([
                    'pagination' => $pagination,
                    'options' => ['class' => 'pagination pagination-sm'],
                    'linkContainerOptions' => ['class' => 'page-item'],
                    'linkOptions' => ['class' => 'page-link'],
                ]) ?>
            </div>
        </div>
    </div>
</div>
