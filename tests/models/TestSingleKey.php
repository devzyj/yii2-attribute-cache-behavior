<?php
/**
 * @link https://github.com/devzyj/yii2-attribute-cache-behavior
 * @copyright Copyright (c) 2018 Zhang Yan Jiong
 * @license http://opensource.org/licenses/BSD-3-Clause
 */
namespace devzyj\behaviors\tests\models;

/**
 * TestSingleKey class.
 * 
 * @property string $id ID
 * @property string $name Name
 * 
 * @author ZhangYanJiong <zhangyanjiong@163.com>
 * @since 1.0
 */
class TestSingleKey extends \yii\db\ActiveRecord
{
    use \devzyj\behaviors\ActiveCacheBehaviorTrait;
    
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'devzyj_test_single_key';
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
                'baseModelCacheKey' => ['devzyj-behaviors-tests-TestSingleKey'],
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'name'], 'safe'],
        ];
    }
}