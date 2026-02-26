<?php

namespace app\controllers;

use app\jobs\SendReviewNotificationJob;
use app\models\search\ReviewSearch;
use app\models\user\Review;
use Yii;
use yii\filters\AccessControl;
use yii\web\Controller;
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
        $searchModel = new ReviewSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        $dataProvider->query->andWhere(['is_approved' => false]);

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
        $model = Review::findOne($id);

        if ($model) {
            $model->is_approved = true;

            if ($model->save()) {

                // Отправляем уведомление
                Yii::$app->queue->push(new SendReviewNotificationJob([
                    'userId'   => $model->user_id,
                    'gameId'   => $model->game_id,
                    'gameName' => $model->game->title,
                    'isApproved'   => true,
                ]));

                Yii::$app->session->setFlash('success', Yii::t('app', 'Отзыв одобрен!'));
            } else {
                Yii::$app->session->setFlash('error', Yii::t('app', 'Ошибка при одобрении отзыва.'));
            }
        } else {
            Yii::$app->session->setFlash('error', Yii::t('app', 'Отзыв не найден.'));
        }

        return $this->redirect(['index']);
    }

    /**
     * Отклонить отзыв (удалить)
     */
    public function actionReject($id): Response
    {
        $model = Review::findOne($id);

        if ($model && $model->delete()) {
            // Отправляем уведомление
            Yii::$app->queue->push(new SendReviewNotificationJob([
                'userId'   => $model->user_id,
                'gameId'   => $model->game_id,
                'gameName' => $model->game->title,
                'isApproved'   => false,
            ]));

            Yii::$app->session->setFlash('success', Yii::t('app', 'Отзыв отклонён!'));
        } else {
            Yii::$app->session->setFlash('error', Yii::t('app', 'Ошибка при отклонении отзыва.'));
        }

        return $this->redirect(['index']);
    }
}
