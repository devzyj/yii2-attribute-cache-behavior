<?php
/**
 * @link https://github.com/devzyj/yii2-cache-behavior
 * @copyright Copyright (c) 2018 Zhang Yan Jiong
 * @license http://opensource.org/licenses/BSD-3-Clause
 */
namespace devzyj\behaviors\tests\unit;

use Yii;
use yii\db\Migration;
use devzyj\behaviors\tests\models\TestCompositeKey;

/**
 * CompositeKeyTest class.
 * 
 * @author ZhangYanJiong <zhangyanjiong@163.com>
 * @since 1.0
 */
class CompositeKeyTest extends TestCase
{
    /**
     * Test by attribute methods
     */
    public function testByAttributeMethods()
    {
        $attribute = [1, 2];
        TestCompositeKey::instance()->deleteModelCacheByAttribute($attribute);

        // start test
        // add
        $this->assertTrue(TestCompositeKey::instance()->addModelCacheByAttribute($attribute, 'add cache'));
        $this->assertTrue(TestCompositeKey::instance()->existsModelCacheByAttribute($attribute));
        $this->assertEquals('add cache', TestCompositeKey::instance()->getModelCacheByAttribute($attribute));
        $this->assertFalse(TestCompositeKey::instance()->addModelCacheByAttribute($attribute, 'add cache again'));
        
        // set
        $this->assertTrue(TestCompositeKey::instance()->setModelCacheByAttribute($attribute, 'set cache'));
        $this->assertTrue(TestCompositeKey::instance()->existsModelCacheByAttribute($attribute));
        $this->assertEquals('set cache', TestCompositeKey::instance()->getModelCacheByAttribute($attribute));
        
        // delete
        $this->assertTrue(TestCompositeKey::instance()->deleteModelCacheByAttribute($attribute));
        $this->assertFalse(TestCompositeKey::instance()->existsModelCacheByAttribute($attribute));
        $this->assertFalse(TestCompositeKey::instance()->getModelCacheByAttribute($attribute));
        $this->assertFalse(TestCompositeKey::instance()->deleteModelCacheByAttribute($attribute));
        
        // get or set
        $this->assertEquals('get or set cache', TestCompositeKey::instance()->getOrSetModelCacheByAttribute($attribute, function () {
            return 'get or set cache';
        }));
        
        // get or set again
        $this->assertEquals('get or set cache', TestCompositeKey::instance()->getOrSetModelCacheByAttribute($attribute, function () {
            return 'get or set cache again';
        }));
    }
    
    /**
     * Test Active Methods
     */
    public function testActiveMethods()
    {
        $model = TestCompositeKey::findOne(['id1' => 1, 'id2' => 2]);
        $model->deleteActiveCache();

        // start test
        // add
        $this->assertTrue($model->addActiveCache());
        $this->assertTrue($model->existsActiveCache());
        $this->assertEquals(['id1' => 1, 'id2' => 2, 'name' => 'TestName'], $model->getActiveCache());
        $this->assertFalse($model->addActiveCache());
        
        // delete
        $this->assertTrue($model->deleteActiveCache());
        $this->assertFalse($model->existsActiveCache());
        $this->assertFalse($model->getActiveCache());
        $this->assertFalse($model->deleteActiveCache());
        
        // set
        $this->assertTrue($model->setActiveCache());
        $this->assertTrue($model->existsActiveCache());
        $this->assertEquals(['id1' => 1, 'id2' => 2, 'name' => 'TestName'], $model->getActiveCache());
        
        // new active record
        $model = new TestCompositeKey();
        $this->assertFalse($model->getActiveCache());
        $this->assertFalse($model->existsActiveCache());
        $this->assertFalse($model->setActiveCache());
        $this->assertFalse($model->addActiveCache());
        $this->assertFalse($model->deleteActiveCache());
    }
    
