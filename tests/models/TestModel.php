<?php
/**
 * @link https://github.com/devzyj/yii2-cache-behavior
 * @copyright Copyright (c) 2018 Zhang Yan Jiong
 * @license http://opensource.org/licenses/BSD-3-Clause
 */
namespace devzyj\behaviors\tests\models;

/**
 * TestModel class.
 * 
 * @author ZhangYanJiong <zhangyanjiong@163.com>
 * @since 1.0
 */
class TestModel extends \yii\base\Model
{
    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            [
                'class' => 'devzyj\behaviors\ModelCacheBehavior',
                'defaultDuration' => 600,
                'baseModelCacheKey' => ['devzyj-behaviors-tests-TestModel'],
            ],
        ];
    }
}