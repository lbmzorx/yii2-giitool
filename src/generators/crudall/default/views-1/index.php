<?php

use yii\helpers\Inflector;
use yii\helpers\StringHelper;

/* @var $this yii\web\View */
/* @var $generator lbmzorx\giitool\generators\crudall\Generator */

$urlParams = $generator->generateUrlParams($model);
$nameAttribute = $generator->getNameAttribute($model);
$statusCodeList=[];
echo "<?php\n";
?>

use yii\helpers\Html;
use lbmzorx\components\widget\BatchDelete;
use <?= $generator->indexWidgetType === 'grid' ? "yii\\grid\\GridView" : "yii\\widgets\\ListView" ?>;
<?= $generator->enablePjax ? 'use yii\widgets\Pjax;' : '' ?>
<?=$generator->statusCode? "use lbmzorx\\components\\widgets\\BatchUpdate;":''?>


/* @var $this yii\web\View */
/* @var $searchModel <?=ltrim($generator->searchNamespace, '\\').'\\'.$model. " */\n"?>
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = <?= $generator->generateString(Inflector::pluralize(Inflector::camel2words(StringHelper::basename($model)))) ?>;
$this->params['breadcrumbs'][] = $this->title;
$this->registerCss(\<\<\<\STYLE
        p .btn{margin-top:5px;}
STYLE
);
?>
<div class="<?= Inflector::camel2id(StringHelper::basename($model)) ?>-index">
    <?='<?='?> \yii\widgets\Breadcrumbs::widget([
        'links' => isset($this->params['breadcrumbs']) ? $this->params['breadcrumbs'] : [],
    ]) <?='?>'?>
    <div class="panel">
        <div class="panel-body">
<?= $generator->enablePjax ? "    <?php Pjax::begin(); ?>\n" : '' ?>
<?php if(!empty($generator->searchNamespace)): ?>
<?= "    <?php " . ($generator->indexWidgetType === 'grid' ? "// " : "") ?>echo $this->render('_search', ['model' => $searchModel]); ?>
<?php endif; ?>

    <p>
        <?= "<?= " ?>Html::a('<i class="fa fa-plus-square"></i> '.<?= $generator->generateString('Create ' . Inflector::camel2words(StringHelper::basename($model))) ?>, ['create'], ['class' => 'btn btn-success']) ?>
        <?= "<?= " ?>BatchDelete::widget(['name'=>$generator->generateString('Batch Deletes')]) ?>
<?php if($generator->statusCode):?>
<?php $changeStatus=$generator->generateGetStatusCode($mode); foreach ($changeStatus as $v):?>
        <?= "<?= " ?>BatchUpdate::widget([ 'name'=>\Yii::t('model','<?=Inflector::camel2words($v)?>'),'attribute'=>'<?=$v?>','btnIcon'=>'<?=$v?>', ]) ?>
<?php endforeach;?>
<?php endif;?>
    </p>

<?php if ($generator->indexWidgetType === 'grid'): ?>
    <?= "<?= " ?>GridView::widget([
        'dataProvider' => $dataProvider,
        'page' =>[
            'class'=>\lbmzorx\components\widgets\JumpPager::className(),
            'firstPageLabel'=>Yii::t('lbmzorx','first'),
            'nextPageLabel'=>Yii::t('lbmzorx','next'),
            'prevPageLabel'=>Yii::t('lbmzorx','prev'),
            'lastPageLabel'=>Yii::t('lbmzorx','last'),
            'jButtonLabel' =>Yii::t('lbmzorx','Jump'),
            'sButtonLabel' =>Yii::t('lbmzorx','PageSize'),
        ],
        <?= !empty($generator->searchModelClass) ? "'filterModel' => \$searchModel,\n        'columns' => [\n" : "'columns' => [\n"; ?>
            ['class' => 'yii\grid\CheckboxColumn'],

<?php
$count = 0;
if (($tableSchema = $generator->getTableSchema($model)) === false) {
    foreach ($generator->getColumnNames() as $name) {
        if (++$count < 6) {
            echo "            '" . $name . "',\n";
        } else {
            echo "            //'" . $name . "',\n";
        }
    }
} else {
    foreach ($tableSchema->columns as $column) {
        $format = $generator->generateColumnFormat($column);

        $statusCode = $generator->generateStatusCodeColum($column->name);
        if($statusCode){
            echo "            " .$statusCode.",\n";
            $statusCodeList[]=$generator->generateStatusCodeDom($column->name);
        }else{
            $datetime=$generator->generateTimeDate($column->name);
            if($datetime){
                echo "            " .$datetime.",\n";
            }else{
                if (++$count < 6) {
                    if($column->name == 'sort'){
                        echo "            [\n            \t'attribute'=>'sort',\n            \t'class'=>'lbmzorx\\components\\grid\\SortColumn',\n            ],";
                    }else{
                        echo "            '" . $column->name . ($format === 'text' ? "" : ":" . $format) . "',\n";
                    }
                } else {
                    if($column->name == 'sort'){
                        echo "            //[\n            //\t'attribute'=>'sort',\n            //\t'class'=>'lbmzorx\\components\\grid\\SortColumn',\n            //],\n";
                    }else{
                        echo "            //'" . $column->name . ($format === 'text' ? "" : ":" . $format) . "',\n";
                    }
                }
            }
        }
    }
}
?>

            ['class' => 'yii\grid\ActionColumn'],
        ],
    ]); ?>
<?php else: ?>
    <?= "<?= " ?>ListView::widget([
        'dataProvider' => $dataProvider,
        'itemOptions' => ['class' => 'item'],
        'itemView' => function ($model, $key, $index, $widget) {
            return Html::a(Html::encode($model-><?= $nameAttribute ?>), ['view', <?= $urlParams ?>]);
        },
    ]) ?>
<?php endif; ?>
<?= $generator->enablePjax ? "    <?php Pjax::end(); ?>\n" : '' ?>
        </div>
    </div>
</div>
<?php
    if(!empty($statusCodeList)){
        foreach ($statusCodeList as $v){
            echo $v;
        }
    }
?>
