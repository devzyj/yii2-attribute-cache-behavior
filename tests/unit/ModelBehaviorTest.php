<?php
/**
 * @link https://github.com/devzyj/yii2-cache-behavior
 * @copyright Copyright (c) 2018 Zhang Yan Jiong
 * @license http://opensource.org/licenses/BSD-3-Clause
 */
namespace devzyj\behaviors\tests\unit;

use devzyj\behaviors\tests\models\TestModel;

/**
 * ModelCacheBehaviorTest class.
 * 
 * @author ZhangYanJiong <zhangyanjiong@163.com>
 * @since 1.0
 */
class ModelCacheBehaviorTest extends TestCase
{
    /**
     * test methods
     */
    public function testMethods()
    {
        $key = __FUNCTION__;
        TestModel::instance()->deleteModelCache($key);
        
        // start test
        // exists
        $this->assertFalse(TestModel::instance()->existsModelCache($key));
        
        // add
        $this->assertTrue(TestModel::instance()->addModelCache($key, 'add cache'));
        $this->assertTrue(TestModel::instance()->existsModelCache($key));
        $this->assertEquals('add cache', TestModel::instance()->getModelCache($key));
        $this->assertFalse(TestModel::instance()->addModelCache($key, 'add cache again'));
        
        // set
        $this->assertTrue(TestModel::instance()->setModelCache($key, 'set cache'));
        $this->assertEquals('set cache', TestModel::instance()->getModelCache($key));
        
        // delete
        $this->assertTrue(TestModel::instance()->deleteModelCache($key));
        $this->assertFalse(TestModel::instance()->existsModelCache($key));
        $this->assertFalse(TestModel::instance()->deleteModelCache($key));
        
        // get or set
        $this->assertEquals('get or set cache', TestModel::instance()->getOrSetModelCache($key, function () {
            return 'get or set cache';
        }));
        
        // get or set again
        $this->assertEquals('get or set cache', TestModel::instance()->getOrSetModelCache($key, function () {
            return 'get or set cache again';
        }));
    }
}