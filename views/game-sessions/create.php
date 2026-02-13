<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model app\models\GameSessions */

$this->title = Yii::t('app', 'Create Game Session');
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Game Sessions'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="game-session-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
            'model' => $model,
    ]) ?>

</div>
