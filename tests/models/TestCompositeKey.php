<?php
/**
 * @link https://github.com/devzyj/yii2-cache-behavior
 * @copyright Copyright (c) 2018 Zhang Yan Jiong
 * @license http://opensource.org/licenses/BSD-3-Clause
 */
namespace devzyj\behaviors\tests\models;

/**
 * TestCompositeKey class.
 * 
 * @property string $id1 ID1
 * @property string $id2 ID2
 * @property string $name Name
 * 
 * @author ZhangYanJiong <zhangyanjiong@163.com>
 * @since 1.0
 */
class TestCompositeKey extends \yii\db\ActiveRecord
{
    use \devzyj\behaviors\ActiveCacheBehaviorTrait;
    
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'devzyj_test_composite_key';
    }
    
    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            [
                'class' => 'devzyj\behaviors\ActiveCacheBehavior',
                'defaultDuration' => 600,
                'baseModelCacheKey' => ['devzyj-behaviors-tests-TestCompositeKey'],
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id1', 'id2', 'name'], 'safe'],
        ];
    }
}