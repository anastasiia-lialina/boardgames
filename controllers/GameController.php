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
     * Отображение игры и отправка отзыва.
     *
     * @param int $id ID
     */
    public function actionView(int $id): string
    {
        $model = $this->gameService->getGameWithSessions($id);

        $reviewSearch = new ReviewSearch();
        $reviewsDataProvider = $reviewSearch->getApprovedReviewsForGame($id);

        $sessionSearch = new GameSessionSearch();
        $sessionsDataProvider = $sessionSearch->getUpcomingSessionsForGame($id);

        $reviewForm = new ReviewForm();
        $reviewForm->game_id = $id;
        $reviewForm->user_id = \Yii::$app->user->id;

        if ($reviewForm->load(\Yii::$app->request->post()) && $reviewForm->validate()) {
            try {
                $review = $this->reviewService->createReview($reviewForm);

                if ($review) {
                    \Yii::$app->session->setFlash('success', \Yii::t('app', 'Review sent for moderation!'));
                    $this->refresh();
                }
            } catch (\Throwable $e) {
                var_dump($e->getMessage());
                \Yii::$app->session->setFlash('error', \Yii::t('app', 'Review do not exist'));
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
     *
     * @throws Exception
     */
    public function actionCreate(): Response|string
    {
        $form = new GameForm();

        if ($form->load($this->request->post()) && $form->validate()) {
            try {
                $model = $this->gameService->createGame($form->getSafeAttributes());

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
