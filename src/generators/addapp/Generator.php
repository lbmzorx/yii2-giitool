<?php
/**
 * Created  Generator.php.
 * Date: 2018/4/22 10:19
 * Emain: lbmzorx@163.com
 * Github: https://github.com/lbmzorx
 */
namespace lbmzorx\giitool\generators\addapp;


use lbmzorx\giitool\generators\BaseGenerator;

class Generator extends BaseGenerator
{

    public $appname;

    const TYPE_FRONTEND=0;
    const TYPE_BACKEND=1;
    const TYPE_RESTFUL_API=2;
    const TYPE_CONSOLE=3;
    public static $type_code=['frontend' => 'Frontend', 'backend' => 'Backend','restfulapi'=>'Restful Api','console'=>'Console','commonapi'=>'Common Api'];
    public $type;

    const ISINIT_YES=1;
    const ISINIT_NO=0;
    public static $isinit_code=['0' => 'Yes', '1' => 'No'];
    public $isinit;

    const ENV_YES=1;
    const ENV_NO=0;
    public static $env_code=['0' => 'Development', '1' => 'Production'];
    public $evn;

    /**
     * @inheritdoc
     */
    public function getName()
    {
        return 'Application Generator';
    }

    /**
     * @inheritdoc
     */
    public function getDescription()
    {
        return 'This generator generates an application for the specified name.';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return array_merge(parent::rules(), [
            [['appname', 'type', 'isinit', 'env'], 'required'],
            [['isinit', 'env'], 'integer', 'pattern' => '/^\w+$/', 'message' => 'Only word characters are allowed.'],
            [['appname','type',], 'match', 'pattern' => '/^[\w\\\\]+$/', 'message' => 'Only word characters and backslashes are allowed.'],
            [['type'], 'in', 'range' =>array_keys(self::$type_code)],
            [['isinit'], 'in', 'range' =>array_keys(self::$isinit_code)],
            [['env'], 'in', 'range' =>array_keys(self::$env_code)],
        ]);
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return array_merge(parent::attributeLabels(), [
            'appname' => 'Application Name',
            'type' => 'Application Type',
            'isinit' => 'If Need to Init',
            'env' => 'Environment of Application',
        ]);
    }

    /**
     * @inheritdoc
     */
    public function hints()
    {
        return array_merge(parent::hints(), [
            'appname' => 'This is the Application Name which is going to generate, <code>app1</code>',
            'type' => 'This is the Tpye of the application, you can choose one of Frontend, Backend ,Restful Api,Console to build',
            'isinit'=>'Application need init when created, the index file will create',
            'env' => 'Environment of Application, Development or Production ,default Development',
        ]);
    }

    /**
     * @inheritdoc
     */
    public function stickyAttributes()
    {
        return array_merge(parent::stickyAttributes(), [
            'appname', 'type', 'isinit','env',
        ]);
    }


    /**
     * @inheritdoc
     */
    public function generate()
    {
        $files = [];
        $files[] = $this->generateBootstrapFile();
        return $files;
    }


    public function generateEnvironmentFile(){
        $path=\yii::getAlias('@common').'../environments';
    }

    public function generateBootstrapFile(){
        $file=null;
        $path=\yii::getAlias('@common').'/config';
        if(is_dir($path)){
            if(file_exists($path.'/bootstrap.php')){
                $content=file_get_contents($path.'/bootstrap.php');
                if(!preg_match('/'.$this->appname.'/',$content)){
                    $content="\n".'Yii::setAlias(\'@'.$this->appname.'\', dirname(dirname(__DIR__)) . \'/'.$this->appname.'\');';
                    file_put_contents($path.'/bootstrap.php',$content,FILE_APPEND);
                    $file=new CodeFile(
                        Yii::getAlias('@' . str_replace('\\', '/', $this->ns)) . '/' . $modelClassName . '.php',
                        $this->renderFile($path,'bootstrap.php',$params)
                    );
                }
            }
        }
        return $file;
    }

    public function generateAppFiles(){

    }

}