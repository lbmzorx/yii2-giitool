<?php
/**
 * Created  Generator.php.
 * Date: 2018/5/1 16:13
 * Emain: lbmzorx@163.com
 * Github: https://github.com/lbmzorx
 */
namespace lbmzorx\giitool\generators\crudall;

use Yii;
use lbmzorx\giitool\generators\BaseGenerator;
use yii\db\Exception;
use yii\db\Schema;
use yii\gii\CodeFile;
use yii\helpers\FileHelper;
use yii\helpers\Inflector;
use yii\helpers\StringHelper;
use yii\helpers\VarDumper;
use yii\db\BaseActiveRecord;
use yii\web\Controller;
use yii\db\ActiveRecord;

class Generator extends BaseGenerator
{
    public $modelNamespace='';
    public $onlyModel='';
    public $exceptModel='';

    public $namespace='';
    public $searchNamespace='';

    public $statusCode = true;
    public $timedate = true;
    public $sort = true;

    public $isLogin=true;
    public $commonControllerClass='BaseCommon';


    public $baseControllerClass = 'yii\web\Controller';
    public $indexWidgetType = 'grid';
    public $searchModelClass = '';
    /**
     * @var bool whether to wrap the `GridView` or `ListView` widget with the `yii\widgets\Pjax` widget
     * @since 2.0.5
     */
    public $enablePjax = false;



    /**
     * @inheritdoc
     */
    public function getName()
    {
        return 'Crud All Generator';
    }

    /**
     * @inheritdoc
     */
    public function getDescription()
    {
        return 'This generator Crud  world create controller base data model';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['searchNamespace', 'baseControllerClass','namespace','modelNamespace','commonControllerClass','baseControllerClass'], 'filter', 'filter' => 'trim'],
            [['modelNamespace','namespace','searchNamespace', 'commonControllerClass', 'baseControllerClass', 'indexWidgetType'], 'required'],
            [['searchNamespace'], 'compare', 'compareAttribute' => 'modelNamespace', 'operator' => '!==', 'message' => 'Search Model Class must not be equal to Model Class.'],
            [['commonControllerClass','namespace' , 'baseControllerClass', 'searchNamespace'], 'match', 'pattern' => '/^[\w\\\\]*$/', 'message' => 'Only word characters and backslashes are allowed.'],
            [['baseControllerClass'], 'validateClass', 'params' => ['extends' => Controller::className()]],
            [['indexWidgetType'], 'in', 'range' => ['grid', 'list']],
            [['enableI18N', 'enablePjax'], 'boolean'],
            [['messageCategory'], 'validateMessageCategory', 'skipOnEmpty' => false],
            ['viewPath', 'safe'],
            [['modelNamespace','onlyModel','exceptModel','namespace','searchNamespace','commonControllerClass'],'string'],
            [['statusCode','timedate','sort','isLogin'],'boolean'],
            ['viewPath', 'safe'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return array_merge(parent::attributeLabels(), [
            'viewPath' => 'View Path',
            'baseControllerClass' => 'Base Controller Class',
            'indexWidgetType' => 'Widget Used in Index Page',
            'enablePjax' => 'Enable Pjax',
            'changeStatus'=>'Change Status',
            'timedate'=>'Time Date',
        ]);
    }

