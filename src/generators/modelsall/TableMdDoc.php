<?php
/**
 * Created by Administrator.
 * Date: 2018/8/11 19:17
 * github: https://github.com/lbmzorx
 */

namespace lbmzorx\giitool\generators\modelsall;

use yii\base\InvalidParamException;
use yii\db\ColumnSchema;
use  yii\db\TableSchema;
use yii\base\Component;
use yii\helpers\Inflector;
use yii\helpers\StringHelper;

class TableMdDoc extends Component
{

    /**
     * @var $tableSchema TableSchema
     */
    public $tableSchema;

    public $tableName;

    public $db;

    public $tableTemplate;


    public function getDb(){
        if($this->db==null){
            $this->db=\yii::$app->db;
        }
        return $this->db;
    }

    public function getTableSchema(){
        if($this->tableSchema==null){
            if($this->tableName==null){
                throw new InvalidParamException('Table name was null!');
            }else{
                $this->tableSchema=\yii::$app->db->getTableSchema($this->tableName);
            }
        }
        return $this->tableSchema;
    }

    public function setTableSchema($tableSchema){
        if($tableSchema instanceof TableSchema){
            $this->tableSchema=$tableSchema;
            $this->tableName=$tableSchema->name;
        }else{
            throw new InvalidParamException('Table shema is invalid !');
        }
    }

    public function generateMdString(){
        $tableSchema=$this->getTableSchema();
        $template = $this->tableMdDefaultTemplate($tableSchema);
        $template .=$this->getTableCreateMd($tableSchema);

        $template.=$this->generateColumnMd($tableSchema->columns);


        return $template;
    }

    /**
     * @param $tableSchema TableSchema
     */
    protected function tableMdDefaultTemplate($tableSchema){

        $simplename=substr($tableSchema->name,strlen($this->getDb()->tablePrefix));
        $splitname=$this->getSplitName($tableSchema);
        $template=<<<TEMPLATE

## <a name="{$tableSchema->name}">表$tableSchema->name</a> 

{$splitname}

1. 表名:{$simplename}
1. 加前缀:{$tableSchema->name}
2. 全名:{$tableSchema->fullName}

TEMPLATE;

        if(!empty($tableSchema->primaryKey)){
            $key=implode(',',$tableSchema->primaryKey);
            $template .=<<<primary
3. **主键** : ```$key```

primary;
        }

        if(!empty($tableSchema->foreignKeys)){
            $template .=<<<primary
4. **外键** :

| 外键名称 | 本表字段 | 外表名 | 外表字段 |
|------|---|-------|---------|

primary;
            $frtable='';
            foreach ($tableSchema->foreignKeys as $key=>$foreignKey){

                if(is_array($foreignKey)){
                    foreach ($foreignKey as $kk=>$vv){
                        if($kk==0){
                            $frtable=$foreignKey;
                            continue;
                        }else{
                            $template.='| '.$key.' | '.$kk.' | '.$frtable.' | '.$vv." |\n";
                        }
                    }
                }

            }
        }

        return $template;
    }

    /**
     * @param $columns array
     * @return string
     */
    public function generateColumnMd($columns){
        $count=count($columns);
        $template=<<<column

### 表列详细信息 共有 **{$count}** 列

| 名称 | 为空 | 类型 | 长度 | 精度 | 默认| 主键 | 自增 |  注释 |
|------|---------|-----|------|-----|-----|---------|------|-----|

column;

        foreach ($columns as $key=>$column){
            /**
             * @var $column \yii\db\mysql\ColumnSchema
             */

            $allowNull=$column->allowNull?'**&radic;**':'	&times;';
            $primaryKey=$column->isPrimaryKey?'**&radic;**':'	&times;';
            $autoIncrement=$column->autoIncrement?'**&radic;**':'	&times;';
            $default=$column->defaultValue===null && $column->allowNull==true ?'null':$column->defaultValue;

            $template.="| {$column->name} | {$allowNull} | {$column->dbType} | {$column->size} | {$column->precision} | {$default} |  {$primaryKey} | {$autoIncrement}| {$column->comment} |\n";
            $this->commentCode($column);
        }
        $template.=$this->getCommentMd();

        return $template;
    }


