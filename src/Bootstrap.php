<?php
/**
 * Created  Bootstrap.php.
 * User: Administrator
 * Date: 2018/4/22 10:13
 * Emain: lbmzorx@163.com
 */
namespace lbmzorx\giitool;

use yii\base\BootstrapInterface;

class Bootstrap implements BootstrapInterface
{
    public function bootstrap($app){
        if ($app->hasModule('gii')) {
            if (!isset($app->getModule('gii')->generators['giitool'])) {
                $app->getModule('gii')->generators['giitool-addapp']['class']= 'lbmzorx\giitool\generators\addapp\Generator';
                $app->getModule('gii')->generators['giitool-modelsall']['class']= 'lbmzorx\giitool\generators\modelsall\Generator';
                $app->getModule('gii')->generators['giitool-migrationall']['class']= 'lbmzorx\giitool\generators\migrationall\Generator';
                $app->getModule('gii')->generators['giitool-crudall']['class']= 'lbmzorx\giitool\generators\crudall\Generator';
                $app->getModule('gii')->generators['giitool-markdowndb']['class']= 'lbmzorx\giitool\generators\markdowndb\Generator';
            }
        }
    }
}