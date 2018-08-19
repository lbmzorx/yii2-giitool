<?php
/**
 * Created  Generator.php.
 * Date: 2018/4/22 10:19
 * Emain: lbmzorx@163.com
 * Github: https://github.com/lbmzorx
 */
namespace lbmzorx\giitool\generators\markdowndb;

use Yii;
use yii\db\TableSchema;
use yii\gii\CodeFile;
use yii\helpers\Inflector;

class Generator extends \yii\gii\generators\model\Generator
{

    public $only;
    public $except;
    public $tableName='*';

    public $db;

    public $templateMdClass='lbmzorx\giitool\generators\markdowndb\TableMdDoc';
    public $mdDoc='';

    /**
     * @inheritdoc
     */
    public function getName()
    {
        return 'Generator markdown document for database';
    }

    /**
     * @inheritdoc
     */
    public function getDescription()
    {
        return 'Generator markdown document for database, provide an userful tool to read database !';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['db', 'mdDoc'], 'filter', 'filter' => 'trim'],
            [['db', 'mdDoc','templateMdClass'], 'required'],
            [['templateMdClass',], 'match', 'pattern' => '/^[\w\\\\]+$/', 'message' => 'Only word characters and backslashes are allowed.'],
            [['db',], 'match', 'pattern' => '/^\w+$/', 'message' => 'Only word characters are allowed.'],
            [['mdDoc',], 'match', 'pattern' => '/^\w+(\/\w+)+$/', 'message' => 'file path'],
            [['only','except'], 'match', 'pattern' => '/^([\w ]+\.)?([\w\* ]+)(,([\w ]+\.)?([\w\* ]+))*/', 'message' => 'Only word characters, and optionally spaces, an asterisk and/or a dot are allowed.'],
            [['db'], 'validateDb'],
            [['only','except','mdDoc','templateMdClass'],'string'],
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
            'mdDoc'=>'Markdown File Name',
            'templateMdClass'=>'Template class to generate markdwon string',
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
            'mdDoc'=>'Generater the document for model which you can read model provide more symple way to use it.'.
                'Example <code>frontend/views/site/aa</code> ,it will generate file in '.
                '<code>@frontend/views/site/aa.md</code>',
            'templateMdClass'=>'Template class to generate markdwon string.',
        ]);
    }

    /**
     * @inheritdoc
     */
    public function stickyAttributes()
    {
        return array_merge(parent::stickyAttributes(), [
            'only', 'except','db','mdDoc','templateMdClass']);
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
                    $this->modelTree[]=$tableName;
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
        $db = $this->getDbConnection();

        $this->generateRelationByColumns();
        foreach ($this->getTableNames() as $tableName) {
            if($this->tableRange($tableName) == false){
                continue;
            }

            $tableSchema = $db->getTableSchema($tableName);

            $this->_tableShemas[]=$tableSchema;
            $this->_mdLinks[]=$tableSchema->name;
        }
        //md generate
        if($this->mdDoc){
            $files[] = $this->generateMds();
        }

        return $files;
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


    protected $_tableShemas=[];
    public function generateMds(){
        $file=\yii::getAlias('@'.$this->mdDoc).'.md';
        if(file_exists($file)){
            $content=file_get_contents($file);
        }else{
            $content='';
        }

        $content=$this->generateTableMdLink($content);

        foreach ($this->_tableShemas as $tableShema){
            $content=$this->generateTableMd($tableShema,$content);
        }
        return new CodeFile(
            $file,
            $this->render('mddoc.php',['content'=>$content])
        );
    }

    protected $_tableMdDoc;

    /**
     * @param $tableSchema TableSchema
     */
    protected function generateTableMd($tableSchema,$content=''){
        $tableMdDoc=$this->getTableMdDoc($tableSchema);
        $mdString=$tableMdDoc->generateMdString();

        $tablstart="<!--start_table_{$tableSchema->name}-->";
        $tablend="<!--end_table_{$tableSchema->name}-->";

        if(preg_match('/('.$tablstart.')([.\s\S]*)('.$tablend.')/',$content)){
            $content=preg_replace('/('.$tablstart.')([.\s\S]*)('.$tablend.')/','${1}'.$mdString.'${3}',$content);
        }else{
            $content.="\n{$tablstart}{$mdString}\n{$tablend}";
        }
        return $content;
    }

    /**
     * @param $tableSchema TableSchema
     * @return TableMdDoc|object
     */
    public function getTableMdDoc($tableSchema){
        if( $this->_tableMdDoc == null){
            $this->_tableMdDoc = Yii::createObject([
                'class'=>$this->templateMdClass,
                'tableSchema'=>$tableSchema,
                'tableName'=>$tableSchema->name,
                'db'=>$this->getDbConnection(),
                'commitCodes'=>[],
            ]);
        }elseif($this->_tableMdDoc instanceof TableMdDoc){
            $this->_tableMdDoc->setTableSchema($tableSchema);
            $this->_tableMdDoc->tableName=$tableSchema->name;
            $this->_tableMdDoc->db=$this->getDbConnection();
            $this->_tableMdDoc->commitCodes=[];

        }
        return $this->_tableMdDoc;
    }

    protected $_mdLinks=[];
    public function generateTableMdLink($content=''){
        $tablstart="<!--start_md_link_table-->";
        $tablend="<!--end_md_link_table-->";
        $mdString='';
        $file_content="## 表列表\n\n";
        $matchlink=false;
        if(preg_match('/('.$tablstart.')([.\s\S]*)('.$tablend.')/',$content,$match)){
            $file_content=$match[2];
            $matchlink=true;
        }
        foreach ($this->_mdLinks as $k=>$link){
            $linkstart="<!--start_md_link_table_{$link}-->";
            $linkend="<!--end_md_link_table_{$link}-->";
            $mdString=($k+1).'. [表 '.$link.'](#'.$link.")\n";
            if(preg_match('/('.$linkstart.')([.\s\S]*)('.$linkend.')/',$file_content)){
                $file_content=preg_replace('/('.$linkstart.')([.\s\S]*)('.$linkend.')/','${1}'.$mdString.'${3}',$file_content);
            }else{
                $file_content.="{$linkstart}\n{$mdString}{$linkend}\n";
            }
        }
        if($matchlink){
            $content=preg_replace('/('.$tablstart.')([.\s\S]*)('.$tablend.')/','${1}'.$file_content.'${3}',$content);
        }else{
            $content=$file_content;
        }

        return $content;
    }

}