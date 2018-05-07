<?php

/* @var $modelClassName string related model class name */
$modelFullClassName = $className;

$ns=ltrim($generator->ns,"\\");
$modelFullClassName = $ns. '\\' . $modelFullClassName;


echo "<?php\n";

$statusCodes=[];

?>
namespace <?= $generator->dataNamespace?>;

use Yii;
use <?= $modelFullClassName?> as BaseModel<?=$className?>;

/**
* This is the data class for [[<?= $modelFullClassName ?>]].
* Data model definde model behavior and status code.
* @see \<?= $modelFullClassName . "\n" ?>
*/
class <?= $className?> extends BaseModel<?=$className."\n"?>
{
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
<?php if($generator->timeAdd && preg_match('/'.$name.'/',$generator->timeAdd)) continue;?>
<?php if($generator->timeUpdate && preg_match('/'.$name.'/',$generator->timeUpdate)) continue;?>
                '<?=$name?>',
<?php endforeach;?>
            ],
            'create' => [
<?php foreach ($properties as $name=>$property):?>
<?php if(strtolower($name) == 'id') continue;?>
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
<?php if($timeAdds||$timeUpdates||!empty($statusCodes)):?>
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
                'class' => \lbmzorx\components\behaviors\StatusCode::className(),
            ],
<?php endif;?>
<?php if($generator->withOneUser):?>
<?php if(array_key_exists('user_id',$labels)):?>
            'withOneUser'=>[
                'class' => \lbmzorx\components\behaviors\WithOneUser::className(),
                'userClass'=> User::ClassName(),
            ],
<?php endif;?>
<?php endif;?>
        ];
    }
<?php endif;?>

}
