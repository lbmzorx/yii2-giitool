<?php

use yii\helpers\Inflector;
use yii\helpers\StringHelper;

/* @var $this yii\web\View */
/* @var $generator yii\gii\generators\crud\Generator */

/* @var $model \yii\db\ActiveRecord */
$model = new $generator->modelClass();
$safeAttributes = $model->safeAttributes();
if (empty($safeAttributes)) {
    $safeAttributes = $model->attributes();
}
$count=0;
echo "<?php\n";
?>

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use <?=$generator->modelClass?>;
<?php if($generator->changeStatus):?>
use common\components\behavior\StatusCode;
<?php endif;?>
/* @var $this yii\web\View */
/* @var $model <?= ltrim($generator->modelClass, '\\') ?> */
/* @var $form yii\widgets\ActiveForm */
?>
<div class="panel">
    <div class="panel-heading">
        <h3 class="panel-title"><?='<?'?>=\Yii::t('<?=$generator->messageCategory?>',Html::encode($this->title))<?='?>'?></h3>
    </div>
    <div class="panel-body">
<div class="<?= Inflector::camel2id(StringHelper::basename($generator->modelClass)) ?>-form">

    <?= "<?php " ?>$form = ActiveForm::begin(); ?>

<?php foreach ($generator->getColumnNames() as $attribute) {
    if (in_array($attribute, $safeAttributes)) {
        if($generator->timedate){
            $time=explode(',',$generator->timedate);
            $status=false;
            foreach ($time as $s){
                if($s==$attribute){
                    $status=true;
                }
            }
            if($status==true){
                continue;
            }
        }

        if(strtolower($attribute)=='id'){
            continue;
        }

        $count++;
        if($count==1){
            echo "<div class=\"row\">"."\n";
            echo "\t<div class=\"col-lg-3 col-sm-3\">\n";
            echo "\t    <?= " . $generator->generateActiveField($attribute) . " ?>\n";
            echo "\t</div>\n";
        }elseif($count>=3){
            $count=0;
            echo "\t<div class=\"col-lg-3 col-sm-3\">\n";
            echo "\t    <?= " . $generator->generateActiveField($attribute) . " ?>\n";
            echo "\t</div>\n</div>\n";
        }else{
            echo "\t<div class=\"col-lg-3 col-sm-3\">\n";
            echo "\t    <?= " . $generator->generateActiveField($attribute) . " ?>\n";
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
