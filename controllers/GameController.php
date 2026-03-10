<?php

namespace app\controllers;

use app\models\forms\ReviewForm;
use app\models\game\Game;
use app\models\search\GameSessionSearch;
use app\models\search\ReviewSearch;
use app\services\GameService;
use app\services\ReviewService;
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
        $searchModel = $this->gameService->getGameSearchModel();
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
     * @throws \yii\base\Exception
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
        $reviewForm->user_id = Yii::$app->user->id;

        if ($reviewForm->load(Yii::$app->request->post()) && $reviewForm->validate()) {
            try {
                $review = $this->reviewService->createReview($reviewForm);
                if ($review) {
                    Yii::$app->session->setFlash('success', Yii::t('app', 'Review sent for moderation!'));
                    $this->refresh();
                }
            } catch (\Throwable $e) {
                var_dump($e->getMessage());
                Yii::$app->session->setFlash('error', Yii::t('app', 'Review do not exist'));
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
        if ($this->request->isPost) {
            try {
                $model = $this->gameService->createGame($this->request->post());
                return $this->redirect(['view', 'id' => $model->id]);
            } catch (Exception $e) {
                Yii::$app->session->setFlash('error', $e->getMessage());
                $model = new Game();
                $model->load($this->request->post());
            }
        } else {
            $model = new Game();
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
        if ($this->request->isPost) {
            try {
                $model = $this->gameService->updateGame($id, $this->request->post());
                return $this->redirect(['view', 'id' => $model->id]);
            } catch (Exception $e) {
                Yii::$app->session->setFlash('error', $e->getMessage());
                $model = $this->gameService->findGame($id);
                $model->load($this->request->post());
            }
        } else {
            $model = $this->gameService->findGame($id);
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
        $this->gameService->deleteGame($id);
        return $this->redirect(['index']);
    }
}
