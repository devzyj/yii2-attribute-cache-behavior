# ActiveRecord attribute cache behavior

提供了一些缓存 `ActiveRecord` 属性的方法。并且当某些事件发生时，自动使缓存失效。


Installation
------------

The preferred way to install this extension is through [composer](http://getcomposer.org/download/).

Either run

```bash
composer require --prefer-dist "devzyj/yii2-attribute-cache-behavior" "~1.0"
```

or add

```json
"devzyj/yii2-attribute-cache-behavior" : "~1.0"
```

to the require section of your application's `composer.json` file.


Usage
-----

```php
// User.php
class User extends \yii\db\ActiveRecord
{
    use \devzyj\behaviors\ActiveCacheBehaviorTrait;
    
    public function behaviors()
    {
        return [
            [
                'class' => 'devzyj\behaviors\ActiveCacheBehavior',
                //'cache' => 'cache',
                //'defaultDuration' => 604800,
                //'baseModelCacheKey' => ['User', 'PrimaryKey'],
                //'keyAttributes' => static::primaryKey(),
                //'valueAttributes' => $this->attributes(),
            ],
        ];
    }
}


// Using trait methods
// Returns a single active record model instance.
// Sets cache value if there is no cache available for the `$primaryKey`.
$user = User::findOrSetOneByAttribute($primaryKey);

// No changed, cache value exists.
$user->save();

// Changed, cache value not exists.
$user->name = 1;
$user->save();

// Deleted, cache value not exists.
$user->delete();

// Gets cache value for model instance.
$user->getActiveCache();

// Checks cache value exists for model instance.
$user->existsActiveCache();

// Sets cache value for model instance.
$user->setActiveCache();

// Adds cache value for model instance.
$user->addActiveCache();

// Deletes cache value for model instance.
$user->deleteActiveCache();


// Using single key attribute
// ActiveCacheBehavior::$keyAttributes = ['id']
$id = 1;

// get cache
User::instance()->getModelCacheByAttribute($id);
// OR
User::instance()->getModelCache(['id' => $id]);

// exists cache
User::instance()->existsModelCacheByAttribute($id);
// OR
User::instance()->existsModelCache(['id' => $id]);

// set cache
User::instance()->setModelCacheByAttribute($id, $value, $duration, $dependency);
// OR
User::instance()->setModelCache(['id' => $id], $value, $duration, $dependency);

// add cache
User::instance()->addModelCacheByAttribute($id, $value, $duration, $dependency);
// OR
User::instance()->addModelCache(['id' => $id], $value, $duration, $dependency);

// delete cache
User::instance()->deleteModelCacheByAttribute($id);
// OR
User::instance()->deleteModelCache(['id' => $id]);

// get or set cache
User::instance()->getOrSetModelCacheByAttribute($id, function ($behavior) use ($id) {
    $condition = $behavior->ensureActiveKeyAttribute($id);
    if (!$condition) {
        $condition = $id;
    }
    $model = User::findOne($condition);
    return $model ? $model->getActiveCacheValue() : false;
}, $duration, $dependency);
// OR
$condition = ['id' => $id];
User::instance()->getOrSetModelCache($condition, function ($behavior) use ($condition) {
    $model = User::findOne($condition);
    return $model ? $model->getActiveCacheValue() : false;
}, $duration, $dependency);

// trait method: find and return ActiveRecord from cache or database
User::findOrSetOneByAttribute($id, $duration, $dependency);


// Using composite key attribute
// ActiveCacheBehavior::$keyAttributes = ['id1', 'id2']
$id1 = 1;
$id2 = 2;
$ids = [$id1, $id2];

// get cache
User::instance()->getModelCacheByAttribute($ids);
// OR
User::instance()->getModelCache(['id1' => $id1, 'id2' => $id2]);

// exists cache
User::instance()->existsModelCacheByAttribute($ids);
// OR
User::instance()->existsModelCache(['id1' => $id1, 'id2' => $id2]);

// set cache
User::instance()->setModelCacheByAttribute($ids, $value, $duration, $dependency);
// OR
User::instance()->setModelCache(['id1' => $id1, 'id2' => $id2], $value, $duration, $dependency);

// add cache
User::instance()->addModelCacheByAttribute($ids, $value, $duration, $dependency);
// OR
User::instance()->addModelCache(['id1' => $id1, 'id2' => $id2], $value, $duration, $dependency);

// delete cache
User::instance()->deleteModelCacheByAttribute($ids);
// OR
User::instance()->deleteModelCache(['id1' => $id1, 'id2' => $id2]);

// get or set cache
User::instance()->getOrSetModelCacheByAttribute($ids, function ($behavior) use ($ids) {
    $condition = $behavior->ensureActiveKeyAttribute($ids);
    if (!$condition) {
        $condition = $ids;
    }
    $model = User::findOne($condition);
    return $model ? $model->getActiveCacheValue() : false;
}, $duration, $dependency);
// OR
$condition = ['id1' => $id1, 'id2' => $id2];
User::instance()->getOrSetModelCache($condition, function ($behavior) use ($condition) {
    $model = User::findOne($condition);
    return $model ? $model->getActiveCacheValue() : false;
}, $duration, $dependency);

// trait method: find and return ActiveRecord from cache or database
User::findOrSetOneByAttribute($ids, $duration, $dependency);


// Using database transactions, and the `commit()` time is too long
$transaction = $db->beginTransaction();
try {
    // old cache key.
    $oldKey = $model->getActiveCacheKey();
    
    // update
    $model->attributes = $attributes;
    $model->save();
    $transaction->commit();
    
    // delete old cache.
    $model->deleteModelCache($oldKey);
} catch (\Exception $e) {
    $transaction->rollBack();
    throw $e;
}
```
