<?php
/**
 * @link https://github.com/devzyj/yii2-cache-behavior
 * @copyright Copyright (c) 2018 Zhang Yan Jiong
 * @license http://opensource.org/licenses/BSD-3-Clause
 */
namespace devzyj\behaviors\tests\unit;

use Yii;
use yii\db\Migration;
use devzyj\behaviors\tests\models\TestActive;

/**
 * ActiveCacheBehaviorTest class.
 * 
 * @author ZhangYanJiong <zhangyanjiong@163.com>
 * @since 1.0
 */
class ActiveCacheBehaviorTest extends TestCase
{
    /**
     * test ModelCacheBehavior methods
     */
    public function testModelCacheBehaviorMethods()
    {
        $key = __METHOD__;
        TestActive::instance()->deleteModelCache($key);
        
        // start test
        // exists
        $this->assertFalse(TestActive::instance()->existsModelCache($key));
        
        // add
        $this->assertTrue(TestActive::instance()->addModelCache($key, 'add cache'));
        $this->assertTrue(TestActive::instance()->existsModelCache($key));
        $this->assertEquals('add cache', TestActive::instance()->getModelCache($key));
        $this->assertFalse(TestActive::instance()->addModelCache($key, 'add cache again'));
        
        // set
        $this->assertTrue(TestActive::instance()->setModelCache($key, 'set cache'));
        $this->assertEquals('set cache', TestActive::instance()->getModelCache($key));
        
        // delete
        $this->assertTrue(TestActive::instance()->deleteModelCache($key));
        $this->assertFalse(TestActive::instance()->existsModelCache($key));
        $this->assertFalse(TestActive::instance()->deleteModelCache($key));
        
        // get or set
        $this->assertEquals('get or set cache', TestActive::instance()->getOrSetModelCache($key, function () {
            return 'get or set cache';
        }));
        
        // get or set again
        $this->assertEquals('get or set cache', TestActive::instance()->getOrSetModelCache($key, function () {
            return 'get or set cache again';
        }));
    }
    
    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        parent::setUp();
        $this->createTestTable();
    }

    /**
     * {@inheritdoc}
     */
    protected function tearDown()
    {
        $this->dropTestTable();
        parent::tearDown();
    }
    
    /**
     * create test table
     */
    protected function createTestTable()
    {
        $db = TestActive::getDb();
        $tableName = TestActive::tableName();
        $migration = new Migration();
        
        // create table
        $db->createCommand()->createTable($tableName, [
            'id' => $migration->integer()->notNull(),
            'name' => $migration->string()->notNull(),
        ])->execute();
        $db->createCommand()->addPrimaryKey('pk_id', $tableName, ['id'])->execute();
        
        Yii::info("Create table `{$tableName}`", __METHOD__);
    }
    
    /**
     * drop test table
     */
    protected function dropTestTable()
    {
        $db = TestActive::getDb();
        $tableName = TestActive::tableName();
        $db->createCommand()->dropTable($tableName)->execute();

        Yii::info("Drop table `{$tableName}`", __METHOD__);
    }
}