    protected function getCommentMd(){

        $template='';
        foreach ($this->commitCodes as $key=>$commitCode){
            if(!empty($commitCode['tran'])){

                $ommit=$commitCode['commit']?($commitCode['label'].'。'.$commitCode['commit']):$commitCode['label'];

                $template.=<<<template

- 列: {$key}

> $ommit

| 值 | 说明 | EN |
|----|-----|-----|

template;
                foreach ($commitCode['tran'] as $kk=>$vv){
                    $tran=isset($commitCode['code'][$kk])?$commitCode['code'][$kk]:'';
                    $template.="| {$kk} | {$vv} | {$tran} |\n";
                }
            }
        }

        if($template!=''){
            $template= "\n### 状态码参考\n".$template;
        }
        return $template;
    }


    public $commitCodes=[];
    protected function commentCode($column)
    {
        list($code,$tran,$commit,$label)=$this->generateCommentInfo($column->comment);
        $this->commitCodes[$column->name]=[
            'comment' => $column->comment,
            'label'=>$label,
            'code'=>$code,
            'tran'=>$tran,
            'commit'=>$commit,
        ];
    }


    /**
     * property info
     * @param $comment
     * @return array
     */
    public function generateCommentInfo($comment){
        $code=[];
        $tran=[];
        $commit='';
        $label='';
        if( $point=mb_strpos($comment,'.')){
            $label = mb_substr($comment,0,$point);
            $other =mb_substr($comment,$point);

            $others = array_filter(explode('.',$other));
            foreach ($others as $v){
                if( $split=mb_strpos($v,':')){
                    $name=mb_substr($v,0,$split);
                    $contents=trim(mb_substr($v,$split),':');
                    if($name=='commit'){
                        $commit=$contents;
                        continue;
                    }
                    if(mb_strpos($contents,'=')!==false){
                        $contents=array_filter(explode(',',$contents));
                        foreach ($contents as $k=>$content){
                            $tmp=explode('=',$content);
                            if(isset($tmp[0])&&isset($tmp[1])){
                                if($name=='code'){
                                    $code[trim($tmp[0])]=trim($tmp[1]);
                                }elseif($name=='tran'){
                                    $tran[trim($tmp[0])]=trim($tmp[1]);
                                }
                            }
                        }
                    }

                }
            }
        }else{
            $commit=$comment;
            $label=$comment;
        }
        return [$code,$tran,$commit,$label];
    }

    public function getSplitName($tablename){
        $name=substr($tablename->name,strlen(\yii::$app->db->tablePrefix));
        return Inflector::camel2words($name);
    }

    /**
     * @param $tableSchema TableSchema
     * @return string
     */
    protected function getTableCreateMd($tableSchema){
        $info=$this->getTableCreateInfo($tableSchema->name);

        $engine='';
        $cherset='';
        $collate='';
        $comment='';
        if(preg_match('/(ENGINE=\w+)/',$info['Create Table'],$match)){
            $engine=isset($match[1])?substr($match[1],7):'';
        }
        if(preg_match('/(CHARSET=\w+)/',$info['Create Table'],$match)){
            $cherset=isset($match[1])?substr($match[1],8):'';
        }
        if(preg_match('/(COLLATE=\w+)/',$info['Create Table'],$match)){
            $collate=isset($match[1])?substr($match[1],8):'';
        }
        if(preg_match('/(COMMENT=\'.*\')/',$info['Create Table'],$match)){
            $comment=isset($match[1])?substr($match[1],8):'';
            $comment=trim($comment,'\'');
        }


        if(is_array($info) && isset($info['Create Table'])){
            return <<<create
- 引擎:$engine
- 字符集:$cherset
- 字符排序:$collate

{$comment}

### 创建表sql语句

```sql
DROP TABLE IF EXISTS `{$tableSchema->name}`;

{$info['Create Table']}

```

create;
        }else{
            return '';
        }
    }

    /**
     * @param $tablename
     * @return mixed
     */
    protected function getTableCreateInfo($tablename){
        $pdo=$this->getDb()->getMasterPdo();
        $sth =$pdo->prepare('SHOW CREATE TABLE '.$tablename);
        $sth->execute();
        $res=$sth->fetch(\PDO::FETCH_ASSOC);
        return $res;
    }

}