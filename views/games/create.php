<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\models\game\Games $model */

$this->title = Yii::t('app', 'Create Game');
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Games'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="game-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
