<?php
/**
 * Created  Generator.php.
 * Date: 2018/5/1 16:20
 * Emain: lbmzorx@163.com
 * Github: https://github.com/lbmzorx
 */
namespace lbmzorx\giitool\generators\migrationall;

use Yii;
use lbmzorx\giitool\generators\BaseGenerator;
use yii\base\Exception;
use yii\db\Connection;
use yii\db\Schema;
use yii\gii\CodeFile;
use yii\db\Expression;
use yii\helpers\VarDumper;
class Generator extends BaseGenerator
{
    public $only;
    public $except;

    public $tableName='*';
    public $db = 'db';

    public $dataLimit = 20;
    public $dataOrderBy='desc';

    public $migrationPath = '@app/migrations';
    public $migrationName = '';
    public $migrationTime;

    public $generateRelations = true;
    public $useTablePrefix = false;
    public $createTableIfNotExists = 0;
    public $disableFkc = false;
    public $isSafeUpDown = false;

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
        $this->migrationTime = date('ymd_His');
    }

    /**
     * @inheritdoc
     */
    public function getName()
    {
        return 'Migration All Generator';
    }

    /**
     * @inheritdoc
     */
    public function getDescription()
    {
        return 'This generator generates a migration for the specified database table.';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return array_merge(parent::rules(), [
            [['db', 'migrationPath', 'tableName', 'migrationName', 'migrationTime'], 'filter', 'filter' => 'trim'],
            [['db', 'migrationPath', 'tableName', 'migrationName', 'migrationTime'], 'required'],
            [['db', 'migrationName'], 'match', 'pattern' => '/^\w+$/', 'message' => 'Only word characters are allowed.'],
            [['tableName'], 'match', 'pattern' => '/^(\w+\.)?([\w\*]+)$/', 'message' => 'Only word characters, and optionally an asterisk and/or a dot are allowed.'],
            [['migrationTime'], 'match', 'pattern' => '/^(\d{6}_\d{6})/', 'message' => 'Only format xxxxxx_xxxxxx are allowed.'],
            [['db'], 'validateDb'],
            [['tableName'], 'validateTableName'],
            [['generateRelations'], 'boolean'],
            [['useTablePrefix'], 'boolean'],
            [['createTableIfNotExists'], 'boolean'],
            [['disableFkc'], 'boolean'],
            [['isSafeUpDown'], 'boolean'],
            [['only','except','dataOrderBy'],'string',],
            [['dataOrderBy'],'in','range'=>['desc','asc']],
            [['dataLimit'],'integer'],
        ]);
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return array_merge(parent::attributeLabels(), [
            'migrationPath' => 'Migration Path',
            'db' => 'Database Connection ID',
            'tableName' => 'Table Name',
            'migrationName' => 'Migration Name',
            'migrationTime' => 'Migration Time',
            'generateRelations' => 'Generate Relations',
            'createTableIfNotExists' => 'If table exist',
            'disableFkc' => 'Disable foreign key checks',
            'isSafeUpDown' => 'Generate with safeUp() and safeDown()',
            'only' =>'Only tables will be migrate',
            'except'=>'Except tables will not be migrate',
            'dataLimit'=>'insert data limit ,default 20 rows',
            'dataOrderBy' =>'If data include column of ID ,the data order by',
        ]);
    }

    /**
     * @inheritdoc
     */
    public function hints()
    {
        return array_merge(parent::hints(), [
            'migrationPath' => 'Path to store generated file, e.g., <code>@app/migrations</code>',
            'db' => 'This is the ID of the DB application component.',
            'tableName' => 'This is the name of the DB table that the new ActiveRecord class is associated with, e.g. <code>post</code>.
                The table name may consist of the DB schema part if needed, e.g. <code>public.post</code>.
                The table name may end with asterisk to match multiple table names, e.g. <code>tbl_*</code>
                will match tables who name starts with <code>tbl_</code>.',
            'migrationName' => 'The name of the new migration. This should only contain letters, digits and/or underscores.',
            'migrationTime' => 'Time of the new migration. This should only has format <code>yymmdd_hhiiss</code>.',
            'generateRelations' => 'This indicates whether the generator should generate relations based on
                foreign key constraints it detects in the database. Note that if your database contains too many tables,
                you may want to uncheck this option to accelerate the code generation process.',
            'useTablePrefix' => 'This indicates whether the table name returned by the generated migration
                should consider the <code>tablePrefix</code> setting of the DB connection. For example, if the
                table name is <code>tbl_post</code> and <code>tablePrefix=tbl_</code>, the migration
                will use the table name as <code>{{%post}}</code>.',
            'createTableIfNotExists' => 'Skip table if it exists in database.',
            'disableFkc' => 'Disable foreign key checks when migrating down (drop table).',
            'isSafeUpDown' => 'Option to generate whether use <code>up() down()</code> or <code>safeUp() safeDown()</code>',
            'only' =>'Only tables will be migrate ,this will be name of table,User \',\' delimer ,tabel name is full name, can use preg_match * is replace by \w+ ,example <code>test_user,test_admin_*</code>',
            'except'=>'Except tables will not be migrate, this will be name of table,User \',\' delimer, can use preg_match * is replace by \w+,example <code>test_table1,test_table2_*</code>',
            'dataLimit'=>'insert data limit ,default 20 rows',
            'dataOrderBy' =>'If data include column of ID ,the data order by',
        ]);
    }

    /**
     * @inheritdoc
     */
    public function autoCompleteData()
    {
        $db = $this->getDbConnection();
        if ($db !== null) {
            return [
                'tableName' => function () use ($db) {
                    return $db->getSchema()->getTableNames();
                },
            ];
        } else {
            return [];
        }
    }

    /**
     * @inheritdoc
     */
    public function requiredTemplates()
    {
        return ['migration.php'];
    }

    /**
     * @inheritdoc
     */
    public function stickyAttributes()
    {
        return array_merge(parent::stickyAttributes(), ['migrationPath', 'db', 'generateRelations', 'useTablePrefix']);
    }

    /**
     * @inheritdoc
     */
    public function generate()
    {
        $relations = $this->generateRelations();
        $db = $this->getDbConnection();
        $tables = [];
        foreach ($this->getTableNames() as $tableName) {
            $tableSchema = $db->getTableSchema($tableName);
            $columns = $this->generateColumns($tableSchema);
            $datas = $this->getInsertData($db,$tableSchema);
            if (isset($columns[0])) {
                $primary = $columns[0];
                unset($columns[0]);
            } else {
                $primary = null;
            }
            $tables[$tableSchema->name] = [
                'name' => $this->generateTableName($tableSchema->name),
                'columns' => $columns,
                'primary' => $primary,
                'datas' => $datas,
                'relations' => isset($relations[$tableSchema->name]) ? $relations[$tableSchema->name] : [],
            ];
        }

        $migrationName = 'm' . $this->migrationTime . '_' . $this->migrationName;
        $file = rtrim(Yii::getAlias($this->migrationPath), '/') . "/{$migrationName}.php";
        $files = new CodeFile($file, $this->render('migration.php', [
            'tables' => $this->reorderTables($tables, $relations),
            'migrationName' => $migrationName,
            'tableSchema'=>$tableSchema,
        ]));
        return [$files];
    }

    /**
     * @param \yii\db\Connection $db
     * @param \yii\db\TableSchema $tableSchema
     * @return  mixed
     */
    protected function getInsertData($db,$tableSchema){
        $sql="select * from {$tableSchema->fullName} ";

        if($this->dataOrderBy == 'desc'){
            if(array_key_exists( 'id',$tableSchema->columns)){
                $sql.=" ORDER BY id DESC";
            }
        }
        if( $limit=intval($this->dataLimit) ){
            $sql.=" LIMIT {$limit}";
        }
        return $db->createCommand($sql)->queryAll();
    }

    /**
     * Reorder tables acourding with dependencies.
     * @param array $tables
     * @param array $relations
     * @return array
     */
    protected function reorderTables($tables, $relations)
    {
        $depencies = $orders = $result = [];
        foreach ($relations as $table => $relation) {
            if (isset($relation[$table])) {
                unset($relation[$table]);
            }
            $depencies[$table] = array_keys($relation);
        }
        $tableNames = array_keys($tables);
        sort($tableNames);
        $this->reorderRecrusive($tableNames, $depencies, $orders);
        foreach (array_keys($orders) as $value) {
            if (isset($tables[$value])) {
                $result[] = $tables[$value];
            }
        }
        return $result;
    }

    /**
     *
     * @param array $tableNames
     * @param array $depencies
     * @param array $orders
     */
    protected function reorderRecrusive($tableNames, &$depencies, &$orders)
    {
        foreach ($tableNames as $table) {
            if (!isset($orders[$table])) {
                if (isset($depencies[$table])) {
                    $this->reorderRecrusive($depencies[$table], $depencies, $orders);
                }
                $orders[$table] = true;
            }
        }
    }

    /**
     * @param $db
     * @param $table
     * @param $colum
     * @return bool
     */
    protected function getSchemaUnique($db,$table,$colum){
        // Unique indexes rules
        try {
            $uniqueIndexes = array_merge($db->getSchema()->findUniqueIndexes($table), [$table->primaryKey]);
            $uniqueIndexes = array_unique($uniqueIndexes, SORT_REGULAR);
            if(array_key_exists($colum,$uniqueIndexes) && count($uniqueIndexes[$colum])==1){
                return true;
            }
        } catch (NotSupportedException $e) {
            // doesn't support unique indexes information...do nothing
            return false;
        }
    }


    protected $constans;


    /**
     * @param \yii\db\ColumnSchema $column
     * @param \yii\db\TableSchema $table
     * @return string
     */
    public function getSchemaType($column,$table)
    {
        if ($this->constans === null) {
            $this->constans = [];
            $ref = new \ReflectionClass(Schema::className());
            foreach ($ref->getConstants() as $constName => $constValue) {
                if (strpos($constName, 'TYPE_') === 0) {
                    if($constValue=='tinyint'){
                        $this->constans['tinyint']='$this->tinyInteger';
                    }else{
                        $this->constans[$constValue] = '$this->' . $constValue;
                    }
                }
            }
            $this->constans['smallint'] = '$this->smallInteger';
            $this->constans['bigint'] = '$this->bigInteger';
        }
        if ($column->type !== Schema::TYPE_BOOLEAN && $column->size !== null) {
            $size = [$column->size];
            if ($column->scale !== null) {
                $size[] = $column->scale;
            }
        } else {
            $size = [];
        }
        $result = '';
        if (isset($this->constans[$column->type])) {
            $result = $this->constans[$column->type] . '(' . implode(',', $size) . ')';
            if (!$column->allowNull) {
                $result .= '->notNull()';
            }
            if ($column->unsigned) {
                $result .= '->unsigned()';
            }
            if ($column->defaultValue !== null) {
                $default = is_string($column->defaultValue) ? "'" . addslashes($column->defaultValue) . "'" : $column->defaultValue;
                $result .= "->defaultValue({$default})";
            }
            if ($this->getSchemaUnique($this->getDbConnection(),$table,$column->name)){
                $result .= "->unique()";
            }
            if($column->comment !==null){
                $result .= "->comment('{$column->comment}')";
            }
        } else {
            $result = $column->dbType;
            if (!empty($size)) {
                $result.= '(' . implode(',', $size) . ')';
            }
            if (!$column->allowNull) {
                $result .= ' NOT NULL';
            }
            if (!$column->unsigned) {
                $result .= ' unsigned';
            }
            if ($column->defaultValue !== null) {
                $default = is_string($column->defaultValue) ? "'" . addslashes($column->defaultValue) . "'" : $column->defaultValue;
                $result .= " DEFAULT {$default}";
            }
            if($column->comment !==null){
                $result .= " COMMENT '{$column->comment}'";
            }
            $result = '"' . $result . '"';
        }
        return $result;
    }

    /**
     * Generates validation rules for the specified table.
     * @param \yii\db\TableSchema $table the table schema
     * @return array the generated validation rules
     */
    protected function generateColumns($table)
    {
        $columns = [];
        $needPK = true;
        foreach ($table->columns as $column) {
            if ($column->autoIncrement) {
                $columns[$column->name] = $column->type == Schema::TYPE_BIGINT ? '$this->bigPrimaryKey()' : '$this->primaryKey()';
                $needPK = false;
                continue;
            }
            $columns[$column->name] = $this->getSchemaType($column,$table);
        }
        if ($needPK && !empty($table->primaryKey)) {
            $pks = implode(']], [[', $table->primaryKey);
            $columns[0] = "PRIMARY KEY ([[{$pks}]])";
        }
        return $columns;
    }

    /**
     * @return array the generated relation declarations
     */
    protected function generateRelations()
    {
        if (!$this->generateRelations) {
            return [];
        }

        $db = $this->getDbConnection();

        if (($pos = strpos($this->tableName, '.')) !== false) {
            $schemaName = substr($this->tableName, 0, $pos);
        } else {
            $schemaName = '';
        }

        $relations = [];
        foreach ($db->getSchema()->getTableSchemas($schemaName) as $table) {
            $tableName = $table->name;
            foreach ($table->foreignKeys as $refs) {
                $refTable = $refs[0];
                $refTableName = $this->generateTableName($refTable);
                unset($refs[0]);

                $fks = implode(']], [[', array_keys($refs));
                $pks = implode(']], [[', array_values($refs));

                $relation = "FOREIGN KEY ([[$fks]]) REFERENCES $refTableName ([[$pks]]) ON DELETE CASCADE ON UPDATE CASCADE";
                $relations[$tableName][$refTable] = $relation;
            }
        }
        return $relations;
    }

    /**
     * Validates the [[db]] attribute.
     */
    public function validateDb()
    {
        if (!Yii::$app->has($this->db)) {
            $this->addError('db', 'There is no application component named "db".');
        } elseif (!Yii::$app->get($this->db) instanceof Connection) {
            $this->addError('db', 'The "db" application component must be a DB connection instance.');
        }
    }

    /**
     * Validates the [[tableName]] attribute.
     */
    public function validateTableName()
    {
        if (strpos($this->tableName, '*') !== false && substr_compare($this->tableName, '*', -1, 1)) {
            $this->addError('tableName', 'Asterisk is only allowed as the last character.');

            return;
        }
        $tables = $this->getTableNames();
        if (empty($tables)) {
            $this->addError('tableName', "Table '{$this->tableName}' does not exist.");
        }
    }
    protected $tableNames;

    /**
     * @return array the table names that match the pattern specified by [[tableName]].
     */
    protected function getTableNames()
    {
        if ($this->tableNames !== null) {
            return $this->tableNames;
        }
        $db = $this->getDbConnection();
        if ($db === null) {
            return [];
        }
        $tableNames = [];
        if (strpos($this->tableName, '*') !== false) {
            if (($pos = strrpos($this->tableName, '.')) !== false) {
                $schema = substr($this->tableName, 0, $pos);
                $pattern = '/^' . str_replace('*', '\w+', substr($this->tableName, $pos + 1)) . '$/';
            } else {
                $schema = '';
                $pattern = '/^' . str_replace('*', '\w+', $this->tableName) . '$/';
            }

            foreach ($db->schema->getTableNames($schema) as $table) {


                if( $this->checkOnly($table)==false){
                    continue;
                }
                if($this->checkExcept($table)==true){
                    continue;
                }

                if (preg_match($pattern, $table)) {
                    $tableNames[] = $schema === '' ? $table : ($schema . '.' . $table);
                }



            }
        } elseif (($table = $db->getTableSchema($this->tableName, true)) !== null) {
            $tableNames[] = $this->tableName;
        }

        return $this->tableNames = $tableNames;
    }

    /**
     * check table name in only set.
     * @param $tableName
     * @return bool
     */
    protected function checkOnly($tableName){
        $only=[];
        if($this->only){
            $only=array_filter(explode(',',$this->only));
        }
        if( !empty($only)){
            foreach ($only as $v){
                if($tableName==$v){
                    return true;
                }elseif(strpos($v, '*') !== false){
                    $pattern = '/^' . str_replace('*', '\w+', $v) . '$/';
                    if(preg_match($pattern,$tableName)){
                        return true;
                    }
                }
            }
            return false;
        }
        return true;
    }

    /**
     * check table name in except set.
     * @param $tableName
     * @return bool
     */
    protected function checkExcept($tableName){
        $except=[];
        if($this->except){
            $except=array_filter(explode(',',$this->except));
        }
        if( !empty($except)){
            foreach ($except as $v){
                if($tableName==$v){
                    return true;
                }elseif(strpos($v, '*') !== false){
                    $pattern = '/^' . str_replace('*', '\w+', $v) . '$/';
                    if(preg_match($pattern,$tableName)){
                        return true;
                    }
                }
            }
        }
        return false;
    }

    /**
     * Generates the table name by considering table prefix.
     * If [[useTablePrefix]] is false, the table name will be returned without change.
     * @param string $tableName the table name (which may contain schema prefix)
     * @return string the generated table name
     */
    public function generateTableName($tableName)
    {
        if (!$this->useTablePrefix) {
            return $tableName;
        }

        $db = $this->getDbConnection();
        if (preg_match("/^{$db->tablePrefix}(.*?)$/", $tableName, $matches)) {
            $tableName = '{{%' . $matches[1] . '}}';
        } elseif (preg_match("/^(.*?){$db->tablePrefix}$/", $tableName, $matches)) {
            $tableName = '{{' . $matches[1] . '%}}';
        }
        return $tableName;
    }

    /**
     * @return Connection the DB connection as specified by [[db]].
     */
    protected function getDbConnection()
    {
        return Yii::$app->get($this->db, false);
    }

    /**
     * Checks if any of the specified columns is auto incremental.
     * @param \yii\db\TableSchema $table the table schema
     * @param array $columns columns to check for autoIncrement property
     * @return bool whether any of the specified columns is auto incremental.
     */
    protected function isColumnAutoIncremental($table, $columns)
    {
        foreach ($columns as $column) {
            if (isset($table->columns[$column]) && $table->columns[$column]->autoIncrement) {
                return true;
            }
        }

        return false;
    }

}