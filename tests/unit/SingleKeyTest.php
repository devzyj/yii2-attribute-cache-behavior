<?php
/**
 * @link https://github.com/devzyj/yii2-cache-behavior
 * @copyright Copyright (c) 2018 Zhang Yan Jiong
 * @license http://opensource.org/licenses/BSD-3-Clause
 */
namespace devzyj\behaviors\tests\unit;

use Yii;
use yii\db\Migration;
use devzyj\behaviors\tests\models\TestSingleKey;

/**
 * SingleKeyTest class.
 * 
 * @author ZhangYanJiong <zhangyanjiong@163.com>
 * @since 1.0
 */
class SingleKeyTest extends TestCase
{
    /**
     * Test by attribute methods
     */
    public function testByAttributeMethods()
    {
        $attribute = 1;
        TestSingleKey::instance()->deleteModelCacheByAttribute($attribute);

        // start test
        // add
        $this->assertTrue(TestSingleKey::instance()->addModelCacheByAttribute($attribute, 'add cache'));
        $this->assertTrue(TestSingleKey::instance()->existsModelCacheByAttribute($attribute));
        $this->assertEquals('add cache', TestSingleKey::instance()->getModelCacheByAttribute($attribute));
        $this->assertFalse(TestSingleKey::instance()->addModelCacheByAttribute($attribute, 'add cache again'));
        
        // set
        $this->assertTrue(TestSingleKey::instance()->setModelCacheByAttribute($attribute, 'set cache'));
        $this->assertTrue(TestSingleKey::instance()->existsModelCacheByAttribute($attribute));
        $this->assertEquals('set cache', TestSingleKey::instance()->getModelCacheByAttribute($attribute));
        
        // delete
        $this->assertTrue(TestSingleKey::instance()->deleteModelCacheByAttribute($attribute));
        $this->assertFalse(TestSingleKey::instance()->existsModelCacheByAttribute($attribute));
        $this->assertFalse(TestSingleKey::instance()->getModelCacheByAttribute($attribute));
        $this->assertFalse(TestSingleKey::instance()->deleteModelCacheByAttribute($attribute));
        
        // get or set
        $this->assertEquals('get or set cache', TestSingleKey::instance()->getOrSetModelCacheByAttribute($attribute, function () {
            return 'get or set cache';
        }));
        
        // get or set again
        $this->assertEquals('get or set cache', TestSingleKey::instance()->getOrSetModelCacheByAttribute($attribute, function () {
            return 'get or set cache again';
        }));
    }
    
    /**
     * Test Active Methods
     */
    public function testActiveMethods()
    {
        $model = TestSingleKey::findOne(1);
        $model->deleteActiveCache();

        // start test
        // add
        $this->assertTrue($model->addActiveCache());
        $this->assertTrue($model->existsActiveCache());
        $this->assertEquals(['id' => 1, 'name' => 'TestName'], $model->getActiveCache());
        $this->assertFalse($model->addActiveCache());
        
        // delete
        $this->assertTrue($model->deleteActiveCache());
        $this->assertFalse($model->existsActiveCache());
        $this->assertFalse($model->getActiveCache());
        $this->assertFalse($model->deleteActiveCache());
        
        // set
        $this->assertTrue($model->setActiveCache());
        $this->assertTrue($model->existsActiveCache());
        $this->assertEquals(['id' => 1, 'name' => 'TestName'], $model->getActiveCache());
        
        // new active record
        $model = new TestSingleKey();
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
        $model = TestSingleKey::findOne(1);

        // start test
        // no changed
        $model->setActiveCache();
        $model->save();
        $this->assertTrue(TestSingleKey::instance()->existsModelCacheByAttribute(1));
        
        // update value
        $model->setActiveCache();
        $model->name = 'changed';
        $model->save();
        $this->assertFalse(TestSingleKey::instance()->existsModelCacheByAttribute(1));

        // update key
        $model->setActiveCache();
        TestSingleKey::instance()->setModelCacheByAttribute(2, 'dirty');
        $model->id = 2;
        $model->save();
        $this->assertFalse(TestSingleKey::instance()->existsModelCacheByAttribute(1));
        $this->assertFalse(TestSingleKey::instance()->existsModelCacheByAttribute(2));
        
        // delete
        $model->setActiveCache();
        $model->delete();
        $this->assertFalse(TestSingleKey::instance()->existsModelCacheByAttribute(2));
        
        // insert
        TestSingleKey::instance()->setModelCacheByAttribute(1, 'dirty');
        $model = new TestSingleKey();
        $model->id = 1;
        $model->name = 'insert';
        $model->save();
        $this->assertFalse(TestSingleKey::instance()->existsModelCacheByAttribute(1));
    }
    
    /**
     * Test trait moethods
     */
    public function testTraitMethods()
    {
        $model = TestSingleKey::findOne(1);
        TestSingleKey::instance()->deleteModelCacheByAttribute(1);
        
        // start test
        $this->assertEquals($model, TestSingleKey::findOrSetOneByAttribute(1));
        $this->assertEquals(['id' => 1, 'name' => 'TestName'], TestSingleKey::instance()->getModelCacheByAttribute(1));
        
        // again
        $this->assertEquals($model, TestSingleKey::findOrSetOneByAttribute(1));
        $this->assertEquals(['id' => 1, 'name' => 'TestName'], TestSingleKey::instance()->getModelCacheByAttribute(1));
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
        $db = TestSingleKey::getDb();
        $tableName = TestSingleKey::tableName();
        $migration = new Migration();
        
        // create table
        $db->createCommand()->createTable($tableName, [
            'id' => $migration->integer()->notNull(),
            'name' => $migration->string()->notNull(),
        ])->execute();
        $db->createCommand()->addPrimaryKey('pk_id', $tableName, ['id'])->execute();
        
        // insert data
        $db->createCommand()->batchInsert($tableName, ['id', 'name'], [
            [1, 'TestName'],
        ])->execute();
        
        Yii::info("Create table `{$tableName}`", __METHOD__);
    }
    
    /**
     * drop test table
     */
    protected function dropTestTable()
    {
        $db = TestSingleKey::getDb();
        $tableName = TestSingleKey::tableName();
        $db->createCommand()->dropTable($tableName)->execute();

        Yii::info("Drop table `{$tableName}`", __METHOD__);
    }
}