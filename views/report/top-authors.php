<?php

use yii\helpers\Html;
use yii\grid\GridView;

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

    <!-- Форма для выбора года -->
    <div class="year-selector">
        <?php $form = \yii\widgets\ActiveForm::begin([
            'method' => 'get',
            'action' => ['report/top-authors']
        ]); ?>
        
        <?= $form->field(new \yii\base\DynamicModel(['year' => $year]), 'year')->input('number', [
            'min' => '1900',
            'max' => date('Y'),
            'value' => $year
        ])->label('Select Year') ?>
        
        <?= Html::submitButton('Show Report', ['class' => 'btn btn-primary']) ?>
        
        <?php \yii\widgets\ActiveForm::end(); ?>
    </div>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
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

</div>