<?php
/* @var $this yii\web\View */
/* @var $form yii\widgets\ActiveForm */
/* @var $generator yii\gii\generators\form\Generator */
use lbmzorx\giitool\generators\addapp\Generator;
echo $form->field($generator, 'appname');
echo $form->field($generator, 'type')->radioList(Generator::$type_code);
echo $form->field($generator, 'isinit')->radioList(Generator::$isinit_code);
echo $form->field($generator, 'env')->radioList(Generator::$env_code);
