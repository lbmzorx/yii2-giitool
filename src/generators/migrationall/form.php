<?php
/* @var $this yii\web\View */
/* @var $form yii\widgets\ActiveForm */
/* @var $generator yii\gii\generators\form\Generator */

echo $form->field($generator, 'tableName');

echo $form->field($generator, 'only');
echo $form->field($generator, 'except');

echo $form->field($generator, 'migrationPath');
echo $form->field($generator, 'migrationTime')->widget('yii\widgets\MaskedInput', [
    'mask' => '999999_999999'
]);
echo $form->field($generator, 'migrationName');
echo $form->field($generator, 'db');
echo $form->field($generator, 'useTablePrefix')->checkbox();
echo $form->field($generator, 'generateRelations')->checkbox();
echo $form->field($generator, 'createTableIfNotExists')->dropDownList(['0' => 'Throw Error', '1' => 'Skip table']);
echo $form->field($generator, 'disableFkc')->checkbox();
echo $form->field($generator, 'isSafeUpDown')->checkbox();

echo $form->field($generator, 'dataLimit');
echo $form->field($generator, 'dataOrderBy')->radioList(['desc'=>'desc','asc'=>'asc']);