<?php

namespace app\controllers;

use app\factories\SendSmsJobFactory;
use app\models\Book;
use yii\data\ActiveDataProvider;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\UploadedFile;

/**
 * BookController implements the CRUD actions for Book model.
 */
class BookController extends Controller
{
    public function __construct(
        $id,
        $module,
        private SendSmsJobFactory $smsJobFactory,
        $config = [],
    ) {
        $this->smsJobFactory = $smsJobFactory;
        parent::__construct($id, $module, $config);
    }

    /**
     * @inheritDoc
     */
    public function behaviors()
    {
        return array_merge(
            parent::behaviors(),
            [
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
                        ],
                    ],
                ],
                'verbs' => [
                    'class' => VerbFilter::class,
                    'actions' => [
                        'delete' => ['POST'],
                    ],
                ],
            ]
        );
    }

    /**
     * Lists all Book models.
     *
     * @return string
     */
    public function actionIndex()
    {
        $dataProvider = new ActiveDataProvider([
            'query' => Book::find(),
            /*
            'pagination' => [
                'pageSize' => 50
            ],
            'sort' => [
                'defaultOrder' => [
                    'id' => SORT_DESC,
                ]
            ],
            */
        ]);

        return $this->render('index', [
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single Book model.
     *
     * @param int $id ID
     *
     * @throws NotFoundHttpException if the model cannot be found
     *
     * @return string
     */
    public function actionView($id)
    {
        return $this->render('view', [
            'model' => $this->findModel($id),
        ]);
    }

    /**
     * Creates a new Book model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     *
     * @return string|\yii\web\Response
     */
    public function actionCreate()
    {
        $model = new Book();

        if ($this->request->isPost) {
            if ($model->load($this->request->post())) {
                $model->cover_image_file = UploadedFile::getInstance($model, 'cover_image_file');
                
                if ($model->validate()) {
                    $transaction = \Yii::$app->db->beginTransaction();

                    try {
                        if ($model->save()) {
                            $model->uploadCoverImage();
                            
                            $authorIds = \Yii::$app->request->post('Book')['author_ids'];
                            if (! empty($authorIds)) {
                                $model->linkAuthors($authorIds);
                            }
                            
                            $job = $this->smsJobFactory->create([
                                'bookId' => $model->id,
                            ]);
                            \Yii::$app->queue->push($job);
                            
                            $transaction->commit();

                            return $this->redirect(['view', 'id' => $model->id]);
                        } else {
                            $transaction->rollback();
                        }
                    } catch (\Exception $e) {
                        $transaction->rollback();

                        throw $e;
                    } catch (\Throwable $e) {
                        $transaction->rollback();

                        throw $e;
                    }
                }
            }
        } else {
            $model->loadDefaultValues();
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }

    /**
     * Updates an existing Book model.
     * If update is successful, the browser will be redirected to the 'view' page.
     *
     * @param int $id ID
     *
     * @throws NotFoundHttpException if the model cannot be found
     *
     * @return string|\yii\web\Response
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($this->request->isPost && $model->load($this->request->post())) {
            $model->cover_image_file = UploadedFile::getInstance($model, 'cover_image_file');
            
            $oldAuthorIds = $model->getAuthorIds();
            if ($model->validate()) {
                $transaction = \Yii::$app->db->beginTransaction();

                try {
                    if ($model->save()) {
                        $model->uploadCoverImage();
                        
                        $authorIds = \Yii::$app->request->post('Book')['author_ids'];
                        if (! empty($authorIds)) {
                            $model->linkAuthors($authorIds);
                        }
                        
                        $transaction->commit();

                        return $this->redirect(['view', 'id' => $model->id]);
                    } else {
                        $transaction->rollback();
                    }
                } catch (\Exception $e) {
                    $transaction->rollback();

                    throw $e;
                } catch (\Throwable $e) {
                    $transaction->rollback();

                    throw $e;
                }
            }
        }

        return $this->render('update', [
            'model' => $model,
        ]);
    }

    /**
     * Deletes an existing Book model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     *
     * @param int $id ID
     *
     * @throws NotFoundHttpException if the model cannot be found
     *
     * @return \yii\web\Response
     */
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();

        return $this->redirect(['index']);
    }

    /**
     * Finds the Book model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     *
     * @param int $id ID
     *
     * @throws NotFoundHttpException if the model cannot be found
     *
     * @return Book the loaded model
     */
    protected function findModel($id)
    {
        if (($model = Book::findOne(['id' => $id])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
