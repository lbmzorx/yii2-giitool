<?php
/**
 * Created  BashGenerator.php.
 * Date: 2018/4/22 10:26
 * Emain: lbmzorx@163.com
 * Github: https://github.com/lbmzorx
 */
namespace lbmzorx\giitool\generators;


use yii\gii\Generator;
abstract class BaseGenerator extends Generator
{

    public function renderFile($path,$template, $params = []){
        $view = new View();
        $params['generator'] = $this;
        return $view->renderFile($path . '/' . $template, $params, $this);
    }

}