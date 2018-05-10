<?php

use yii\helpers\Inflector;
use yii\helpers\StringHelper;

/* @var $this yii\web\View */
/* @var $generator lbmzorx\giitool\generators\crudall\Generator */

echo "<?php\n";
?>

use yii\helpers\Html;
use yii\widgets\ActiveForm;
<?php
$class = trim($generator->modelNamespace,'\\').'\\'.$model;
if(method_exists($class,'statusCodes')){
    $status=$class::statusCodes();
    if(count($status)>0){
        echo "use lbmzorx\\components\\behavior\\StatusCode;\n";
        echo "use {$generator->searchNamespace}\\$model as SearchModel;\n";
    }
}
?>
/* @var $this yii\web\View */
/* @var $model <?= ltrim($generator->searchNamespace, '\\').'\\'.$model ?> */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="<?= Inflector::camel2id(StringHelper::basename($model)) ?>-search">

    <?= "<?php " ?>$form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
<?php if ($generator->enablePjax): ?>
        'options' => [
            'data-pjax' => 1
        ],
<?php endif; ?>
    ]); ?>

<?php
$count = 0;
foreach ($generator->getColumnNames($model) as $attribute) {
    $str='<?='.$generator->generateActiveSearchField($model,$attribute)."?>";
    if ( ++$count==1) {
        echo "<div class=\"row\">"."\n";
        echo "\t<div class=\"col-lg-2 col-sm-2\">\n";
        echo "\t\t".$str."\n";
        echo "\t</div>\n";
    } elseif($count>=5) {
        $count=0;
        echo "\t<div class=\"col-lg-2 col-sm-2\">\n";
        echo "\t\t".$str."\n";
        echo "\t</div>\n</div>\n";
    }else{
        echo "\t<div class=\"col-lg-2 col-sm-2\">\n";
        echo "\t\t".$str."\n";
        echo "\t</div>\n";
    }
}
if($count!=0){
    echo "</div>";
}
?>
    <div class="form-group">
        <?= "<?= " ?>Html::submitButton(<?= $generator->generateString('Search') ?>, ['class' => 'btn btn-primary']) ?>
        <?= "<?= " ?>Html::resetButton(<?= $generator->generateString('Reset') ?>, ['class' => 'btn btn-default']) ?>
    </div>

    <?= "<?php " ?>ActiveForm::end(); ?>

</div>
