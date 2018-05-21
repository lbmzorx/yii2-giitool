<?php

/* @var $modelClassName string related model class name */
/* @var $generator lbmzorx\giitool\generators\modelsall\Generator */
$modelFullClassName = $className;

$ns=ltrim($generator->ns,"\\");
$modelFullClassName = $ns. '\\' . $modelFullClassName;


echo "<?php\n";

$statusCodes=[];

?>
namespace <?= $generator->dataNamespace?>;

use Yii;
use <?= $modelFullClassName?> as BaseModel<?=$className?>;
use yii\caching\TagDependency;

/**
* This is the data class for [[<?= $modelFullClassName ?>]].
* Data model definde model behavior and status code.
* @see \<?= $modelFullClassName . "\n" ?>
*/
class <?= $className?> extends BaseModel<?=$className."\n"?>
{
    /**
     * The cache tag
     */
    const CACHE_TAG='<?=str_replace('\\','_',$generator->dataNamespace.'\\'.$className)?>';

<?php foreach ($properties as $property):?>
<?php if(!empty($property['code'])):?>

<?php $code='';$key='';foreach ($property['code'] as $k=>$v):$code.=is_numeric($k)?$k.'=>\''.$v.'\',':'\''.$k.'\'=>\''.$v.'\',';$key.=is_numeric($k)?$k.',':'\''.$k.'\',';?>
    const <?=strtoupper($property['name'])?>_<?=strtoupper(str_replace(' ','_',$v))?>=<?=is_numeric($k)?$k:'\''.$k.'\''?>;
<?php endforeach;?>
    /**
    * <?=$property['label']."\n"?>
    * <?=$property['comment']."\n"?>
    * @var array $<?=$property['name']?>_code
    */
    public static $<?=$property['name']?>_code = [<?=$code?>];<?php $statusCodes[$property['name']]=$key;?>

<?php endif;?>
<?php endforeach;?>

