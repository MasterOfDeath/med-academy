<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\helpers\ArrayHelper;
use app\models\Author;

/** @var yii\web\View $this */
/** @var app\models\Book $model */
/** @var yii\widgets\ActiveForm $form */
?>

<div class="book-form">

    <?php $form = ActiveForm::begin(['options' => ['enctype' => 'multipart/form-data']]); ?>

    <?= $form->field($model, 'title')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'year')->textInput() ?>

    <?= $form->field($model, 'description')->textarea(['rows' => 6]) ?>

    <?= $form->field($model, 'isbn')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'cover_image')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'cover_image_file')->fileInput() ?>

    <?php
    // Получаем список всех авторов для выпадающего списка
    $authors = ArrayHelper::map(Author::find()->all(), 'id', 'full_name');
    // Получаем текущие ID авторов для этой книги (при редактировании)
    $selectedAuthors = $model->isNewRecord ? [] : $model->getAuthorIds();
    ?>
    
    <?= $form->field($model, 'author_ids')->dropDownList($authors, [
        'multiple' => true,
        'size' => 10,
        'options' => array_combine($selectedAuthors, array_fill(0, count($selectedAuthors), ['selected' => true]))
    ])->label('Authors') ?>

    <div class="form-group">
        <?= Html::submitButton('Save', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
