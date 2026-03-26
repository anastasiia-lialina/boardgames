<?php

use yii\helpers\Html;

/* @var $this yii\web\View */

$this->title = Yii::t('app', 'Report for games');
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="report-index">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1><?= Html::encode($this->title) ?></h1>
        <div>
            <?= Html::a(
                '<i class="fas fa-file-alt"></i> ' . Yii::t('app', 'Export CSV'),
                ['report/export'],
                [
                    'class' => 'btn btn-success',
                    'id' => 'export-btn',
                    'target' => '_blank',
                ]
            ) ?>
            <?= Html::button(Yii::t('app', 'Refresh data'), ['id' => 'refresh-btn', 'class' => 'btn btn-warning']) ?>
        </div>
    </div>

    <div class="row">
        <!-- Общая статистика -->
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0"><?= Yii::t('app', 'General statistics for games') ?></h5>
                </div>
                <div class="card-body data-container">
                    <div class="row">
                        <div class="col-md-3">
                            <div class="stat-item">
                                <h6 class="text-muted"><?= Yii::t('app', 'Total games') ?></h6>
                                <h3 class="text-primary" id="total-games">0</h3>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="stat-item">
                                <h6 class="text-muted"><?= Yii::t('app', 'Active games') ?></h6>
                                <h3 class="text-success" id="active-games">0</h3>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="stat-item">
                                <h6 class="text-muted"><?= Yii::t('app', 'Total sessions') ?></h6>
                                <h3 class="text-info" id="total-sessions">0</h3>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="stat-item">
                                <h6 class="text-muted"><?= Yii::t('app', 'Total subscriptions') ?></h6>
                                <h3 class="text-warning" id="total-subscriptions">0</h3>
                            </div>
                        </div>
                    </div>
                    <div class="row mt-3">
                        <div class="col-md-3">
                            <div class="stat-item">
                                <h6 class="text-muted"><?= Yii::t('app', 'Completed sessions') ?></h6>
                                <h3 class="text-success" id="completed-sessions">0</h3>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="stat-item">
                                <h6 class="text-muted"><?= Yii::t('app', 'Cancelled sessions') ?></h6>
                                <h3 class="text-danger" id="cancelled-sessions">0</h3>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="stat-item">
                                <h6 class="text-muted"><?= Yii::t('app', 'Total reviews') ?></h6>
                                <h3 class="text-info" id="total-reviews">0</h3>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="stat-item">
                                <h6 class="text-muted"><?= Yii::t('app', 'Average rating') ?></h6>
                                <h3 class="text-warning" id="avg-rating">0.00</h3>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-body loading-placeholder text-center py-5" style="display: none;">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <div class="mt-2">Loading statistics...</div>
                </div>
            </div>
        </div>
    </div>
    <div class="row mt-4">
        <!-- Статистика отзывов -->
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0"><?= Yii::t('app', 'Rating distribution') ?></h5>
                </div>
                <div class="card-body data-container">
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th><?= Yii::t('app', 'Rating') ?></th>
                                    <th><?= Yii::t('app', 'Count') ?></th>
                                    <th><?= Yii::t('app', 'Percent') ?></th>
                                </tr>
                            </thead>
                            <tbody id="reviews-stats-tbody">
                                <!-- Данные будут загружены через AJAX -->
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="card-body loading-placeholder text-center py-5" style="display: none;">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <div class="mt-2">Loading reviews...</div>
                </div>
            </div>
        </div>
        <!-- Популярные игры по сессиям -->
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0"><?= Yii::t('app', 'Popular games by sessions') ?></h5>
                </div>
                <div class="card-body data-container">
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th><?= Yii::t('app', 'Game') ?></th>
                                    <th><?= Yii::t('app', 'Sessions') ?></th>
                                    <th><?= Yii::t('app', 'Status of last session') ?></th>
                                </tr>
                            </thead>
                            <tbody id="popular-games-sessions-tbody">
                                <!-- Данные будут загружены через AJAX -->
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="card-body loading-placeholder text-center py-5" style="display: none;">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <div class="mt-2">Loading popular games...</div>
                </div>
            </div>
        </div>

        <!-- Популярные игры по подпискам -->
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0"><?= Yii::t('app', 'Popular games by subscriptions') ?></h5>
                </div>
                <div class="card-body data-container">
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th><?= Yii::t('app', 'Game') ?></th>
                                    <th><?= Yii::t('app', 'Subscriptions') ?></th>
                                    <th><?= Yii::t('app', 'Active') ?></th>
                                    <th><?= Yii::t('app', 'Activity') ?></th>
                                </tr>
                            </thead>
                            <tbody id="popular-games-subscriptions-tbody">
                                <!-- Данные будут загружены через AJAX -->
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="card-body loading-placeholder text-center py-5" style="display: none;">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <div class="mt-2">Loading subscriptions...</div>
                </div>
            </div>
        </div>
    </div>

    <div class="row mt-4">
        <!-- Последняя активность -->
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0"><?= Yii::t('app', 'Activity for last 30 days') ?></h5>
                </div>
                <div class="card-body data-container">
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th><?= Yii::t('app', 'Date') ?></th>
                                    <th><?= Yii::t('app', 'New games') ?></th>
                                    <th><?= Yii::t('app', 'New sessions') ?></th>
                                    <th><?= Yii::t('app', 'New subscriptions') ?></th>
                                    <th><?= Yii::t('app', 'New reviews') ?></th>
                                </tr>
                            </thead>
                            <tbody id="recent-activity-tbody">
                                <!-- Данные будут загружены через AJAX -->
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="card-body loading-placeholder text-center py-5" style="display: none;">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <div class="mt-2">Loading activity...</div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
$this->registerJsFile('/js/report.js?v=' . time(), [
    'depends' => [\yii\web\JqueryAsset::class],
]);
?>
