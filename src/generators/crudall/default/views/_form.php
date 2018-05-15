<?php

use yii\helpers\Inflector;
use yii\helpers\StringHelper;

/* @var $this yii\web\View */
/* @var $generator lbmzorx\giitool\generators\crudall\Generator */

/* @var $model \yii\db\ActiveRecord */
$class=ltrim($generator->modelNamespace, '\\').'\\'.$model;
$modelObject = new $class();
$safeAttributes = $modelObject->safeAttributes();
if (empty($safeAttributes)) {
    $safeAttributes = $modelObject->attributes();
}
$count=0;
echo "<?php\n";
?>

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use <?=$class?>;
<?php if($generator->statusCode):?>
use lbmzorx\components\behavior\StatusCode;
<?php endif;?>
/* @var $this yii\web\View */
/* @var $model <?= $class ?> */
/* @var $form yii\widgets\ActiveForm */
?>
<div class="panel">
    <div class="panel-heading">
        <h3 class="panel-title"><?='<?'?>=\Yii::t('<?=$generator->messageCategory?>',Html::encode($this->title))<?='?>'?></h3>
    </div>
    <div class="panel-body">
<div class="<?= Inflector::camel2id($model) ?>-form">

    <?= "<?php " ?>$form = ActiveForm::begin(); ?>

<?php foreach ($generator->getColumnNames($model) as $attribute) {
    if (in_array($attribute, $safeAttributes)) {
        if(in_array($attribute,['add_time','edit_time','created_at','updated_at'])){
            continue;
        }
        if(strtolower($attribute)=='id'){
            continue;
        }
        if(method_exists($class,'isTree') && $class::isTree()){
            if(in_array($attribute,['level','path'])){
                continue;
            }
        }

        $count++;
        if($count==1){
            echo "<div class=\"row\">"."\n";
            echo "\t<div class=\"col-lg-3 col-sm-3\">\n";
            echo "\t    <?= " . $generator->generateActiveField($model,$attribute) . " ?>\n";
            echo "\t</div>\n";
        }elseif($count>=4){
            $count=0;
            echo "\t<div class=\"col-lg-3 col-sm-3\">\n";
            echo "\t    <?= " . $generator->generateActiveField($model,$attribute) . " ?>\n";
            echo "\t</div>\n</div>\n";
        }else{
            echo "\t<div class=\"col-lg-3 col-sm-3\">\n";
            echo "\t    <?= " . $generator->generateActiveField($model,$attribute) . " ?>\n";
            echo "\t</div>\n";
        }
    }
} ?>
    <?php
        if($count!=0){
            echo "</div>";
        }
    ?>

    <div class="row">
        <div class="col-lg-12 col-sm-12">
            <div class="form-group">
                <?= "<?= " ?>Html::submitButton(Yii::t('<?=$generator->messageCategory?>',<?= $generator->generateString('Save') ?>), ['class' => 'btn btn-success']) ?>
            </div>
        </div>
    </div>
    <?= "<?php " ?>ActiveForm::end(); ?>
</div>
    </div>
</div>
