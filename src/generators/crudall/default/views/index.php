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
use <?= $generator->indexWidgetType === 'grid' ? "yii\\grid\\GridView" : "yii\\widgets\\ListView" ?>;
<?= $generator->enablePjax ? "use yii\\widgets\\Pjax;\n" : "\n" ?>
<?=$generator->statusCode? "use lbmzorx\\components\\widget\\BatchUpdate;\n":"\n"?>
<?=$generator->searchNamespace? "use $generator->searchNamespace\\{$model};\n":"{$generator->modelNamespace}\n"?>
<?=($generator->statusCode)?"use lbmzorx\\components\\behavior\\StatusCode;\n":"\n"?>
use lbmzorx\components\widget\BatchDelete;

/* @var $this yii\web\View */
/* @var $searchModel <?=ltrim($generator->searchNamespace, '\\').'\\'.$model. " */\n"?>
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = <?= $generator->generateString(Inflector::pluralize(Inflector::camel2words(StringHelper::basename($model)))) ?>;
$this->params['breadcrumbs'][] = $this->title;
$this->registerCss(<?='<<<'?>STYLE
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
    <p>
        <?='<?'?>=Html::button(Yii::t('<?=$generator->messageCategory?>','Unfolding Search Condition').'  '.Html::tag('i','',['class'=>'fa fa-chevron-down']),['class'=>'btn btn-success ','id'=>'search-button'])?>
        <?="<?php\n"?>
        $this->registerJs(<?="<<<"?>str
var show=false;
$('#search-button').click(function(){
    if(show==true){
        $('#search-button').find('i').addClass('fa-chevron-down');
        $('#search-button').find('i').removeClass('fa-chevron-up');
        show=false;
    }else{
        $('#search-button').find('i').removeClass('fa-chevron-down');
        $('#search-button').find('i').addClass('fa-chevron-up');
        show=true;
    }
    $('#search-panel').toggle('fast');
});
str
);
        ?>
    </p>
    <div class="panel panel-success" id="search-panel" style="display: none">
        <div class="panel-body">
            <?='<?php'?>  echo $this->render('_search', ['model' => $searchModel]); ?>
        </div>
    </div>
<?php endif; ?>

    <p>
        <?= "<?= " ?>Html::a('<i class="fa fa-plus-square"></i> '.<?= $generator->generateString('Create ' . Inflector::camel2words(StringHelper::basename($model))) ?>, ['create'], ['class' => 'btn btn-success']) ?>
        <?= "<?= " ?>BatchDelete::widget(['name'=><?=$generator->generateString('Batch Deletes')?>,'griViewKey'=>GridView::$counter]) ?>
<?php if($generator->statusCode):?>
<?php $changeStatus=$generator->generateGetStatusCode($model); foreach ($changeStatus as $v):?>
        <?= "<?= " ?>BatchUpdate::widget([ 'name'=>\Yii::t('model','<?=Inflector::camel2words($v)?>'),'attribute'=>'<?=$v?>','btnIcon'=>'<?=$v?>','griViewKey'=>GridView::$counter]) ?>
<?php endforeach;?>
<?php endif;?>
    </p>

<?php if ($generator->indexWidgetType === 'grid'): ?>
    <?= "<?= " ?>GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'pager' =>[
            'class'=>\lbmzorx\components\widget\JumpPager::className(),
            'firstPageLabel'=>Yii::t('app','First'),
            'nextPageLabel'=>Yii::t('app','Next'),
            'prevPageLabel'=>Yii::t('app','Prev'),
            'lastPageLabel'=>Yii::t('app','Last'),
            'jButtonLabel' =>Yii::t('app','Jump'),
            'sButtonLabel' =>Yii::t('app','PageSize'),
        ],
        <?= !empty($generator->searchModelClass) ? "'filterModel' => \$searchModel,\n        'columns' => [\n" : "'columns' => [\n"; ?>
            ['class' => 'yii\grid\CheckboxColumn'],

<?php
$count = 0;
if (($tableSchema = $generator->getTableSchema($model)) === false) {
    foreach ($generator->getColumnNames($model) as $name) {
        if (++$count < 6) {
            echo "            '" . $name . "',\n";
        } else {
            echo "            //'" . $name . "',\n";
        }
    }
} else {
    foreach ($tableSchema->columns as $column) {
        $format = $generator->generateColumnFormat($column);

        $statusCode = $generator->generateStatusCodeColum($column->name,$model);
        if($statusCode){
            echo "            " .$statusCode.",\n";
            $statusCodeList[]=$generator->generateStatusCodeDom($column->name,$model);
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
            echo $v."\n";
        }
    }
?>
