<?php

use yii\gii\generators\model\Generator;

/* @var $this yii\web\View */
/* @var $form yii\widgets\ActiveForm */
/* @var $generator yii\gii\generators\model\Generator */

echo $form->field($generator, 'only');
echo $form->field($generator, 'except');

echo $form->field($generator, 'db');
echo $form->field($generator, 'mdDoc');
echo $form->field($generator, 'templateMdClass');