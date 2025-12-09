<?php

use yii\grid\GridView;
use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var yii\data\ArrayDataProvider $dataProvider */
/** @var int $year */

$this->title = 'Top Authors Report for Year ' . $year;
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="report-top-authors">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        This report shows the Top 10 authors who published the most books in the year <?= $year ?>.
    </p>

    <div class="year-selector">
        <?php $form = \yii\widgets\ActiveForm::begin([
            'method' => 'get',
            'action' => ['report/top-authors'],
        ]); ?>
        
        <div class="form-group">
            <label for="year">Select Year</label>
            <input
                type="number"
                id="year"
                name="year"
                min="1900"
                max="<?= date('Y') ?>"
                value="<?= $year ?>"
                class="form-control"
            />
        </div>
        
        <?= Html::submitButton('Show Report', ['class' => 'btn btn-primary']) ?>
        
        <?php \yii\widgets\ActiveForm::end(); ?>
    </div>

    <?php if ($dataProvider->getTotalCount() > 0): ?>
        <?= GridView::widget([
            'dataProvider' => $dataProvider,
            'tableOptions' => ['class' => 'table table-striped table-hover'],
            'columns' => [
                ['class' => 'yii\grid\SerialColumn'],

                [
                    'attribute' => 'author_name',
                    'label' => 'Author Name',
                ],
                [
                    'attribute' => 'book_count',
                    'label' => 'Number of Books',
                    'contentOptions' => ['style' => 'text-align: right;'],
                ],
            ],
        ]); ?>
    <?php else: ?>
        <div class="alert alert-info">
            No data found for this year.
        </div>
    <?php endif; ?>

</div>