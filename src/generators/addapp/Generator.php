<?php
/**
 * Created  Generator.php.
 * Date: 2018/4/22 10:19
 * Emain: lbmzorx@163.com
 * Github: https://github.com/lbmzorx
 */
namespace lbmzorx\giitool\generators\addapp;


use lbmzorx\giitool\generators\BaseGenerator;
use yii\base\Exception;
use yii\gii\CodeFile;
use yii\helpers\FileHelper;
use yii\helpers\VarDumper;

class Generator extends BaseGenerator
{

    public $appname;

    const TYPE_FRONTEND='frontend';
    const TYPE_BACKEND='backend';
    const TYPE_RESTFUL_API='restfulapi';
    const TYPE_CONSOLE='console';
    const TYPE_COMMONAPI='commonapi';
    public static $type_code=['frontend' => 'Frontend', 'backend' => 'Backend','restfulapi'=>'Restful Api','console'=>'Console','commonapi'=>'Common Api'];
    public $type;

    const ISINIT_YES=1;
    const ISINIT_NO=0;
    public static $isinit_code=[ 0=>'No', 1=> 'Yes'];
    public $isinit;

    const ENV_DEV='DEV';
    const ENV_PROD='PROD';
    public static $env_code=['dev' => 'Development', 'prod' => 'Production'];
    public $env;

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
            [['isinit',], 'integer',],
            [['appname','type','env'], 'match', 'pattern' => '/^[\w\\\\]+$/', 'message' => 'Only word characters and backslashes are allowed.'],
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

        $file=$this->generateBootstrapFile();
        if( $file ){
            $files[] = $file;
        }
        $file=$this->generateEnvironmentFile();
        if( $file ){
            $files=array_merge($files,$file);
        }
        $file=$this->generateAppFiles();
        if( $file ){
            $files=array_merge($files,$file);
        }
        $file=$this->generateInit();
        if( $file ){
            $files=array_merge($files,$file);
        }

