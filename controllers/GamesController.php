<?php

namespace app\controllers;

use app\models\game\Game;
use app\models\search\GameSearch;
use app\models\search\ReviewSearch;
use app\models\search\GameSessionSearch;
use app\models\user\Review;
use Yii;
use yii\db\Exception;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\Response;

/**
 * GameController implements the CRUD actions for Game model.
 */
class GamesController extends Controller
{
    /**
     * @inheritDoc
     */
    public function behaviors(): array
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    [
                        'allow' => true,
                        'actions' => ['index', 'view'],
                        'roles' => ['?', '@'],
                    ],
                    [
                        'allow' => true,
                        'actions' => ['create', 'update', 'delete'],
                        'roles' => ['@'],
                        'permissions' => ['manageGames'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'delete' => ['POST'],
                ],
            ],
        ];
    }

    /**
     * Lists all Game models.
     *
     * @return string
     */
    public function actionIndex(): string
    {
        $searchModel = new GameSearch();
        $dataProvider = $searchModel->search($this->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Отображение игры и отправка отзыва
     * @param int $id ID
     * @return string
     * @throws NotFoundHttpException|Exception if the model cannot be found
     */
    public function actionView(int $id): string
    {
        $model = $this->findModel($id);

        $reviewSearch = new ReviewSearch();
        $reviewsDataProvider = $reviewSearch->getApprovedReviewsForGame($id);

        $sessionSearch = new GameSessionSearch();
        $sessionsDataProvider = $sessionSearch->getUpcomingSessionsForGame($id);

        $reviewForm = new Review();
        $reviewForm->game_id = $id;

        if ($reviewForm->load(Yii::$app->request->post())) {
            $reviewForm->user_id = Yii::$app->user->id;
            if ($reviewForm->save()) {
                Yii::$app->session->setFlash('success', Yii::t('app','Отзыв отправлен на модерацию!'));
                $this->refresh();
            }
        }

        return $this->render('view', [
            'model' => $model,
            'reviewsDataProvider' => $reviewsDataProvider,
            'sessionsDataProvider' => $sessionsDataProvider,
            'reviewForm' => $reviewForm,
        ]);
    }

    /**
     * Creates a new Game model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return string|Response
     * @throws Exception
     */
    public function actionCreate(): Response|string
    {
        $model = new Game();

        if ($this->request->isPost) {
            if ($model->load($this->request->post()) && $model->save()) {
                return $this->redirect(['view', 'id' => $model->id]);
            }
        } else {
            $model->loadDefaultValues();
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }

    /**
     * Updates an existing Game model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param int $id ID
     * @return string|Response
     * @throws NotFoundHttpException|Exception if the model cannot be found
     */
    public function actionUpdate($id): Response|string
    {
        $model = $this->findModel($id);

        if ($this->request->isPost && $model->load($this->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('update', [
            'model' => $model,
        ]);
    }

    /**
     * Deletes an existing Game model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param int $id ID
     * @return Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id): Response
    {
        $this->findModel($id)->delete();

        return $this->redirect(['index']);
    }

    /**
     * Finds the Game model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param int $id ID
     * @return Game the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id): Game
    {
        if (($model = Game::findOne(['id' => $id])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException(Yii::t('app', 'The requested page does not exist.'));
    }
}