    /**
     * get status code attribute list
     */
    public static function statusCodes(){
        return [
<?php if($statusCodes):?>
            '<?=implode('\',\'',array_keys($statusCodes))?>'
<?php endif;?>
        ];
    }

    /**
    * @inheritdoc
    */
    public function rules()
    {
        return array_merge(parent::rules(),[
<?php if(!empty($statusCodes)):?>
<?php
    $keyRanges=[];
    foreach ($statusCodes as $name=>$v){
        $keyRanges[$v][]=$name;
    }
?>
<?php foreach ($keyRanges as $k=>$v):?>
            [['<?=implode('\',\'',$v)?>'], 'in', 'range' => [<?=$k?>]],
<?php endforeach;?>
<?php endif;?>
<?php
$defaults=[];
foreach ($tableSchema->columns as $colum){
    $value=is_string($colum->defaultValue)?"'".$colum->defaultValue."'":$colum->defaultValue;
    if($value===null){
        continue;
    }
    $defaults[$value][]=$colum->name;
}
?>
<?php foreach ($defaults as $value=>$colum):?>
            [['<?=implode('\',\'',$colum)?>'], 'default', 'value' =><?=$value?>,],
<?php endforeach;?>
        ]);
    }

    /**
    * @inheritdoc
    */
    public function scenarios()
    {
        return [
            'default' => [
<?php foreach ($properties as $name=>$property):?>
                '<?=$name?>',
<?php endforeach;?>
            ],
            'search' => [
<?php foreach ($properties as $name=>$property):?>
                '<?=$name?>',
<?php endforeach;?>
            ],
            'view' => [
<?php foreach ($properties as $name=>$property):?>
                '<?=$name?>',
<?php endforeach;?>
            ],
            'update' => [
<?php foreach ($properties as $name=>$property):?>
<?php if(strtolower($name) == 'id') continue;?>
<?php if(in_array($tableName,$generator->modelTree) && in_array($name,['level','path'])) continue;?>
<?php if($generator->timeAdd && preg_match('/'.$name.'/',$generator->timeAdd)) continue;?>
<?php if($generator->timeUpdate && preg_match('/'.$name.'/',$generator->timeUpdate)) continue;?>
                '<?=$name?>',
<?php endforeach;?>
            ],
            'create' => [
<?php foreach ($properties as $name=>$property):?>
<?php if(strtolower($name) == 'id') continue;?>
<?php if(in_array($tableName,$generator->modelTree) && in_array($name,['level','path'])) continue;?>
<?php if($generator->timeAdd && preg_match('/'.$name.'/',$generator->timeAdd)) continue;?>
<?php if($generator->timeUpdate && preg_match('/'.$name.'/',$generator->timeUpdate)) continue;?>
                '<?=$name?>',
<?php endforeach;?>
            ],
        ];
    }

<?php
    $colums=array_keys($labels);
    $timeAdds = $generator->generateTimeAdd($colums);
    $timeUpdates=$generator->generateTimeUpdate($colums);

?>
<?php if($timeAdds||$timeUpdates||!empty($statusCodes) || (in_array($tableName,$generator->modelTree))):?>
    public function behaviors()
    {
        return [
<?php if( $timeAdds || $timeUpdates):?>
            'timeUpdate'=>[
                'class' => \yii\behaviors\TimestampBehavior::className(),
                'attributes' => [
<?php if($timeAdds):?>
<?php
            $timeAdd=implode('\',\'',$timeAdds);
?>
                    self::EVENT_BEFORE_INSERT => ['<?=$timeAdd ?>'],
<?php endif;?>
<?php if($timeUpdates):?>
<?php
    $timeUpdate=implode('\',\'',$timeUpdates);
?>
                    self::EVENT_BEFORE_UPDATE => ['<?=$timeUpdate?>'],
<?php endif;?>
                ],
            ],
<?php endif;?>
<?php if(!empty($statusCodes)):?>
            'getStatusCode'=>[
                'class' => \lbmzorx\components\behavior\StatusCode::className(),
                'category' =>'<?=$generator->statusCodeMessage?>',
            ],
<?php endif;?>
<?php if($generator->withOneUser):?>
<?php if(array_key_exists('user_id',$labels)):?>
            'withOneUser'=>[
                'class' => \lbmzorx\components\behavior\WithOneUser::className(),
                'userClass'=> User::ClassName(),
            ],
<?php endif;?>
<?php endif;?>
<?php if(in_array($tableName,$generator->modelTree)):?>
            'parent_id'=>[
                'class'=>\yii\behaviors\AttributesBehavior::className(),
                'attributes'=>[
                    'parent_id'=>[
                        self::EVENT_BEFORE_INSERT=>[$this,'treeBuild'],
                        self::EVENT_BEFORE_UPDATE=>[$this,'treeBuild'],
                    ],
                ],
            ],
<?php endif;?>
        ];
    }
<?php endif;?>
<?php if($generator->relation && !empty($generator->relationTable[$tableName])):?>
<?php $link=[];foreach ($generator->relationTable[$tableName] as $k=>$v):?>

<?php if (in_array($v,$generator->modelNames)):$link[$k]=$v?>
    /**
     * @return \yii\db\ActiveQuery
     */
    public function get<?=$v?>(){
        return $this->hasOne(<?=$v?>::className(),['id'=>'<?=$k?>']);
    }
<?php endif;?>
<?php endforeach;?>

<?php if(!empty($link)):?>
    /**
     * get relation columns
     * @return array
     */
    public static function columnRetions(){
        return [
<?php foreach ($link as $k=>$v):?>
            '<?=$k?>'=>'<?=$v?>',
<?php endforeach;?>
        ];
    }
<?php endif?>
<?php endif;?>

    /**
     * If is tree which have parent_id
     * @return boolean
     */
    public static function isTree(){
<?php if( $generator->modelTree ):?>
        return <?=in_array($tableName,$generator->modelTree)?'true':'false'?>;
<?php endif;?>
    }

<?php if(in_array($tableName,$generator->modelTree)):?>
    /**
     * Build tree
     * @return mixed
     */
    public function treeBuild($event, $attribute){
        if($this->$attribute==0){
            if($this->hasAttribute('level')) $this->level=0;
            if($this->hasAttribute('path')) $this->level=0;
        }else{
            $parent_model=self::findOne($this->$attribute);
            if($parent_model){
                if($this->hasAttribute('level')) $this->level=$parent_model->level+1;
                if($this->hasAttribute('path')) $this->path=$parent_model->path.','.$parent_model->id;
            }else{
                $this->$attribute=0;
                if($this->hasAttribute('level')) $this->level=0;
                if($this->hasAttribute('path')) $this->level=0;
            }
        }
        return $this->$attribute;
    }
<?php endif;?>

    public function afterSave($insert , $changedAttributes)
    {
        TagDependency::invalidate(\yii::$app->cache,self::CACHE_TAG);
        parent::afterSave($insert , $changedAttributes); // TODO: Change the autogenerated stub
    }

    public function afterDelete()
    {
        TagDependency::invalidate(\yii::$app->cache,self::CACHE_TAG);
        parent::afterDelete(); // TODO: Change the autogenerated stub
    }

}