    /**
     * Test ActiveRecord Events
     */
    public function testActiveRecordEvents()
    {
        $model = TestCompositeKey::findOne(['id1' => 1, 'id2' => 2]);

        // start test
        // no changed
        $model->setActiveCache();
        $model->save();
        $this->assertTrue(TestCompositeKey::instance()->existsModelCacheByAttribute([1, 2]));
        
        // update value
        $model->setActiveCache();
        $model->name = 'changed';
        $model->save();
        $this->assertFalse(TestCompositeKey::instance()->existsModelCacheByAttribute([1, 2]));

        // update key1
        $model->setActiveCache();
        TestCompositeKey::instance()->setModelCacheByAttribute([3, 2], 'dirty');
        $model->id1 = 3;
        $model->save();
        $this->assertFalse(TestCompositeKey::instance()->existsModelCacheByAttribute([1, 2]));
        $this->assertFalse(TestCompositeKey::instance()->existsModelCacheByAttribute([3, 2]));

        // update key2
        $model->setActiveCache();
        TestCompositeKey::instance()->setModelCacheByAttribute([3, 4], 'dirty');
        $model->id2 = 4;
        $model->save();
        $this->assertFalse(TestCompositeKey::instance()->existsModelCacheByAttribute([3, 2]));
        $this->assertFalse(TestCompositeKey::instance()->existsModelCacheByAttribute([3, 4]));

        // update key1 & key2
        $model->setActiveCache();
        TestCompositeKey::instance()->setModelCacheByAttribute([5, 6], 'dirty');
        $model->id1 = 5;
        $model->id2 = 6;
        $model->save();
        $this->assertFalse(TestCompositeKey::instance()->existsModelCacheByAttribute([3, 4]));
        $this->assertFalse(TestCompositeKey::instance()->existsModelCacheByAttribute([5, 6]));
        
        // delete
        $model->setActiveCache();
        $model->delete();
        $this->assertFalse(TestCompositeKey::instance()->existsModelCacheByAttribute([5, 6]));
        
        // insert
        TestCompositeKey::instance()->setModelCacheByAttribute([1, 2], 'dirty');
        $model = new TestCompositeKey();
        $model->id1 = 1;
        $model->id2 = 2;
        $model->name = 'insert';
        $model->save();
        $this->assertFalse(TestCompositeKey::instance()->existsModelCacheByAttribute([1, 2]));
    }
    
    /**
     * Test trait moethods
     */
    public function testTraitMethods()
    {
        $model = TestCompositeKey::findOne(['id1' => 1, 'id2' => 2]);
        TestCompositeKey::instance()->deleteModelCacheByAttribute([1, 2]);
        
        // start test
        $this->assertEquals($model, TestCompositeKey::findOrSetOneByAttribute([1, 2]));
        $this->assertEquals(['id1' => 1, 'id2' => 2, 'name' => 'TestName'], TestCompositeKey::instance()->getModelCacheByAttribute([1, 2]));
        
        // again
        $this->assertEquals($model, TestCompositeKey::findOrSetOneByAttribute([1, 2]));
        $this->assertEquals(['id1' => 1, 'id2' => 2, 'name' => 'TestName'], TestCompositeKey::instance()->getModelCacheByAttribute([1, 2]));
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
        $db = TestCompositeKey::getDb();
        $tableName = TestCompositeKey::tableName();
        $migration = new Migration();
        
        // create table
        $db->createCommand()->createTable($tableName, [
            'id1' => $migration->integer()->notNull(),
            'id2' => $migration->integer()->notNull(),
            'name' => $migration->string()->notNull(),
        ])->execute();
        $db->createCommand()->addPrimaryKey('pk_id1_id2', $tableName, ['id1', 'id2'])->execute();
        
        // insert data
        $db->createCommand()->batchInsert($tableName, ['id1', 'id2', 'name'], [
            [1, 2, 'TestName'],
        ])->execute();
        
        Yii::info("Create table `{$tableName}`", __METHOD__);
    }
    
    /**
     * drop test table
     */
    protected function dropTestTable()
    {
        $db = TestCompositeKey::getDb();
        $tableName = TestCompositeKey::tableName();
        $db->createCommand()->dropTable($tableName)->execute();

        Yii::info("Drop table `{$tableName}`", __METHOD__);
    }
}