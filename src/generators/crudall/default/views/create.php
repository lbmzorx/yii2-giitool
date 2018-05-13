<?php

use yii\helpers\Inflector;
use yii\helpers\StringHelper;

/* @var $this yii\web\View */
/* @var $generator lbmzorx\giitool\generators\crudall\Generator*/
\yii\web\Response::className();
echo "<?php\n";
?>

use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $model <?= ltrim($generator->modelNamespace, '\\').'\\'.$model ?> */

$this->title = <?= $generator->generateString('Create ' . Inflector::camel2words($model)) ?>;
$this->params['breadcrumbs'][] = ['label' => <?= $generator->generateString(Inflector::pluralize(Inflector::camel2words($model))) ?>, 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="<?= Inflector::camel2id($model) ?>-create">
    <?='<?='?> \yii\widgets\Breadcrumbs::widget([
    'links' => isset($this->params['breadcrumbs']) ? $this->params['breadcrumbs'] : [],
    ]) <?='?>'?>
    <?= "<?= " ?>$this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