        return $files;
    }

    /**
     * Generator boostrap file this will add alias for new application
     * @return null|CodeFile
     */
    public function generateBootstrapFile(){
        $file=null;
        $path=\yii::getAlias('@common/config');
        if(!is_dir($path)){
            FileHelper::createDirectory($path);
        }
        $content='<?php';
        if(file_exists($path.'/bootstrap.php')){
            $content=file_get_contents($path.'/bootstrap.php');
        }

        if(!preg_match('/'.$this->appname.'/',$content)){
            $content.="\n".'Yii::setAlias(\'@'.$this->appname.'\', dirname(dirname(__DIR__)) . \'/'.$this->appname.'\');';
            $file=new CodeFile(
                $path.'/bootstrap.php',
                $this->render('bootstrap.php',['content'=>$content])
            );
        }else{
            $file=new CodeFile(
                $path.'/bootstrap.php',
                $this->render('bootstrap.php',['content'=>$content])
            );
        }
        return $file;
    }

    /**
     * Generator environment file which you can use commond `php init` to init project
     * @return array
     */
    public function generateEnvironmentFile(){
        $path=\yii::getAlias('@common').'/../environments';

        if(!is_dir($path)){
            FileHelper::createDirectory($path);
        }
        if(file_exists($path.'/index.php')){
            $content=require($path.'/index.php');
        }
        $setWritable=[];
        if( !empty($content['Development']['setWritable'])){
            $setWritable=$content['Development']['setWritable'];
            if(!in_array($this->appname.'/runtime',$content['Development']['setWritable'])){
                $setWritable[]=$this->appname.'/runtime';
            }
            if(!in_array($this->appname.'/web/assets',$content['Development']['setWritable'])){
                $setWritable[]=$this->appname.'/web/assets';
            }
        }
        $setCookieValidationKey=[];
        if( !empty($content['Development']['setCookieValidationKey'])){
            $setCookieValidationKey=$content['Development']['setCookieValidationKey'];
            if(!in_array($this->appname.'/config/main-local.php',$content['Development']['setCookieValidationKey'])){
                $setCookieValidationKey[]=$this->appname.'/config/main-local.php';
            }
        }

        $file=[];
        $file[]=new CodeFile(
            $path.'/index.php',
            $this->render('environments/index.php',[
                'setwrites'=>$setWritable,
                'setCookieValidationKeys'=>$setCookieValidationKey,
            ])
        );

        foreach (['dev','prod'] as $v){
            if(!is_dir($path.'/'.$v.'/'.$this->appname)){
                FileHelper::createDirectory($path.'/'.$v.'/'.$this->appname);
            }
            if(!is_dir($path.'/'.$v.'/'.$this->appname.'/config')){
                FileHelper::createDirectory($path.'/'.$v.'/'.$this->appname.'/config');
            }
            if(!is_dir($path.'/'.$v.'/'.$this->appname.'/web')){
                FileHelper::createDirectory($path.'/'.$v.'/'.$this->appname.'/web');
            }

            $file[]=new CodeFile(
                $path.'/'.$v.'/'.$this->appname.'/config/main-local.php',
                $this->render('environments/app/config/main-local.php',[])
            );
            $file[]=new CodeFile(
                $path.'/'.$v.'/'.$this->appname.'/config/params-local.php',
                $this->render('environments/app/config/params-local.php',[])
            );

            if($v=='dev'){
                $file[]=new CodeFile(
                    $path.'/'.$v.'/'.$this->appname.'/config/test-local.php',
                    $this->render('environments/app/config/test-local.php',[])
                );
                $file[]=new CodeFile(
                    $path.'/'.$v.'/'.$this->appname.'/web/index-test.php',
                    $this->render('environments/app/web/index-test.php',['generator'=>$this])
                );
            }

            $file[]=new CodeFile(
                $path.'/'.$v.'/'.$this->appname.'/web/index.php',
                $this->render('environments/app/web/index.php',['env'=>$v])
            );

            $file[]=new CodeFile(
                $path.'/'.$v.'/'.$this->appname.'/web/robots.txt',
                $this->render('environments/app/web/robots.php',['type'=>$this->type])
            );
        }
        return $file;
    }


    /**
     * generator new Application
     * @return array
     */
    public function generateAppFiles(){
        $file=[];
        $path=\yii::getAlias('@common').'/../'.$this->appname;
        if(!is_dir($path)){
            FileHelper::createDirectory($path);
        }

        $lists=FileHelper::findFiles($this->templatePath.'/'.$this->type);

        foreach ($lists as $v){
            $str=str_replace($this->templatePath.'/'.$this->type,'',$v);

            $filename=$path.$str;
            if(preg_match('/\.yml\./',$filename)){
                $filename=str_replace('.php','',$filename);
            }
            $file[]=new CodeFile(
                $filename,
                $this->render($this->type.'/'.$str,['generator'=>$this])
            );
        }
        return $file;
    }


    public function generateInit(){
        if($this->isinit==self::ISINIT_YES){
            $path=\yii::getAlias('@common').'/../'.$this->appname;

            if(!is_dir($path)){
                FileHelper::createDirectory($path);
            }

            if(!is_dir($path.'/config')){
                FileHelper::createDirectory($path.'/config');
            }
            if(!is_dir($path.'/web')){
                FileHelper::createDirectory($path.'/web');
            }
            $file=[];
            $file[]=new CodeFile(
                $path.'/config/main-local.php',
                $this->render('environments/app/config/main-local.php',['cookieValidationKey'=>$this->generatorCookieValidationKey()])
            );
            $file[]=new CodeFile(
                $path.'/config/params-local.php',
                $this->render('environments/app/config/params-local.php',[])
            );

            if($this->env=='dev'){
                $file[]=new CodeFile(
                    $path.'/config/test-local.php',
                    $this->render('environments/app/config/test-local.php',[])
                );
                $file[]=new CodeFile(
                    $path.'/web/index-test.php',
                    $this->render('environments/app/web/index-test.php',['generator'=>$this])
                );
            }

            $file[]=new CodeFile(
                $path.'/web/index.php',
                $this->render('environments/app/web/index.php',['env'=>$this->env])
            );
            $file[]=new CodeFile(
                $path.'/web/robots.txt',
                $this->render('environments/app/web/robots.php',['type'=>$this->type])
            );
            return $file;
        }else{
            return false;
        }
    }

    public function generatorCookieValidationKey(){
        $length = 32;
        $bytes = openssl_random_pseudo_bytes($length);
        $key = strtr(substr(base64_encode($bytes), 0, $length), '+/=', '_-.');
        return $key;
    }
}