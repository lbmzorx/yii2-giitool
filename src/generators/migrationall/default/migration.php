<?php
/**
 * This is the template for generating the model class of a specified table.
 */

/* @var $this yii\web\View */
/* @var $generator lbmzorx\giitool\migrationall\Generator */
/* @var $migrationName string migration name */

//echo \yii\helpers\VarDumper::dumpAsString($unitindex);
echo "<?php\n";
?>

use yii\db\Schema;

class <?= $migrationName ?> extends \yii\db\Migration
{
<?php if($generator->isSafeUpDown): ?>
    public function safeUp()
<?php else: ?>
    public function up()
<?php endif; ?>
    {
<?php if ($generator->createTableIfNotExists): ?>
        $tables = Yii::$app->db->schema->getTableNames();
<?php endif; ?>
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_general_ci ENGINE=InnoDB';
        }
        
<?php foreach ($tables as $table): 
        $tableRaw = trim($table['name'], '{}%');
        $t = '';
        if ($generator->createTableIfNotExists == 1) :
        $t = '  ';
?>
        if (!in_array(Yii::$app->db->tablePrefix.'<?= $tableRaw ?>', $tables))  {
<?php endif; ?>
        $this->createTable('<?= $table['name'] ?>', [
<?php foreach ($table['columns'] as $column => $definition): ?>
            <?=$t?><?= "'$column' => $definition"?>,
<?php endforeach;?>
<?php if(isset($table['primary'])): ?>
            <?=$t?><?= "'{$table['primary']}'" ?>,
<?php endif; ?>
<?php foreach ($table['relations'] as $definition): ?>
            <?=$t?><?= "'$definition'" ?>,
<?php endforeach;?>
        ], $tableOptions);
<?php if(!empty($table['datas'])): ?>
<?php
    $dataColumn=array_keys($table['datas'][0]);
    $dataColumnStr=implode('\',\'',$dataColumn);
    ?>
        $this->batchInsert('<?= $table['name'] ?>', ['<?=$dataColumnStr?>'],
        [
<?php foreach ($table['datas'] as $data):?>
<?php
        foreach ($data as $k=>$v){
            $data[$k]=str_replace("\n","\\n",addslashes($v));
        }
        $dataColumnStr=implode('\',\'',$data);
?>
        <?=$t."\t"?>['<?= $dataColumnStr?>'],
<?php endforeach;?>
        ]);
<?php endif; ?>
<?php if ($generator->createTableIfNotExists == 1) :?>
        } else {
          echo "\nTable `".Yii::$app->db->tablePrefix."<?= $tableRaw ?>` already exists!\n";
        }
<?php endif; ?>
<?php endforeach;?>
        
    }

<?php if($generator->isSafeUpDown): ?>
    public function safeDown()
<?php else: ?>
    public function down()
<?php endif; ?>
    {
<?php if ($generator->disableFkc) : ?>
        $this->execute('SET foreign_key_checks = 0');
<?php endif; ?>
<?php foreach (array_reverse($tables) as $table): ?>
        $this->dropTable('<?= $table['name'] ?>');
<?php endforeach;?>
<?php if ($generator->disableFkc) : ?>
        $this->execute('SET foreign_key_checks = 1');
<?php endif; ?>
    }
}
