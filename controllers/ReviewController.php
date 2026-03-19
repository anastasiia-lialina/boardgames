<?php

namespace app\controllers;

use app\models\forms\ReviewForm;
use app\models\user\Review;
use app\services\ReviewService;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\Response;

/**
 * ReviewController implements the CRUD actions for Review model.
 */
class ReviewController extends Controller
{
    public function __construct(
        $id,
        $module,
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
                        'actions' => ['create'],
                        'roles' => ['@'],
                        'permissions' => ['createReview'],
                    ],
                    [
                        'allow' => true,
                        'actions' => ['update', 'delete', 'view'],
                        'permissions' => ['manageReviews'],
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
     * Displays a single Reviews model.
     *
     * @param int $id ID
     *
     * @throws \Exception|NotFoundHttpException if the model cannot be found
     */
    public function actionView(int $id): string
    {
        return $this->render('view', [
            'model' => $this->reviewService->findModel(Review::class, $id),
        ]);
    }

    /**
     * Creates a new Reviews model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     */
    public function actionCreate(): Response|string
    {
        $form = new ReviewForm();
        $form->user_id = \Yii::$app->user->id;

        if ($form->load(\Yii::$app->request->post()) && $form->validate()) {
            try {
                $this->reviewService->createReview($form);
                \Yii::$app->session->setFlash('success', \Yii::t('app', 'Review created successfully!'));

                return $this->redirect(['game/view', 'id' => $form->game_id]);
            } catch (\Exception $e) {
                \Yii::$app->session->setFlash('error', $e->getMessage());
            }
        }

        return $this->render('create', [
            'model' => $form,
        ]);
    }

    /**
     * Updates an existing Reviews model.
     * If update is successful, the browser will be redirected to the 'view' page.
     *
     * @param int $id ID
     *
     * @throws \Exception|NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate(int $id): Response|string
    {
        if ($this->request->isPost) {
            try {
                $model = $this->reviewService->updateReview($id, $this->request->post());

                return $this->redirect(['view', 'id' => $model->id]);
            } catch (\Exception $e) {
                \Yii::$app->session->setFlash('error', $e->getMessage());
                $model = $this->reviewService->findModel(Review::class, $id);
                $model->load($this->request->post());
            }
        } else {
            $model = $this->reviewService->findModel(Review::class, $id);
        }

        return $this->render('update', [
            'model' => $model,
        ]);
    }
}
