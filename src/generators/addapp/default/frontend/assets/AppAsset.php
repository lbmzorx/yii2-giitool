<?php echo "<?php"?>
?>

namespace <?=$generator->appname?>\assets;

use yii\web\AssetBundle;

/**
 * Main <?=$generator->appname?> application asset bundle.
 */
class AppAsset extends AssetBundle
{
    public $basePath = '@webroot';
    public $baseUrl = '@web';
    public $css = [
        'css/site.css',
    ];
    public $js = [
    ];
    public $depends = [
        'yii\web\YiiAsset',
        'yii\bootstrap\BootstrapAsset',
    ];
}
