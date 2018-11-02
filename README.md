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


// Using Object Instance
$user = User::findOne(1);

// get cache
$user->getActiveCache();

// exists cache
$user->existsActiveCache();

// set cache
$user->setActiveCache();

// add cache
$user->addActiveCache();

// delete cache
$user->deleteActiveCache();


// Using single key attribute
$key = 1; // `ActiveCacheBehavior::$keyAttributes` values

// get cache
User::instance()->getModelCacheByAttribute($key);
// OR
User::instance()->getModelCache(['keyAttribute' => $key]);

// exists cache
User::instance()->existsModelCacheByAttribute($key);
// OR
User::instance()->existsModelCache(['keyAttribute' => $key]);

// set cache
User::instance()->setModelCacheByAttribute($key, $value, $duration, $dependency);
// OR
User::instance()->setModelCache(['keyAttribute' => $key], $value, $duration, $dependency);

// add cache
User::instance()->addModelCacheByAttribute($key, $value, $duration, $dependency);
// OR
User::instance()->addModelCache(['keyAttribute' => $key], $value, $duration, $dependency);

// delete cache
User::instance()->deleteModelCacheByAttribute($key);
// OR
User::instance()->deleteModelCache(['keyAttribute' => $key]);

// get or set cache
User::instance()->getOrSetModelCacheByAttribute($key, function ($behavior) use ($key) {
    $condition = $behavior->ensureActiveKeyAttribute($key);
    $model = User::findOne($condition);
    return $model ? $model->getActiveCacheValue() : false;
}, $duration, $dependency);
// OR
$condition = ['keyAttribute' => $key];
User::instance()->getOrSetModelCache($condition, function ($behavior) use ($condition) {
    $model = User::findOne($condition);
    return $model ? $model->getActiveCacheValue() : false;
}, $duration, $dependency);

// trait method: find and return ActiveRecord from cache or database
User::findOrSetOneByAttribute($key, $duration, $dependency);


// Using composite key attribute
$key1 = 1; // `ActiveCacheBehavior::$keyAttributes` values
$key2 = 2; // `ActiveCacheBehavior::$keyAttributes` values
$keys = [$key1, $key2];

// get cache
User::instance()->getModelCacheByAttribute($keys);
// OR
User::instance()->getModelCache(['keyAttribute1' => $key1, 'keyAttribute2' => $key2]);

// exists cache
User::instance()->existsModelCacheByAttribute($keys);
// OR
User::instance()->existsModelCache(['keyAttribute1' => $key1, 'keyAttribute2' => $key2]);

// set cache
User::instance()->setModelCacheByAttribute($keys, $value, $duration, $dependency);
// OR
User::instance()->setModelCache(['keyAttribute1' => $key1, 'keyAttribute2' => $key2], $value, $duration, $dependency);

// add cache
User::instance()->addModelCacheByAttribute($keys, $value, $duration, $dependency);
// OR
User::instance()->addModelCache(['keyAttribute1' => $key1, 'keyAttribute2' => $key2], $value, $duration, $dependency);

// delete cache
User::instance()->deleteModelCacheByAttribute($keys);
// OR
User::instance()->deleteModelCache(['keyAttribute1' => $key1, 'keyAttribute2' => $key2]);

// get or set cache
User::instance()->getOrSetModelCacheByAttribute($keys, function ($behavior) use ($keys) {
    $condition = $behavior->ensureActiveKeyAttribute($keys);
    $model = User::findOne($condition);
    return $model ? $model->getActiveCacheValue() : false;
}, $duration, $dependency);
// OR
$condition = ['keyAttribute1' => $key1, 'keyAttribute2' => $key2];
User::instance()->getOrSetModelCache($condition, function ($behavior) use ($condition) {
    $model = User::findOne($condition);
    return $model ? $model->getActiveCacheValue() : false;
}, $duration, $dependency);

// trait method: find and return ActiveRecord from cache or database
User::findOrSetOneByAttribute($keys, $duration, $dependency);


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
