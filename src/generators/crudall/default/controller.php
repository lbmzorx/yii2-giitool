<?php
/**
 * This is the template for generating a CRUD controller class file.
 * @var $generator
 */
use yii\helpers\StringHelper;

$baseCtrlNsClass = str_replace('Controller','',StringHelper::basename($generator->commonControllerClass));
$baseCtrlNs=StringHelper::dirname(ltrim($generator->commonControllerClass, '\\'));

$modelNamespace= $generator->modelNamespace;

echo "<?php\n";
?>

namespace <?= StringHelper::dirname(ltrim($generator->namespace, '\\')) ?>;

use Yii;
use <?=$baseCtrlNs.'\\'.$baseCtrlNsClass?>;
use <?= $modelNamespace.'\\'.$model ?>;
<?php if(isset($searchModel)):?>
use <?= trim($generator->searchNamespace,'\\').'\\'.$searchModel ?> as SearchModel;
<?php endif;?>
/**
 * <?= $controllerClass ?> implements the CRUD actions for <?= $modelNamespace.'\\'.$model ?> model.
 */
class <?= $controllerClass ?> extends <?= $baseCtrlNsClass ."\n" ?>
{
    /**
     * @inheritdoc
     */
    public function init(){
        parent::init();
<?php if(isset($searchModel)):?>
        $this->modelNameIndexSearch =SearchModel::className();
<?php else:?>
        $this->modelNameIndex       =<?=$model?>::className();
<?php endif;?>
        $this->modelNameView        =<?=$model?>::className();
        $this->modelNameCreate      =<?=$model?>::className();
        $this->modelNameUpdate      =<?=$model?>::className();
        $this->modelNameDelete      =<?=$model?>::className();
        $this->modelNameSort        =<?=$model?>::className();
        $this->modelNameChangeStatus=<?=$model?>::className();
    }

    /**
     * @inheritdoc
     */
    public function actions()
    {
        return parent::actions();
    }

}
