<?php

namespace app\controllers;

use app\exception\ServiceException;
use app\models\forms\GameForm;
use app\models\forms\ReviewForm;
use app\models\game\Game;
use app\models\search\GameSessionSearch;
use app\models\search\ReviewSearch;
use app\services\GameService;
use app\services\ReviewService;
use yii\bootstrap5\ActiveForm;
use yii\db\Exception;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\Response;

/**
 * GameController implements the CRUD actions for Game model.
 */
class GameController extends Controller
{
    public function __construct(
        $id,
        $module,
        private readonly GameService $gameService,
        private readonly ReviewService $reviewService,
        $config = []
    ) {
        parent::__construct($id, $module, $config);
    }

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
     */
    public function actionIndex(): string
    {
        $searchModel = $this->gameService->getGameSearchModel();
        $dataProvider = $searchModel->search($this->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Отображение игры
     *
     * @param int $id ID
     * @return Response|array|string
     * @throws \Throwable
     */
    public function actionView(int $id): Response|array|string
    {
        $model = $this->gameService->getGameWithSessions($id);
        $reviewForm = new ReviewForm(['game_id' => $id, 'user_id' => \Yii::$app->user->id]);
        $loaded = $reviewForm->load(\Yii::$app->request->post());

        if (\Yii::$app->request->isAjax && $loaded) {
            \Yii::$app->response->format = Response::FORMAT_JSON;
            return ActiveForm::validate($reviewForm);
        }

        if ($loaded && $reviewForm->validate()) {
            if ($this->reviewService->createReview($reviewForm)) {
                \Yii::$app->session->setFlash('success', \Yii::t('app', 'Review sent for moderation!'));
                return $this->refresh();
            }
        }

        return $this->render('view', [
            'model' => $model,
            'reviewForm' => $reviewForm,
            'reviewsDataProvider' => (new ReviewSearch())->getApprovedReviewsForGame($id),
            'sessionsDataProvider' => (new GameSessionSearch())->getUpcomingSessionsForGame($id),
        ]);
    }

    /**
     * Creates a new Game model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     *
     * @throws Exception
     */
    public function actionCreate(): Response|string
    {
        $form = new GameForm();

        if ($form->load($this->request->post()) && $form->validate()) {
            try {
                $model = $this->gameService->createGame($form);

                return $this->redirect(['view', 'id' => $model->id]);
            } catch (Exception $e) {
                \Yii::$app->session->setFlash('error', $e->getMessage());
            }
        }

        return $this->render('create', [
            'model' => $form,
        ]);
    }

    /**
     * Updates an existing Game model.
     * If update is successful, the browser will be redirected to the 'view' page.
     *
     * @param int $id ID
     *
     * @throws Exception|NotFoundHttpException|ServiceException if the model cannot be found
     */
    public function actionUpdate($id): Response|string
    {
        $model = $this->gameService->findModel(Game::class, $id);
        $form = new GameForm();
        $form->setAttributes($model->attributes);

        if ($form->load($this->request->post()) && $form->validate()) {
            try {
                $model = $this->gameService->updateGame($id, $form->getSafeAttributes());

                return $this->redirect(['view', 'id' => $model->id]);
            } catch (Exception|ServiceException $e) {
                \Yii::$app->session->setFlash('error', $e->getMessage());
            }
        }

        return $this->render('update', [
            'model' => $form,
        ]);
    }

    /**
     * Deletes an existing Game model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     *
     * @param int $id ID
     *
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id): Response
    {
        $this->gameService->deleteGame($id);

        return $this->redirect(['index']);
    }
}