    /**
     * @inheritdoc
     */
    public function hints()
    {
        return array_merge(parent::hints(), [
            'viewPath' => 'Specify the directory for storing the view scripts for the controller. You may use path alias here, e.g.,
                <code>/var/www/basic/controllers/views/post</code>, <code>@app/views/post</code>. If not set, it will default
                to <code>@app/views/ControllerID</code>',
            'baseControllerClass' => 'This is the class that the new CRUD controller class will extend from.
                You should provide a fully qualified class name, e.g., <code>yii\web\Controller</code>.',
            'indexWidgetType' => 'This is the widget type to be used in the index page to display list of the models.
                You may choose either <code>GridView</code> or <code>ListView</code>',
            'enablePjax' => 'This indicates whether the generator should wrap the <code>GridView</code> or <code>ListView</code>
                widget on the index page with <code>yii\widgets\Pjax</code> widget. Set this to <code>true</code> if you want to get
                sorting, filtering and pagination without page refreshing.',
            'exceptModel'=>'`except`: array, list of patterns excluding from the results matching file or directory paths.
                Patterns ending with slash (\'/\') apply to directory paths only, and patterns not ending with \'/\'
                apply to file paths only. For example, \'/a/b\' matches all file paths ending with \'/a/b\';
                and `.svn/` matches directory paths ending with `.svn`.
                If the pattern does not contain a slash (`/`), it is treated as a shell glob pattern
                and checked for a match against the pathname relative to `$dir`.
                Otherwise, the pattern is treated as a shell glob suitable for consumption by `fnmatch(3)`
                `with the `FNM_PATHNAME` flag: wildcards in the pattern will not match a `/` in the pathname.
                For example, `views/*.php` matches `views/index.php` but not `views/controller/index.php`.
                A leading slash matches the beginning of the pathname. For example, `/*.php` matches `index.php` but not `views/start/index.php`.
                An optional prefix `!` which negates the pattern; any matching file excluded by a previous pattern will become included again.
                If a negated pattern matches, this will override lower precedence patterns sources. Put a backslash (`\`) in front of the first `!`
                for patterns that begin with a literal `!`, for example, `\!important!.txt`.
                Note, the \'/\' characters in a pattern matches both \'/\' and \'\\\' in the paths .',
            'onlyModel'=>'only: array , list of patterns that the file paths should match if they are to be returned . Directory paths
                are not checked against them . Same pattern matching rules as in the `except` option are used .
                If a file path matches a pattern in both `only` and `except`, it will NOT be returned .
                `caseSensitive`: boolean, whether patterns specified at `only` or `except` should be case sensitive . Defaults to `true` .
                `recursive`: boolean, whether the files under the subdirectories should also be looked for. Defaults to `true` .',
            'changeStatus' => 'column you want to change status',
            'timedate' =>'time with timestamp',
            'isLogin' =>'If need to limit access before login',
            'commonControllerClass'=>'All action defind in this method , if don\'t need please base on other controller',
        ]);
    }

    /**
     * @inheritdoc
     */
    public function requiredTemplates()
    {
        return ['controller.php'];
    }

    /**
     * @inheritdoc
     */
    public function stickyAttributes()
    {
        return array_merge(parent::stickyAttributes(), [
            'baseControllerClass', 'indexWidgetType','enablePjax',
            'statusCode','timedate','sort','isLogin','commonControllerClass']);
    }

    /**
     * Checks if model class is valid
     */
    public function validateModelClass($modelClass)
    {
        /* @var $class ActiveRecord */
        $class=trim($this->modelNamespace,'\\').'/'.$modelClass;
        $pk = $modelClass::primaryKey();
        if (empty($pk)) {
            $this->addError('modelClass', "The table associated with $class must have primary key(s).");
        }
    }

    /**
     * @inheritdoc
     */
    public function generate()
    {
        $this->checkExcept('');
        $this->checkOnly('');

        $files=[];

        $baseCtrlNsClass = str_replace('Controller','',StringHelper::basename($this->commonControllerClass));
        $baseCtrlNs=StringHelper::dirname(ltrim($this->commonControllerClass, '\\'));
        $files[] = new CodeFile(
            Yii::getAlias('@'.str_replace('\\', '/', $baseCtrlNs.'/'.$baseCtrlNsClass).'.php'),
            $this->render('commonbase.php',['generator'=>$this])
        );

        $modelPath=Yii::getAlias('@' . str_replace('\\', '/', ltrim($this->modelNamespace, '\\')));
        $modelFiles=FileHelper::findFiles($modelPath,['only'=>$this->onlys,'except'=>$this->excepts]);
        $models=[];
        foreach ($modelFiles as $v){
            $model=trim(str_replace('.php','',trim(str_replace($modelPath,'',$v),'\\')));
            $controller=$model.'Controller';

//            try{
                $searchModel='';
                if (!empty($this->searchNamespace)) {
                    $searchModel = Yii::getAlias('@' . str_replace('\\', '/', ltrim($this->searchNamespace, '\\') .$model. '.php'));
                    $files[] = new CodeFile($searchModel, $this->render('search.php',['generator'=>$this,'model'=>$model]));
                    $searchModel = $model;
                }

                $files[] = new CodeFile(
                    Yii::getAlias('@'.str_replace('\\', '/', $this->namespace).'/'.$controller.'.php'),
                    $this->render('controller.php',['generator'=>$this,'model'=>$model,'searchModel'=>$searchModel,'controllerClass'=>$controller])
                );
                $models[]=$model;

                $viewPath = $this->getViewPath($controller);
                $templatePath = $this->getTemplatePath() . '/views';
                foreach (scandir($templatePath) as $file) {
                    if (empty($this->searchModelClass) && $file === '_search.php') {
                        continue;
                    }
                    if (is_file($templatePath . '/' . $file) && pathinfo($file, PATHINFO_EXTENSION) === 'php') {
                        $files[] = new CodeFile("$viewPath/$file", $this->render("views/$file",[
                            'generator'=>$this,'model'=>$model,
                            'controller'=>$controller
                        ]));
                    }
                }

//            }catch (\yii\base\Exception $e){
//                continue;
//            }
        }



        return $files;

        $files[] = new CodeFile($controllerFile, $this->render('controller.php'));



        $files[] = Yii::getAlias('@' . str_replace('\\', '/', ltrim($this->commonControllerClass, '\\')) . '.php');

        return $files;
    }

    /**
     * @param $controller
     * @return string the controller ID (without the module ID prefix)
     */
    public function getControllerID($controller)
    {
        return str_replace('-controller','',Inflector::camel2id($controller));
    }


    /**
     * @param $controller
     * @return string the controller view path
     */
    public function getViewPath($controller)
    {

        $viewpath=StringHelper::dirname(ltrim($this->namespace, '\\'));
        return Yii::getAlias(str_replace('\\', '/',$viewpath).'/views/'.$this->getControllerID($controller));
    }

    /**
     * @param $model
     * @return array model column names
     */
    public function getColumnNames($model)
    {
        /* @var $class ActiveRecord */
        $class = trim($this->modelNamespace,'\\').'\\'.$model;
        if (is_subclass_of($class, 'yii\db\ActiveRecord')) {
            return $class::getTableSchema()->getColumnNames();
        }

        /* @var $model \yii\base\Model */
        $model = new $class();

        return $model->attributes();
    }

    /**
     * @return string
     */
    public function getNameAttribute($model)
    {
        foreach ($this->getColumnNames($model) as $name) {
            if (!strcasecmp($name, 'name') || !strcasecmp($name, 'title')) {
                return $name;
            }
        }
        /* @var $class \yii\db\ActiveRecord */
        $class = trim($this->modelNamespace,'\\').'\\'.$model;
        $pk = $class::primaryKey();

        return $pk[0];
    }

    /**
     * Returns table schema for current model class or false if it is not an active record
     * @return bool|\yii\db\TableSchema
     */
    public function getTableSchema($model)
    {
        /* @var $class ActiveRecord */
        $class = trim($this->modelNamespace,'\\').'\\'.$model;
        if (is_subclass_of($class, 'yii\db\ActiveRecord')) {
            return $class::getTableSchema();
        } else {
            return false;
        }
    }

    /**
     * Generates code for active field
     * @param $model
     * @param string $attribute
     * @return string
     */
    public function generateActiveField($model,$attribute)
    {
        $tableSchema = $this->getTableSchema($model);
        if ($tableSchema === false || !isset($tableSchema->columns[$attribute])) {
            if (preg_match('/^(password|pass|passwd|passcode)$/i', $attribute)) {
                return "\$form->field(\$model, '$attribute')->passwordInput()";
            }

            return "\$form->field(\$model, '$attribute')";
        }
        $column = $tableSchema->columns[$attribute];
        if ($column->phpType === 'boolean') {
            return "\$form->field(\$model, '$attribute')->checkbox()";
        }

        if($this->changeStatus){
            $status=explode(',',$this->changeStatus);
            foreach ($status as $s){
                if($s==$column->name){
                    $className=StringHelper::basename($model);
                    return "\$form->field(\$model, '$attribute')->dropDownList(StatusCode::tranStatusCode({$className}::\${$column->name}_code,'{$this->messageCategory}'),['prompt'=>\\Yii::t('{$this->messageCategory}','Please Select')])";
                }
            }
        }

        if ($column->type === 'text') {
            return "\$form->field(\$model, '$attribute')->textarea(['rows' => 6])";
        }

        if (preg_match('/^(password|pass|passwd|passcode)$/i', $column->name)) {
            $input = 'passwordInput';
        } else {
            $input = 'textInput';
        }

        if (is_array($column->enumValues) && count($column->enumValues) > 0) {
            $dropDownOptions = [];
            foreach ($column->enumValues as $enumValue) {
                $dropDownOptions[$enumValue] = Inflector::humanize($enumValue);
            }
            return "\$form->field(\$model, '$attribute')->dropDownList("
                . preg_replace("/\n\s*/", ' ', VarDumper::export($dropDownOptions)).", ['prompt' => ''])";
        }

        if ($column->phpType !== 'string' || $column->size === null) {
            return "\$form->field(\$model, '$attribute')->$input()";
        }

        return "\$form->field(\$model, '$attribute')->$input(['maxlength' => true])";
    }

    /**
     * Generates code for active search field
     * @param $model
     * @param string $attribute
     * @return string
     */
    public function generateActiveSearchField($model,$attribute)
    {
        $tableSchema = $this->getTableSchema($model);
        if ($tableSchema === false) {
            return "\$form->field(\$model, '$attribute')";
        }

        $column = $tableSchema->columns[$attribute];
        if ($column->phpType === 'boolean') {
            return "\$form->field(\$model, '$attribute')->checkbox()";
        }

        return "\$form->field(\$model, '$attribute')";
    }

    /**
     * Generates column format
     * @param \yii\db\ColumnSchema $column
     * @return string
     */
    public function generateColumnFormat($column)
    {
        if ($column->phpType === 'boolean') {
            return 'boolean';
        }

        if ($column->type === 'text') {
            return 'ntext';
        }

        if (stripos($column->name, 'time') !== false && $column->phpType === 'integer') {
            return 'datetime';
        }

        if (stripos($column->name, 'email') !== false) {
            return 'email';
        }

        if (preg_match('/(\b|[_-])url(\b|[_-])/i', $column->name)) {
            return 'url';
        }

        return 'text';
    }


    /**
     * Generates validation rules for the search model.
     * @param $model
     * @return array the generated validation rules
     */
    public function generateSearchRules($model)
    {
        if (($table = $this->getTableSchema($model)) === false) {
            return ["[['" . implode("', '", $this->getColumnNames($model)) . "'], 'safe']"];
        }
        $types = [];
        foreach ($table->columns as $column) {
            switch ($column->type) {
                case Schema::TYPE_SMALLINT:
                case Schema::TYPE_INTEGER:
                case Schema::TYPE_BIGINT:
                    if($this->timedate && preg_match('/(\w+_time)|(\w+d_at)/',$column->name)){
                        $types['string'][] = $column->name;
                    }else{
                        $types['integer'][] = $column->name;
                    }
                    break;
                case Schema::TYPE_BOOLEAN:
                    $types['boolean'][] = $column->name;
                    break;
                case Schema::TYPE_FLOAT:
                case Schema::TYPE_DOUBLE:
                case Schema::TYPE_DECIMAL:
                case Schema::TYPE_MONEY:
                    $types['number'][] = $column->name;
                    break;
                case Schema::TYPE_DATE:
                case Schema::TYPE_TIME:
                case Schema::TYPE_DATETIME:
                case Schema::TYPE_TIMESTAMP:
                default:
                    $types['safe'][] = $column->name;
                    break;
            }
        }

        $rules = [];
        foreach ($types as $type => $columns) {
            $rules[] = "[['" . implode("', '", $columns) . "'], '$type']";
        }
        $class= trim($this->modelNamespace,'\\').'\\'.$model;
        if(method_exists($class,'statusCodes')){
            foreach ($class::statusCodes() as $status){
                $status=$status.'_code';
                if(isset($class::${$status})){
                    $rules[] = "[['" .$status. "'], 'in', 'range'=>array_keys( DataModel::\${$status}_code ) ]";
                }
            }
        }

        return $rules;
    }

    public function generateTimeSearch($model){
        $timeDate=[];
        if (($table = $this->getTableSchema($model)) !== false) {
            foreach ($table->columns as $column) {
                if($this->timedate && preg_match('/(\w+_time)|(\w+d_at)/',$column->name)){
                    $timeDate[] = $column->name;
                }
            }
        }
        return $timeDate;
    }

    /**
     * @param $model
     * @return array searchable attributes
     */
    public function getSearchAttributes($model)
    {
        return $this->getColumnNames($model);
    }

    /**
     * Generates the attribute labels for the search model.
     * @param $model
     * @return array the generated attribute labels (name => label)
     */
    public function generateSearchLabels($modelName)
    {
        /* @var $model \yii\base\Model */
        $class= trim($this->modelNamespace,'\\').'\\'.$modelName;
        $model = new $class();
        $attributeLabels = $model->attributeLabels();
        $labels = [];
        foreach ($this->getColumnNames($modelName) as $name) {
            if (isset($attributeLabels[$name])) {
                $labels[$name] = $attributeLabels[$name];
            } else {
                if (!strcasecmp($name, 'id')) {
                    $labels[$name] = 'ID';
                } else {
                    $label = Inflector::camel2words($name);
                    if (!empty($label) && substr_compare($label, ' id', -3, 3, true) === 0) {
                        $label = substr($label, 0, -3) . ' ID';
                    }
                    $labels[$name] = $label;
                }
            }
        }

        return $labels;
    }

    /**
     * Generates search conditions
     * @param $modelName
     * @return array
     */
    public function generateSearchConditions($modelName)
    {
        $columns = [];
        if (($table = $this->getTableSchema($modelName)) === false) {
            $class= trim($this->modelNamespace,'\\').'\\'.$modelName;
            /* @var $model \yii\base\Model */
            $model = new $class();
            foreach ($model->attributes() as $attribute) {
                $columns[$attribute] = 'unknown';
            }
        } else {
            foreach ($table->columns as $column) {
                $columns[$column->name] = $column->type;
            }
        }

        $likeConditions = [];
        $hashConditions = [];
        $timeDate=$this->generateTimeSearch($modelName);

        foreach ($columns as $column => $type) {
            switch ($type) {
                case Schema::TYPE_SMALLINT:
                case Schema::TYPE_INTEGER:
                case Schema::TYPE_BIGINT:
                case Schema::TYPE_BOOLEAN:
                case Schema::TYPE_FLOAT:
                case Schema::TYPE_DOUBLE:
                case Schema::TYPE_DECIMAL:
                case Schema::TYPE_MONEY:
                case Schema::TYPE_DATE:
                case Schema::TYPE_TIME:
                case Schema::TYPE_DATETIME:
                case Schema::TYPE_TIMESTAMP:
                    if(in_array($column,$timeDate)){
                        continue;
                    }
                    $hashConditions[] = "'{$column}' => \$this->{$column},";
                    break;
                default:
                    $likeKeyword = $this->getClassDbDriverName($modelName) === 'pgsql' ? 'ilike' : 'like';
                    $likeConditions[] = "->andFilterWhere(['{$likeKeyword}', '{$column}', \$this->{$column}])";
                    break;
            }
        }

        $conditions = [];
        if (!empty($hashConditions)) {
            $conditions[] = "\$query->andFilterWhere([\n"
                . str_repeat(' ', 12) . implode("\n" . str_repeat(' ', 12), $hashConditions)
                . "\n" . str_repeat(' ', 8) . "]);\n";
        }
        if (!empty($likeConditions)) {
            $conditions[] = "\$query" . implode("\n" . str_repeat(' ', 12), $likeConditions) . ";\n";
        }

        return $conditions;
    }

    /**
     * Generates URL parameters
     * @param $model
     * @return string
     */
    public function generateUrlParams($model)
    {
        /* @var $class ActiveRecord */
        $class= trim($this->modelNamespace,'\\').'\\'.$model;
        $pks = $class::primaryKey();
        if (count($pks) === 1) {
            if (is_subclass_of($class, 'yii\mongodb\ActiveRecord')) {
                return "'id' => (string)\$model->{$pks[0]}";
            }

            return "'id' => \$model->{$pks[0]}";
        }

        $params = [];
        foreach ($pks as $pk) {
            if (is_subclass_of($class, 'yii\mongodb\ActiveRecord')) {
                $params[] = "'$pk' => (string)\$model->$pk";
            } else {
                $params[] = "'$pk' => \$model->$pk";
            }
        }

        return implode(', ', $params);
    }

    /**
     * Generates action parameters
     * @param $model
     * @return string
     */
    public function generateActionParams($model)
    {
        /* @var $class ActiveRecord */
        $class = trim($this->modelNamespace,'\\').'\\'.$model;
        $pks = $class::primaryKey();
        if (count($pks) === 1) {
            return '$id';
        }

        return '$' . implode(', $', $pks);
    }

    /**
     * Generates parameter tags for phpdoc
     * @param $model
     * @return array parameter tags for phpdoc
     */
    public function generateActionParamComments($model)
    {
        /* @var $class ActiveRecord */
        $class =  trim($this->modelNamespace,'\\').'\\'.$model;
        $pks = $class::primaryKey();
        if (($table = $this->getTableSchema($model)) === false) {
            $params = [];
            foreach ($pks as $pk) {
                $params[] = '@param ' . (strtolower(substr($pk, -2)) === 'id' ? 'integer' : 'string') . ' $' . $pk;
            }

            return $params;
        }
        if (count($pks) === 1) {
            return ['@param ' . $table->columns[$pks[0]]->phpType . ' $id'];
        }

        $params = [];
        foreach ($pks as $pk) {
            $params[] = '@param ' . $table->columns[$pk]->phpType . ' $' . $pk;
        }

        return $params;
    }

    /**
     * @return string|null driver name of modelClass db connection.
     * In case db is not instance of \yii\db\Connection null will be returned.
     * @since 2.0.6
     */
    protected function getClassDbDriverName($model)
    {
        /* @var $class ActiveRecord */
        $class =  trim($this->modelNamespace,'\\').'\\'.$model;
        $db = $class::getDb();
        return $db instanceof \yii\db\Connection ? $db->driverName : null;
    }

    protected $onlys=[];
    /**
     * except table name in except set.
     * @param $modelName
     * @return bool
     */
    protected function checkOnly($modelName){
        $only=[];
        if($this->onlyModel){
            $onlyModel=array_filter(explode(',',$this->onlyModel));
            $this->onlyModels=$onlyModel;
        }
        if( !empty($onlyModel)){
            foreach ($onlyModel as $v){
                if($modelName==$v){
                    return true;
                }elseif(strpos($v, '*') !== false){
                    $pattern = '/^' . str_replace('*', '\w+', $v) . '$/';
                    if(preg_match($pattern,$modelName)){
                        return true;
                    }
                }
            }
            return false;
        }
        return true;
    }

    protected $excepts=[];
    /**
     * model table name in except set.
     * @param $modelName
     * @return bool
     */
    protected function checkExcept($modelName){
        $except=[];
        if($this->exceptModel){
            $exceptModel=array_filter(explode(',',$this->exceptModel));
            $this->exceptModels=$exceptModel;
        }
        if( !empty($exceptModel)){
            foreach ($exceptModel as $v){
                if($modelName==$v){
                    return true;
                }elseif(strpos($v, '*') !== false){
                    $pattern = '/^' . str_replace('*', '\w+', $v) . '$/';
                    if(preg_match($pattern,$modelName)){
                        return true;
                    }
                }
            }
        }
        return false;
    }


    public function generateSearchDefaultOrder($model){
        $default=[];
        foreach ($this->getColumnNames($model) as $name){
            if($name =='id'){
                $default['id']='SORT_DESC';
            }
            if($name =='sort'){
                $default['sort']='SORT_ASC';
            }
        }
        return $default;
    }




    public function generateStatusCodeColum($column,$model){
        $class =  trim($this->modelNamespace,'\\').'\\'.$model;
        if($this->statusCode && method_exists($class,'statusCodes')){
            $changeStatus=$class::statusCodes();
            if(in_array($column,$changeStatus)){
                $string="[\n".
                    "               'class'=>\common\components\grid\StatusCodeColumn::className(),\n".
                    "               'attribute'=>'{$column}',\n".
                    "               'filter'=>\common\components\behaviors\StatusCode::tranStatusCode({$model}::\${$column}_code,'{$this->messageCategory}'),\n".
                    "               'value'=> function (\$model) {\n".
                    "                   return Html::button(\$model->getStatusCode('{$column}','{$column}_code'),\n".
                    "                       ['data-id'=>\$model->id,'class'=>'{$column}-change btn btn-xs btn-'.\$model->getStatusCss('{$column}','{$column}_css',\$model->{$column})]);\n".
                    "               },\n".
                    "               'format'=>'raw',\n".
                    "            ]";
                return $string;
            }
        }
        return false;
    }

    public function generateStatusCodeRow($column){
        if($this->changeStatus){
            $changeStatus=explode(',',$this->changeStatus);
            if(in_array($column,$changeStatus)){
                $string=
                    "            [\n".
                    "               'attribute'=>'{$column}',\n".
                    "               'value'=>\$model->getStatusCode('{$column}','{$column}_code'),\n".
                    "            ],\n";
                return $string;
            }
        }
        return false;
    }


    public function generateTimeDate($column){
        if($this->timedate){
            $timedate=explode(',',$this->timedate);
            if(in_array($column,$timedate)){
                $string="[\n".
                    "               'class'=>\common\components\grid\DateTimeColumn::className(),\n".
                    "               'attribute'=>'{$column}',\n".
                    "            ]";
                return $string;
            }
        }
        return false;
    }

    public function generateGetStatusCode($model){
        $class =  trim($this->modelNamespace,'\\').'\\'.$model;
        if(method_exists($class,'statusCodes')){
            return $class::statusCodes();
        }else{
            return [];
        }
    }

    public function generateStatusCodeDom($column,$model){
        $string=
            <<<DOM
<div id="{$column}-change-dom" style="display: none;">
    <div style="padding: 10px;">
        <?=Html::beginForm(['change-status'],'post')?>
        <input type="hidden" name="key" value="{$column}">
        <input type="hidden" name="id" value="">
        <?php foreach ( {$model}::\${$column}_code as \$k=>\$v):?>           
            <label class="checkbox-inline" style="margin: 5px 10px;">
                <?php
                    \$css='warning';
                    if( isset({$model}::\${$column}_css) && isset({$model}::\${$column}_css[\$k])){
                        \$css = {$model}::\${$column}_css [\$k];
                    }else{
                        \$css=isset(\common\components\behaviors\StatusCode::\$cssCode[\$k])?\common\components\behaviors\StatusCode::\$cssCode[\$k]:\$css;
                    }
                ?>               
                <?=Html::input('radio','value',\$k)?>
                <?=Html::tag('span',\Yii::t('{$this->messageCategory}',\$v),['class'=>'btn btn-'.\$css])?>
            </label>          
        <?php endforeach;?>
        <?=Html::endForm()?>
    </div>
</div>
DOM;
        return $string;
    }

}