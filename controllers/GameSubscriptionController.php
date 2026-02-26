<?php

namespace app\controllers;

use app\models\game\Game;
use Yii;
use app\models\game\GameSubscription;
use app\models\search\models\GameSubscriptionSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use yii\web\Response;

class GameSubscriptionController extends Controller
{
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'delete' => ['POST'],
                    'toggle' => ['POST'],
                    'subscribe' => ['POST'],
                    'unsubscribe' => ['POST'],
                ],
            ],
        ];
    }

    /**
     * Список подписок пользователя
     */
    public function actionIndex(): string
    {
        $searchModel = new GameSubscriptionSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams, Yii::$app->user->id);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Подписаться на игру
     */
    public function actionSubscribe(): Response
    {
        //TODO убрать дублирование кода с actionUnsubscribe и добавить переводы
        $gameId = $this->request->post('gameId');
        $game = Game::findOne($gameId);
        if (!$game) {
            Yii::$app->session->setFlash('error', Yii::t('app', 'Game not found.'));
            return $this->redirect(['game/index']);
        }

        if (GameSubscription::subscribe(Yii::$app->user->id, $gameId)) {
            Yii::$app->session->setFlash('success', Yii::t('app', 'You have subscribed to the game "{game}".', ['game' => $game->title]));
        } else {
            Yii::$app->session->setFlash('error', Yii::t('app', 'Error subscribing to the game.'));
        }

        return $this->redirect(['game/view', 'id' => $gameId]);
    }

    /**
     * Отписаться от игры
     */
    public function actionUnsubscribe(): Response
    {
        $gameId = $this->request->post('gameId');
        $game = Game::findOne($gameId);
        if (!$game) {
            Yii::$app->session->setFlash('error', Yii::t('app', 'Game not found.'));
            return $this->redirect(['game/index']);
        }

        if (GameSubscription::unsubscribe(Yii::$app->user->id, $gameId)) {
            Yii::$app->session->setFlash('success', Yii::t('app', 'You have unsubscribed from the "{game}".', ['game' => $game->title]));
        } else {
            Yii::$app->session->setFlash('error', Yii::t('app', 'Error unsubscribing from the game.'));
        }

        return $this->redirect(['game/view', 'id' => $gameId]);
    }

    /**
     * Переключить статус подписки (активна/неактивна)
     */
    public function actionToggle($id)
    {
        $model = $this->findModel($id);

        if ($model->user_id !== Yii::$app->user->id) {
            throw new NotFoundHttpException(Yii::t('app', 'Subscription not found.'));
        }

        $model->is_active = !$model->is_active;
        if ($model->save(false, ['is_active'])) {
            Yii::$app->session->setFlash('success', Yii::t('app', 'Subscription status changed.'));
        } else {
            Yii::$app->session->setFlash('error', Yii::t('app', 'Error changing subscription status.'));
        }

        return $this->redirect(['index']);
    }

    /**
     * Удалить подписку
     */
    public function actionDelete($id)
    {
        $model = $this->findModel($id);

        if ($model->user_id !== Yii::$app->user->id) {
            throw new NotFoundHttpException(Yii::t('app', 'Subscription not found.'));
        }

        $gameTitle = $model->game->title;
        if ($model->delete()) {
            Yii::$app->session->setFlash('success', Yii::t('app', 'Subscription to game "{game}" has been removed.', ['game' => $gameTitle]));
        } else {
            Yii::$app->session->setFlash('error', Yii::t('app', 'Error deleting subscription.'));
        }

        return $this->redirect(['index']);
    }

    /**
     * Finds the GameSubscription model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return GameSubscription the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel(int $id): GameSubscription
    {
        if (($model = GameSubscription::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException(Yii::t('app', 'The requested page does not exist.'));
    }
}