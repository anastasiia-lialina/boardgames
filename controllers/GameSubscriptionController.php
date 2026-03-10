<?php

namespace app\controllers;

use app\models\game\GameSubscription;
use app\models\search\GameSubscriptionSearch;
use app\services\GameSubscriptionService;
use Yii;
use yii\base\Exception;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\Response;

class GameSubscriptionController extends Controller
{
    /**
     * @param $id
     * @param $module
     * @param GameSubscriptionService $subscriptionService
     * @param array $config
     */
    public function __construct(
        $id,
        $module,
        private readonly GameSubscriptionService $subscriptionService,
        array $config = []
    ) {
        parent::__construct($id, $module, $config);
    }

    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    [
                        'allow' => true,
                        'actions' => ['index', 'subscribe', 'unsubscribe'],
                        'roles' => ['@'],
                    ],
                    [
                        'allow' => true,
                        'actions' => ['toggle', 'delete'],
                        'roles' => ['@'],
                        'matchCallback' => function ($rule, $action) {
                            $id = Yii::$app->request->get('id');
                            return GameSubscription::find()
                                ->where(['id' => $id, 'user_id' => Yii::$app->user->id])
                                ->exists();
                        },
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
        $searchModel = new \app\models\search\GameSubscriptionSearch();
        $dataProvider = $this->subscriptionService->getSubscriptionProvider(
            Yii::$app->request->queryParams,
            Yii::$app->user->id
        );

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Подписаться на игру
     * @throws NotFoundHttpException
     * @throws \Exception
     */
    public function actionSubscribe(): Response
    {
        $gameId = $this->request->post('gameId');

        try {
            $this->subscriptionService->subscribe(Yii::$app->user->id, $gameId);
            Yii::$app->session->setFlash('success', Yii::t('app', 'You have subscribed to the game.'));
        } catch (\Exception $e) {
            Yii::$app->session->setFlash('error', $e->getMessage());
        }

        return $this->redirect(['game/view', 'id' => $gameId]);
    }

    /**
     * Отписаться от игры
     * @throws NotFoundHttpException
     * @throws \yii\db\Exception
     * @throws \Exception
     */
    public function actionUnsubscribe(): Response
    {
        $gameId = $this->request->post('gameId');

        try {
            $this->subscriptionService->unsubscribe(Yii::$app->user->id, $gameId);
            Yii::$app->session->setFlash('success', Yii::t('app', 'You have unsubscribed from the game.'));
        } catch (\Exception $e) {
            Yii::$app->session->setFlash('error', $e->getMessage());
        }

        return $this->redirect(['game/view', 'id' => $gameId]);
    }

    /**
     * Переключить статус подписки (активна/неактивна)
     * @param int $id
     * @return Response
     */
    public function actionToggle(int $id): Response
    {
        try {
            $this->subscriptionService->toggleSubscription($id);
            Yii::$app->session->setFlash('success', Yii::t('app', 'Subscription status changed.'));
        } catch (Exception) {
            Yii::$app->session->setFlash('error', Yii::t('app', 'Error changing subscription status.'));
        }

        return $this->redirect(['index']);
    }

    /**
     * Удалить подписку
     */
    public function actionDelete($id): Response
    {
        try {
            $this->subscriptionService->deleteSubscription($id);
            Yii::$app->session->setFlash('success', Yii::t('app', 'Subscription to game has been removed.'));
        } catch (\Exception $e) {
            Yii::$app->session->setFlash('error', $e->getMessage());
        }

        return $this->redirect(['index']);
    }
}
