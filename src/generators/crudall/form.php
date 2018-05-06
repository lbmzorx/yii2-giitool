<?php
/* @var $this yii\web\View */
/* @var $form yii\widgets\ActiveForm */
/* @var $generator yii\gii\generators\crud\Generator */
echo $form->field($generator, 'modelNamespace');
echo $form->field($generator, 'namespace');
echo $form->field($generator, 'onlyModel');
echo $form->field($generator, 'exceptModel');
echo $form->field($generator, 'searchNamespace');
echo $form->field($generator, 'isLogin')->checkbox();
echo $form->field($generator, 'commonControllerClass');
echo $form->field($generator, 'statusCode')->checkbox();
echo $form->field($generator, 'sort')->checkbox();

echo $form->field($generator, 'baseControllerClass');
echo $form->field($generator, 'indexWidgetType')->dropDownList([
    'grid' => 'GridView',
    'list' => 'ListView',
]);
echo $form->field($generator, 'enableI18N')->checkbox();
echo $form->field($generator, 'enablePjax')->checkbox();
echo $form->field($generator, 'messageCategory');
