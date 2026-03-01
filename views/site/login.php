<?php

use app\models\forms\LoginForm;
use yii\bootstrap5\ActiveForm;
use yii\bootstrap5\Html;

/** @var LoginForm $model */

$this->title = Yii::t('app', 'Login');
?>
<div class="site-login">
    <div class="row justify-content-center mt-5">
        <div class="col-lg-4 col-md-6">
            <div class="card shadow-sm">
                <div class="card-body p-4">
                    <h2 class="card-title text-center mb-4"><?= Html::encode($this->title) ?></h2>

                    <?php $form = ActiveForm::begin(['id' => 'login-form']); ?>

                    <?= $form->field($model, 'username')->textInput(['autofocus' => true, 'placeholder' => true]) ?>

                    <?= $form->field($model, 'password')->passwordInput(['placeholder' => true]) ?>

                    <?= $form->field($model, 'rememberMe')->checkbox([
                            'template' => "<div class=\"form-check mb-3\">{input} {label}</div>\n<div class=\"col-lg-8\">{error}</div>",
                    ]) ?>

                    <div class="form-group d-grid">
                        <?= Html::submitButton(Yii::t('app', 'Login'), ['class' => 'btn btn-success btn-lg', 'name' => 'login-button']) ?>
                    </div>

                    <?php ActiveForm::end(); ?>
                </div>
            </div>
        </div>
    </div>
    <?php  if (YII_ENV_DEV): ?>
        <p class="text-muted">
            <?= Yii::t('app', 'Тестовые пользователи:') ?><br>
            <?= Yii::t('app', 'admin / test') ?><br>
            <?= Yii::t('app', 'moderator / test') ?><br>
            <?= Yii::t('app', 'user1 / test') ?>
        </p>
    <?php endif; ?>
</div>
