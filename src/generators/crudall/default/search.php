<?php
/**
 * This is the template for generating CRUD search class of the specified model.
 */

use yii\helpers\StringHelper;


/* @var $this yii\web\View */
/* @var $generator lbmzorx\giitool\generators\crudall\Generator */


$searchModelClass = StringHelper::basename($generator->searchModelClass);

$rules = $generator->generateSearchRules($model);
$labels = $generator->generateSearchLabels($model);
$searchAttributes = $generator->getSearchAttributes($model);
$searchConditions = $generator->generateSearchConditions($model);

echo "<?php\n";
?>

namespace <?= ltrim($generator->searchNamespace, '\\')?>;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use lbmzorx\components\event\SearchEvent;
use <?=trim($generator->modelNamespace,'\\').'\\'.$model?> as DataModel;

/**
 * <?= $model ?> represents the model behind the search form of `<?= $model ?>`.
 */
class <?= $model ?> extends DataModel

{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            <?= implode(",\n            ", $rules) ?>,
        ];
    }

<?php if($generator->timedate && ($time=$generator->generateTimeSearch($model))):?>
    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'searchTime'=>[
                'class'=>\lbmzorx\components\behavior\TimeSearch::className(),
                'timeAttributes' =>['<?=implode('\',\'',$time)?>'],
             ],
        ];
    }

<?php endif?>
    /**
     * @inheritdoc
     */
    public function scenarios()
    {
        // bypass scenarios() implementation in the parent class
        return Model::scenarios();
    }

    /**
     * Creates data provider instance with search query applied
     *
     * @param array $params
     * @return ActiveDataProvider
     */
    public function search($params)
    {
        $query = DataModel::find();

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
<?php $order=$generator->generateSearchDefaultOrder($model);if(!empty($order)):?>
            'sort' => [
                'defaultOrder' => [
<?php foreach ($order as $name=>$value):?>
                    '<?=$name?>' => <?=$value?>,
<?php endforeach;?>
                ]
            ]
<?php endif;?>
        ]);

        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        // grid filtering conditions
        <?= implode("\n        ", $searchConditions) ?>
        $this->trigger(SearchEvent::BEFORE_SEARCH, new SearchEvent(['query'=>$query]));
        return $dataProvider;
    }
}
