<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\widgets\DetailView;

/** @var yii\web\View $this */
/** @var app\models\Author $model */
/** @var app\models\Subscription $subscriptionModel */

$this->title = $model->full_name;
$this->params['breadcrumbs'][] = ['label' => 'Authors', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);
?>
<div class="author-view">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?php if (!Yii::$app->user->isGuest): ?>
            <?= Html::a('Update', ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
            <?= Html::a('Delete', ['delete', 'id' => $model->id], [
                'class' => 'btn btn-danger',
                'data' => [
                    'confirm' => 'Are you sure you want to delete this item?',
                    'method' => 'post',
                ],
            ]) ?>
        <?php endif; ?>
    </p>

    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            'id',
            'full_name',
        ],
    ]) ?>

    <?php if (Yii::$app->session->hasFlash('success')): ?>
        <div class="alert alert-success">
            <?= Yii::$app->session->getFlash('success') ?>
        </div>
    <?php endif; ?>
    
    <?php if (Yii::$app->session->hasFlash('error')): ?>
        <div class="alert alert-danger">
            <?= Yii::$app->session->getFlash('error') ?>
        </div>
    <?php endif; ?>

    <div class="subscription-form mt-5">
        <h3>Subscribe to new books</h3>
        <p>Leave your phone number to receive SMS notifications when new books by this author are added:</p>
        
        <?php $form = ActiveForm::begin([
    'action' => ['author/subscribe', 'id' => $model->id],
    'method' => 'post',
]); ?>

        <?= $form->field($subscriptionModel, 'phone')->textInput(['maxlength' => true, 'placeholder' => 'Enter your phone number']) ?>

        <div class="form-group">
            <?= Html::submitButton('Subscribe', ['class' => 'btn btn-success']) ?>
        </div>

        <?php ActiveForm::end(); ?>
    </div>

    <div class="author-books mt-5">
        <h3>Books by this author</h3>
        <?php if ($model->books): ?>
            <ul>
            <?php foreach ($model->books as $book): ?>
                <li>
                    <strong><a href="<?= \yii\helpers\Url::to(['book/view', 'id' => $book->id]) ?>"><?= Html::encode($book->title) ?></a></strong> 
                    (<?= $book->year ?>)
                </li>
            <?php endforeach; ?>
            </ul>
        <?php else: ?>
            <p>No books available yet.</p>
        <?php endif; ?>
    </div>

</div>
