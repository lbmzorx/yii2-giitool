<?php

use yii\helpers\Inflector;
use yii\helpers\StringHelper;

/* @var $this yii\web\View */
/* @var $generator lbmzorx\giitool\generators\crudall\Generator */

$urlParams = $generator->generateUrlParams($model);

echo "<?php\n";
?>

use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model <?= ltrim($generator->modelNamespace, '\\').'\\'.$model ?> */

$this->title = $model-><?= $generator->getNameAttribute($model) ?>;
$this->params['breadcrumbs'][] = ['label' => <?= $generator->generateString(Inflector::pluralize(Inflector::camel2words($model))) ?>, 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="<?= Inflector::camel2id($model) ?>-view">
    <?='<?='?> \yii\widgets\Breadcrumbs::widget([
    'links' => isset($this->params['breadcrumbs']) ? $this->params['breadcrumbs'] : [],
    ]) <?='?>'?>

    <div class="panel">
        <div class="panel-heading">
            <h3 class="panel-title"><?='<?'?>=\Yii::t('<?=$generator->messageCategory?>',Html::encode($this->title))<?='?>'?></h3>
        </div>
        <div class="panel-body">
    <p>
        <?= "<?= " ?>Html::a('<i class="fa fa-pencil"></i> '.<?= $generator->generateString('Update') ?>, ['update', <?= $urlParams ?>], ['class' => 'btn btn-primary']) ?>
        <?= "<?= " ?>Html::a('<i class="fa fa-trash"></i> '.<?= $generator->generateString('Delete') ?>, ['delete', <?= $urlParams ?>], [
            'class' => 'btn btn-danger',
            'data' => [
                'confirm' => <?= $generator->generateString('Are you sure you want to delete this item?') ?>,
                'method' => 'post',
            ],
        ]) ?>
    </p>

    <?= "<?= " ?>DetailView::widget([
        'model' => $model,
        'attributes' => [
<?php
if (($tableSchema = $generator->getTableSchema($model)) === false) {
    foreach ($generator->getColumnNames($model) as $name) {
        if($string=$generator->generateStatusCodeRow($model,$name)){
            echo $string;
        }else{
            echo "            '" . $name . "',\n";
        }
    }
} else {
    foreach ($generator->getTableSchema($model)->columns as $column) {
        $format = $generator->generateColumnFormat($column);
        if( preg_match('/(time)|(_at$)/', $column->name)){
            $format='datetime';
        }
        if( $string=$generator->generateStatusCodeRow($model,$column->name)){
            echo $string;
        }else{
            echo "            '" . $column->name . ($format === 'text' ? "" : ":" . $format) . "',\n";
        }
    }
}
?>
        ],
    ]) ?>
</div>
    </div>
</div>
