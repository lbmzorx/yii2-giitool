<?php
/**
 * Created  Generator.php.
 * Date: 2018/4/22 10:19
 * Emain: lbmzorx@163.com
 * Github: https://github.com/lbmzorx
 */
namespace lbmzorx\giitool\generators\modelsall;

use Yii;
use yii\base\Exception;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;
use yii\gii\CodeFile;
use yii\helpers\FileHelper;
use yii\helpers\Inflector;
use yii\helpers\Json;
use yii\helpers\VarDumper;

class Generator extends \yii\gii\generators\model\Generator
{

    public $only;
    public $except;

    public $tableName='*';
    public $dataNamespace;
    public $timeAdd;
    public $timeUpdate;
    public $statusCode = true;
    public $withOneUser = true;

    public $labelExplain=true;
    public $labelTran=true;
    public $targetLanguage='zh-CN';
    public $statusCodeMessage='statuscode';

    public $relation=true;

    /**
     * @inheritdoc
     */
    public function getName()
    {
        return 'Model All Generator';
    }

    /**
     * @inheritdoc
     */
    public function getDescription()
    {
        return 'This generator generates all models for the specified name.';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['db', 'ns', 'dataNamespace', 'queryNs', 'queryClass', 'queryBaseClass','statusCodeMessage'], 'filter', 'filter' => 'trim'],
            [['ns', 'queryNs','dataNamespace'], 'filter', 'filter' => function ($value) { return trim($value, '\\'); }],
//            [['db', 'ns', 'baseClass',], 'required'],
            [['db', 'queryClass'], 'match', 'pattern' => '/^\w+$/', 'message' => 'Only word characters are allowed.'],
            [['ns', 'baseClass', 'queryNs', 'queryBaseClass'], 'match', 'pattern' => '/^[\w\\\\]+$/', 'message' => 'Only word characters and backslashes are allowed.'],
            [['only','except'], 'match', 'pattern' => '/^([\w ]+\.)?([\w\* ]+)(,([\w ]+\.)?([\w\* ]+))*/', 'message' => 'Only word characters, and optionally spaces, an asterisk and/or a dot are allowed.'],
            [['db'], 'validateDb'],
            [['ns', 'queryNs'], 'validateNamespace'],
            [['baseClass'], 'validateClass', 'params' => ['extends' => ActiveRecord::className()]],
            [['queryBaseClass'], 'validateClass', 'params' => ['extends' => ActiveQuery::className()]],
            [['generateRelations'], 'in', 'range' => [self::RELATIONS_NONE, self::RELATIONS_ALL, self::RELATIONS_ALL_INVERSE]],
            [['generateLabelsFromComments', 'useTablePrefix', 'useSchemaName', 'generateQuery', 'generateRelationsFromCurrentSchema'], 'boolean'],
            [['enableI18N'], 'boolean'],
            [['messageCategory'], 'validateMessageCategory', 'skipOnEmpty' => false],
            [['statusCodeMessage'], 'validateStatusCodeMessageCategory', 'skipOnEmpty' => false],
            [['only','except','dataNamespace','timeUpdate','timeAdd',],'string'],
            [['dataNamespace','statusCode','timeUpdate','timeAdd'] , 'filter' , 'filter' => 'trim'] ,
            [['statusCode','withOneUser','labelExplain','labelTran','relation',] , 'boolean' ,] ,
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return array_merge(parent::attributeLabels(), [
            'only' => 'Only',
            'except' => 'Except',
            'dataNamespace' => 'Data Model Namespace' ,
            'timeAdd'=>'insert event insert timestamp',
            'timeUpdate'=>'update event update timestamp',
            'statusCode'=>"Behavior of Status Code",
            'withOneUser'=>'With One User',
            'labelExplain'=>'Table commit be used to explain label',
            'labelTran'=>'Tanslation label to translation file',
            'targetLanguage'=>'Translation label to target language by used table comments',
            'relation'=>'Analysis relation except of Foreign key',
        ]);
    }

    /**
     * @inheritdoc
     */
    public function hints()
    {
        return array_merge(parent::hints(), [
            'only' => 'Only table to generate',
            'except' => 'Except table to generate',
            'dataNamespace' => 'data of the model namespace' ,
            'timeAdd'=>'insert event insert timestamp',
            'timeUpdate'=>'update event update timestamp',
            'statusCode'=>'status code',
            'withOneUser'=>'With One User,Only id,name,head, if table has column of \'user_id\', you can use model as $model->with(\'user\') as one to one.',
            'labelExplain'=>'Table commit be used to explain label',
            'labelTran'=>'Tanslation label to translation file',
            'targetLanguage'=>'Translation label to target language by used table comments',
            'statusCodeMessage'=>'Status Code can be tran into target language with column of table comment like 
                <code>状态.tran:0=,1=冻结.code:0=Delete,1=Freeze.</code> 
                 It will tran as <code>\'Delete\'=>\'删除\',\'Freeze\'=>\'冻结\'</code> ',
            'relation'=>'Generater all relation by column name and table,not of the Foreign key',
        ]);
    }

    /**
     * @inheritdoc
     */
    public function stickyAttributes()
    {
        return array_merge(parent::stickyAttributes(), [
            'only', 'except', 'statusCode','withOneUser','labelExplain','labelTran','targetLanguage','statusCodeMessage','relation']);
    }

    public function validateStatusCodeMessageCategory(){
        if ($this->enableI18N && empty($this->statusCodeMessage)) {
            $this->addError('statusCodeMessage', "Status Code Message Category cannot be blank.");
        }
    }


    protected $onlytable;
    protected $excepttable;

    /**
     * set range
     * @param $tablename
     * @return bool
     */
    protected function tableRange($tablename){
        if($this->only){
            if(empty($this->onlytable)){
                $this->onlytable=array_filter(explode(',',$this->only));
            }
            if(!in_array($tablename,$this->excepttable)){
                return false;
            }
        }
        if($this->except){
            if(empty($this->except)){
                $this->excepttable=array_filter(explode(',',$this->except));
            }
            if(in_array($tablename,$this->excepttable)){
                return false;
            }
        }
        return true;
    }

    public function generateDataModelClassName($tableName){
        $className='';
        if($this->dataNamespace){
            $className=$this->generateClassName($tableName);
        }
        return $className;
    }

    public $relationTable=[];
    public $modelNames=[];
    public $modelTree=[];


    public function generateRelationByColumns(){
        $db = $this->getDbConnection();
        foreach ($this->getTableNames() as $tableName) {
            $tableSchames=$db->getTableSchema($tableName);
            $this->modelNames[]=$this->generateClassName($tableName);
            foreach ($tableSchames->columns as $column) {
                if($column->name=='parent_id'){
                    $modelTree[]=$tableName;
                }else{
                    if($column->name!='id' && ($point=strpos($column->name,'_id'))){
                        $tableLink=trim(substr($column->name,0,$point),'_');
                        $this->relationTable[$tableName][$column->name]=Inflector::id2camel($tableLink,'_');
                    }
                }
            }
        }
    }

    /**
     * @inheritdoc
     */
    public function generate()
    {
        $files = [];
        $relations = $this->generateRelations();
        $db = $this->getDbConnection();

        $this->generateRelationByColumns();
        foreach ($this->getTableNames() as $tableName) {
            if($this->tableRange($tableName) == false){
                continue;
            }

            // model
            $modelClassName = $this->generateClassName($tableName);
            $queryClassName = ($this->generateQuery) ? $this->generateQueryClassName($modelClassName) : false;

            $tableSchema = $db->getTableSchema($tableName);
            $params = [
                'tableName' => $tableName ,
                'className' => $modelClassName ,
                'queryClassName' => $queryClassName ,
                'tableSchema' => $tableSchema ,
                'properties' => $this->generateProperties($tableSchema) ,
                'labels' => $this->generateLabels($tableSchema) ,
                'rules' => $this->generateRules($tableSchema) ,
                'relations' => isset($relations[$tableName]) ? $relations[$tableName] : [] ,
            ];
            $files[] = new CodeFile(
                Yii::getAlias('@' . str_replace('\\' , '/' , $this->ns)) . '/' . $modelClassName . '.php' ,
                $this->render('model.php' , $params)
            );

            // query :
            if ($queryClassName) {
                $params['className'] = $queryClassName;
                $params['modelClassName'] = $modelClassName;
                $files[] = new CodeFile(
                    Yii::getAlias('@' . str_replace('\\' , '/' , $this->queryNs)) . '/' . $queryClassName . '.php' ,
                    $this->render('query.php' , $params)
                );
            }

            //data
            if ($this->dataNamespace) {
                $params['dataNamespace'] = $this->dataNamespace;
                $files[] = new CodeFile(
                    Yii::getAlias('@' . str_replace('\\' , '/' , ltrim($this->dataNamespace,'\\'))).'\\'.$modelClassName.'.php' ,
                    $this->render('data.php' , $params)
                );
            }
            //translation
            $this->generateTrans($modelClassName,$params['properties']);
        }
        if($this->labelTran){
            $files[] = $this->generateLabelTrans();
        }
        //translation status code
        if($this->statusCode){
            $files[] = $this->generateStatusTran();
        }

        return $files;
    }


    protected $statusTrans=[];
    protected $commonStatus=[];  //common label
    protected $classStatus=[];   //class label


    protected $tranAttrs=[];    //all label
    protected $commonAttrs=[];  //common label
    protected $classAttrs=[];   //class label

    public function generateTrans($className,$properties){
        foreach ($properties as $property){
            if (!strcasecmp($property['name'], 'id')) {
                $label="ID";
                $this->tranAttrs[$label]=$label;
                $this->commonAttrs[$label]=$label;
            } else {
                $label = Inflector::camel2words($property['name']);
                if (!empty($label) && substr_compare($label, ' id', -3, 3, true) === 0) {
                    $label = substr($label, 0, -3) . ' ID';
                }
                if(isset($this->tranAttrs[$label])){
                    $this->commonAttrs[$label]=empty($property['label'])&&!empty($this->tranAttrs[$label])?$this->tranAttrs[$label]:$property['label'];
                }else{
                    $this->classAttrs[$label]=$className;
                }
                $this->tranAttrs[$label]=empty($property['label'])&&!empty($this->tranAttrs[$label])?$this->tranAttrs[$label]:$property['label'];
            }

            if(!empty($property['code'])){
                foreach ($property['code'] as $k => $v){
                    if(!empty($property['tran'][$k])){
                        if(isset($this->statusTrans[$v])){
                            $this->commonStatus[$v]=empty($property['tran'][$k])&&!empty($this->statusTrans[$label])?$this->statusTrans[$label]:$property['tran'][$k];
                        }else{
                            $this->classStatus[$v]=$className;
                        }
                        $this->statusTrans[$v]=empty($property['tran'][$k])&&!empty($this->statusTrans[$label])?$this->statusTrans[$label]:$property['tran'][$k];
                    }
                }
            }
        }
    }

    public function generateLabelTrans(){
        $messageCategory=\Yii::$app->i18n->translations[$this->messageCategory];
        $basepath=isset($messageCategory['basePath'])?$messageCategory['basePath']:'@app/messages';
        $file = Yii::getAlias($basepath).'/'.$this->targetLanguage.'/'.$this->messageCategory.'.php';

        $classAttrs=array_diff(array_keys($this->classAttrs),array_keys($this->commonAttrs));
        $classCommon = array_keys($this->commonAttrs);

        $file_content='';
        if(file_exists($file)){

            $filestart='<\?php[\s\S.]+return\s+\[';
            $fileend = '\];';

            $content=file_get_contents($file);
            if(preg_match('/('.$filestart.')([.\s\S]*)('.$fileend.')/',$content,$match)){
                $file_content=$match[2];
            }

            $old=require($file);
            $update=array_intersect(array_keys($old),array_keys($this->tranAttrs));

            $classCommon = array_diff($classCommon,$update);
            $classAttrs = array_diff($classAttrs,$update);
            //update
            foreach ($update as $v){
                preg_replace('/(\''.$v.'\'=>\')((?![\'])[\w\W])*(\',.*)/','${1}'.$this->tranAttrs[$v].'${3}',$file_content);
            }
        }

        $class=[];
        foreach ($classAttrs as $v){
            $class[$this->classAttrs[$v]][]=$v;
        }

        //common label update
        $commonStr="\n";
        foreach ($classCommon as $v){
            $commonStr.="\t'{$v}'=>'{$this->tranAttrs[$v]}',\n";
        }
        $str['NameCommon']=$commonStr;

        //add
        $str=[];
        foreach ($class as $k=>$v){
            $str[$k]="\n";
            foreach ($v as $vv){
                $str[$k].="\t'{$vv}'=>'{$this->tranAttrs[$vv]}',\n";
            }
        }

        //other label
        foreach ($str as $k=>$v){
            $start='\/\*start\*'.$k.'\*\/';
            $end = '\/\*end\*'.$k.'\*\/';

            if(preg_match('/('.$start.')([.\s\S]*)('.$end.')/',$file_content)){
                if($v!="\n"){
                    $file_content=preg_replace('/('.$start.')([.\s\S]*)('.$end.')/','${1}${2}'.$v."\t".'${3}',$file_content);
                }
            }else{
                $file_content.="\n\t/*start*{$k}*/{$v}\t/*end*{$k}*/\n";
            }
        }
        $file_content=preg_replace('/^[\s\n]+/',"",$file_content);
        $file_content=preg_replace('/[\s\n]+$/',"",$file_content);
        return new CodeFile(
            $file,
            $this->render('translation.php',['tran'=>$file_content])
        );
    }



    public function generateStatusTran(){

        $messageCategory=\Yii::$app->i18n->translations[$this->messageCategory];
        $basepath=isset($messageCategory['basePath'])?$messageCategory['basePath']:'@app/messages';
        $file = Yii::getAlias($basepath).'/'.$this->targetLanguage.'/'.($this->statusCodeMessage).'.php';

        $file_content='';
        $classAttrs=array_diff(array_keys($this->classStatus),array_keys($this->commonStatus));
        $classCommon = array_keys($this->commonStatus);

        if(file_exists($file)){

            $filestart='<\?php[\s\S.]+return\s+\[';
            $fileend = '\];';

            $content=file_get_contents($file);
            if(preg_match('/('.$filestart.')([.\s\S]*)('.$fileend.')/',$content,$match)){
                $file_content=$match[2];
            }

            $old=require($file);
            $update=array_intersect(array_keys($old),array_keys($this->statusTrans));

            $classCommon = array_diff($classCommon,$update);
            $classAttrs = array_diff($classAttrs,$update);
            //update
            foreach ($update as $v){
                preg_replace('/(\''.$v.'\'=>\')((?![\'])[\w\W])*(\',.*)/','${1}'.$this->statusTrans[$v].'${3}',$file_content);
            }
        }

        $class=[];
        foreach ($classAttrs as $v){
            $class[$this->classStatus[$v]][]=$v;
        }

        //add
        $str=[];
        $commonStr="\n";
        foreach ($classCommon as $v){
            $commonStr.="\t'{$v}'=>'{$this->statusTrans[$v]}',\n";
        }
        $str['NameCommon']=$commonStr;

        foreach ($class as $k=>$v){
            $str[$k]="\n";
            foreach ($v as $vv){
                $str[$k].="\t'{$vv}'=>'{$this->statusTrans[$vv]}',\n";
            }
        }

        foreach ($str as $k=>$v){
            $start='\/\*start\*'.$k.'\*\/';
            $end = '\/\*end\*'.$k.'\*\/';

            if(preg_match('/('.$start.')([.\s\S]*)('.$end.')/',$file_content)){
                if($v!="\n"){
                    $file_content=preg_replace('/('.$start.')([.\s\S]*)('.$end.')/','${1}${2}'.$v."\t".'${3}',$file_content);
                }
            }else{
                $file_content.="\n\t/*start*{$k}*/{$v}\t/*end*{$k}*/\n";
            }
        }
        $file_content=preg_replace('/^[\s\n]+/',"",$file_content);
        $file_content=preg_replace('/[\s\n]+$/',"",$file_content);
        return new CodeFile(
            $file,
            $this->render('statuscode.php',['tran'=>$file_content])
        );

    }


    protected $timeAdds=[];
    public function generateTimeAdd($colums){
        if($this->timeAdd && empty($this->timeAdds)){
            $this->timeAdds=explode(',',$this->timeAdd);

        }
        return array_intersect($this->timeAdds,$colums);
    }

    protected $timeUpdates=[];
    public function generateTimeUpdate($colums){
        if($this->timeAdd && empty($this->timeUpdates)){
            $this->timeUpdates=explode(',',$this->timeUpdate);
        }
        return array_intersect($this->timeUpdates,$colums);
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
//                    throw new Exception(VarDumper::dumpAsString($contents.mb_strpos('=',$contents)));
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

    /**
     * Generates the properties for the specified table.
     * @param \yii\db\TableSchema $table the table schema
     * @return array the generated properties (property => type)
     * @since 2.0.6
     */
    protected function generateProperties($table)
    {
        $properties = [];
        foreach ($table->columns as $column) {
            $columnPhpType = $column->phpType;
            if ($columnPhpType === 'integer') {
                $type = 'int';
            } elseif ($columnPhpType === 'boolean') {
                $type = 'bool';
            } else {
                $type = $columnPhpType;
            }

            list($code,$tran,$commit,$label)=$this->generateCommentInfo($column->comment);
            $properties[$column->name] = [
                'type' => $type,
                'name' => $column->name,
                'databaseType' => $column->type,
                'comment' => $column->comment,
                'label'=>$label,
                'code'=>$code,
                'tran'=>$tran,
                'commit'=>$commit,
            ];
        }

        return $properties;
    }
}