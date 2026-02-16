<?php

namespace app\controllers;

use app\models\SearchReviews;
use Yii;
use app\models\Reviews;
use yii\web\Controller;
use yii\filters\AccessControl;
use yii\web\Response;

/**
 * AdminController for moderating reviews
 */
class AdminController extends Controller
{
    /**
     * @inheritDoc
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => ['@'],
                        'permissions' => ['manageReviews'],
                    ],
                ],
            ],
        ];
    }

    /**
     * Список отзывов на модерации
     */
    public function actionIndex()
    {
        $searchModel = new SearchReviews();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        $dataProvider->query->andWhere(['is_approved' => false]);//TODO replace to Reviews

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Одобрить отзыв
     */
    public function actionApprove($id): Response
    {
        $model = Reviews::findOne($id);

        if ($model) {
            $model->is_approved = true;

            if ($model->save()) {
                Yii::$app->session->setFlash('success', Yii::t('app', 'Отзыв одобрен!'));
            } else {
                Yii::$app->session->setFlash('error',  Yii::t('app','Ошибка при одобрении отзыва.'));
            }
        } else {
            Yii::$app->session->setFlash('error',  Yii::t('app','Отзыв не найден.'));
        }

        return $this->redirect(['index']);
    }

    /**
     * Отклонить отзыв (удалить)
     */
    public function actionReject($id): Response
    {
        $model = Reviews::findOne($id);

        if ($model && $model->delete()) {
            Yii::$app->session->setFlash('success',  Yii::t('app','Отзыв отклонён!'));
        } else {
            Yii::$app->session->setFlash('error',  Yii::t('app','Ошибка при отклонении отзыва.'));
        }

        return $this->redirect(['index']);
    }
}
