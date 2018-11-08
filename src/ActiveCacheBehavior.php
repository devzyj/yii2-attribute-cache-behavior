<?php
/**
 * @link https://github.com/devzyj/yii2-attribute-cache-behavior
 * @copyright Copyright (c) 2018 Zhang Yan Jiong
 * @license http://opensource.org/licenses/BSD-3-Clause
 */
namespace devzyj\behaviors;

use Yii;
use yii\base\InvalidConfigException;
use yii\db\BaseActiveRecord;
use yii\helpers\ArrayHelper;

/**
 * ActiveCacheBehavior 提供了一些缓存 [[\yii\db\ActiveRecord]] 属性的方法。
 * 并且当某些事件发生时，自动使缓存失效。
 * 
 * For example:
 * 
 * ```php
 * // User.php
 * class User extends \yii\db\ActiveRecord
 * {
 *     use \devzyj\behaviors\ActiveCacheBehaviorTrait;
 *     
 *     public function behaviors()
 *     {
 *         return [
 *             [
 *                 'class' => 'devzyj\behaviors\ActiveCacheBehavior',
 *                 //'cache' => 'cache',
 *                 //'defaultDuration' => 604800,
 *                 //'baseModelCacheKey' => ['User', 'PrimaryKey'],
 *                 //'keyAttributes' => static::primaryKey(),
 *                 //'valueAttributes' => $this->attributes(),
 *             ],
 *         ];
 *     }
 * }
 * 
 * 
 * // Using trait methods
 * // Returns a single active record model instance.
 * // Sets cache value if there is no cache available for the `$primaryKey`.
 * $user = User::findOrSetOneByAttribute($primaryKey);
 * 
 * // No changed, cache value exists.
 * $user->save();
 * 
 * // Changed, cache value not exists.
 * $user->name = 1;
 * $user->save();
 * 
 * // Deleted, cache value not exists.
 * $user->delete();
 * 
 * // Gets cache value for model instance.
 * $user->getActiveCache();
 * 
 * // Checks cache value exists for model instance.
 * $user->existsActiveCache();
 * 
 * // Sets cache value for model instance.
 * $user->setActiveCache();
 * 
 * // Adds cache value for model instance.
 * $user->addActiveCache();
 * 
 * // Deletes cache value for model instance.
 * $user->deleteActiveCache();
 * 
 * 
 * // Using single key attribute
 * // ActiveCacheBehavior::$keyAttributes = ['id']
 * $id = 1;
 * 
 * // get cache
 * User::instance()->getModelCacheByAttribute($id);
 * // OR
 * User::instance()->getModelCache(['id' => $id]);
 * 
 * // exists cache
 * User::instance()->existsModelCacheByAttribute($id);
 * // OR
 * User::instance()->existsModelCache(['id' => $id]);
 * 
 * // set cache
 * User::instance()->setModelCacheByAttribute($id, $value, $duration, $dependency);
 * // OR
 * User::instance()->setModelCache(['id' => $id], $value, $duration, $dependency);
 * 
 * // add cache
 * User::instance()->addModelCacheByAttribute($id, $value, $duration, $dependency);
 * // OR
 * User::instance()->addModelCache(['id' => $id], $value, $duration, $dependency);
 * 
 * // delete cache
 * User::instance()->deleteModelCacheByAttribute($id);
 * // OR
 * User::instance()->deleteModelCache(['id' => $id]);
 * 
 * // get or set cache
 * User::instance()->getOrSetModelCacheByAttribute($id, function ($behavior) use ($id) {
 *     $condition = $behavior->ensureActiveKeyAttribute($id);
 *     $model = User::findOne($condition);
 *     return $model ? $model->getActiveCacheValue() : false;
 * }, $duration, $dependency);
 * // OR
 * $condition = ['id' => $id];
 * User::instance()->getOrSetModelCache($condition, function ($behavior) use ($condition) {
 *     $model = User::findOne($condition);
 *     return $model ? $model->getActiveCacheValue() : false;
 * }, $duration, $dependency);
 * 
 * // trait method: find and return ActiveRecord from cache or database
 * User::findOrSetOneByAttribute($id, $duration, $dependency);
 * 
 * 
 * // Using composite key attribute
 * // ActiveCacheBehavior::$keyAttributes = ['id1', 'id2']
 * $id1 = 1;
 * $id2 = 2;
 * $ids = [$id1, $id2];
 * 
 * // get cache
 * User::instance()->getModelCacheByAttribute($ids);
 * // OR
 * User::instance()->getModelCache(['id1' => $id1, 'id2' => $id2]);
 * 
 * // exists cache
 * User::instance()->existsModelCacheByAttribute($ids);
 * // OR
 * User::instance()->existsModelCache(['id1' => $id1, 'id2' => $id2]);
 * 
 * // set cache
 * User::instance()->setModelCacheByAttribute($ids, $value, $duration, $dependency);
 * // OR
 * User::instance()->setModelCache(['id1' => $id1, 'id2' => $id2], $value, $duration, $dependency);
 * 
 * // add cache
 * User::instance()->addModelCacheByAttribute($ids, $value, $duration, $dependency);
 * // OR
 * User::instance()->addModelCache(['id1' => $id1, 'id2' => $id2], $value, $duration, $dependency);
 * 
 * // delete cache
 * User::instance()->deleteModelCacheByAttribute($ids);
 * // OR
 * User::instance()->deleteModelCache(['id1' => $id1, 'id2' => $id2]);
 * 
 * // get or set cache
 * User::instance()->getOrSetModelCacheByAttribute($ids, function ($behavior) use ($ids) {
 *     $condition = $behavior->ensureActiveKeyAttribute($ids);
 *     $model = User::findOne($condition);
 *     return $model ? $model->getActiveCacheValue() : false;
 * }, $duration, $dependency);
 * // OR
 * $condition = ['id1' => $id1, 'id2' => $id2];
 * User::instance()->getOrSetModelCache($condition, function ($behavior) use ($condition) {
 *     $model = User::findOne($condition);
 *     return $model ? $model->getActiveCacheValue() : false;
 * }, $duration, $dependency);
 * 
 * // trait method: find and return ActiveRecord from cache or database
 * User::findOrSetOneByAttribute($ids, $duration, $dependency);
 * 
 * 
 * // Using database transactions, and the `commit()` time is too long
 * $transaction = $db->beginTransaction();
 * try {
 *     // old cache key.
 *     $oldKey = $model->getActiveCacheKey();
 *     
 *     // update
 *     $model->attributes = $attributes;
 *     $model->save();
 *     $transaction->commit();
 *     
 *     // delete old cache.
 *     $model->deleteModelCache($oldKey);
 * } catch (\Exception $e) {
 *     $transaction->rollBack();
 *     throw $e;
 * }
 * ```
 * 
 * @property string[] $keyAttributes 缓存键属性。如果没有设置，则使用 [[$owner::primaryKey()]]。
 * @property string[] $valueAttributes 缓存值属性。如果没有设置，则使用 [[$owner::attributes()]]。
 * @property array $activeCacheKey 缓存键属性值列表。
 * @property array $activeCacheValue 缓存值属性值列表。
 * 
 * @author ZhangYanJiong <zhangyanjiong@163.com>
 * @since 1.0
 */
class ActiveCacheBehavior extends ModelCacheBehavior
{
    /**
     * @var \yii\db\BaseActiveRecord
     */
    public $owner;

    /**
     * @var string[] 缓存键属性。
     */
    private $_keyAttributes;

    /**
     * @var string[] 缓存值属性。
     */
    private $_valueAttributes;
    
    /**
     * @var array 临时的缓存键。
     */
    private $_oldCacheKey;
    
    /**
     * 获取缓存键属性。
     * 
     * @throws \yii\base\InvalidConfigException
     * @return string[]
     */
    public function getKeyAttributes()
    {
        if ($this->_keyAttributes === null) {
            $modelClass = $this->owner;
            $this->_keyAttributes = $modelClass::primaryKey();
        } elseif (is_string($this->_keyAttributes)) {
            $this->_keyAttributes = [$this->_keyAttributes];
        } elseif (!is_array($this->_keyAttributes)) {
            throw new InvalidConfigException(get_class($this) . '::$keyAttributes invalid.');
        }
        
        return $this->_keyAttributes;
    }
    
    /**
     * 设置缓存键属性。
     * 
     * @param string|string[] $value
     */
    public function setKeyAttributes($value)
    {
        $this->_keyAttributes = $value;
    }

    /**
     * 获取缓存值属性。
     *
     * @throws \yii\base\InvalidConfigException
     * @return string[]
     */
    public function getValueAttributes()
    {
        if ($this->_valueAttributes === null) {
            $this->_valueAttributes = $this->owner->attributes();
        } elseif (!is_array($this->_valueAttributes)) {
            throw new InvalidConfigException(get_class($this) . '::$valueAttributes invalid.');
        }
    
        return $this->_valueAttributes;
    }
    
    /**
     * 设置缓存值属性。
     *
     * @param string[] $value
     */
    public function setValueAttributes($value)
    {
        $this->_valueAttributes = $value;
    }
    
    /**
     * 获取属性值。
     * 
     * @return array
     * @see \yii\db\BaseActiveRecord::getOldAttributes()
     */
    protected function getOwnerAttributes()
    {
        return $this->owner->getOldAttributes();
    }
    
    /**
     * 获取缓存键属性值列表。
     * 
     * @return array
     */
    public function getActiveCacheKey()
    {
        $key = [];
        $attributes = $this->getOwnerAttributes();
        foreach ($this->getKeyAttributes() as $keyAttribute) {
            $key[$keyAttribute] = ArrayHelper::getValue($attributes, $keyAttribute);
        }
        
        return $key;
    }

    /**
     * 获取缓存值属性值列表。
     * 
     * @return array
     */
    public function getActiveCacheValue()
    {
        $value = [];
        $attributes = $this->getOwnerAttributes();
        foreach ($this->getValueAttributes() as $valueAttribute) {
            $value[$valueAttribute] = ArrayHelper::getValue($attributes, $valueAttribute);
        }
        
        return $value;
    }

    /**
     * 获取活动记录的缓存。
     *
     * @return mixed 缓存的数据。如果是新的活动记录，或缓存的数据不存在，或相关依赖项已更改，则返回 `false`。
     * @see ModelCacheBehavior::getModelCache()
     */
    public function getActiveCache()
    {
        if (!$this->owner->getIsNewRecord()) {
            $key = $this->getActiveCacheKey();
            return $this->getModelCache($key);
        }
        
        return false;
    }

    /**
     * 活动记录的缓存是否存在。
     *
     * @return mixed 是否存在。如果是新的活动记录，或缓存的数据不存在，则返回 `false`。
     * @see ModelCacheBehavior::existsModelCache()
     */
    public function existsActiveCache()
    {
        if (!$this->owner->getIsNewRecord()) {
            $key = $this->getActiveCacheKey();
            return $this->existsModelCache($key);
        }
        
        return false;
    }
    
    /**
     * 设置活动记录的缓存。
     * 
     * @param integer $duration 缓存的持续时间（秒）。
     * @param \yii\caching\Dependency $dependency 缓存的依赖项。
     * @return boolean 是否设置成功。
     * @see ModelCacheBehavior::setModelCache()
     */
    public function setActiveCache($duration = null, $dependency = null)
    {
        if (!$this->owner->getIsNewRecord()) {
            $key = $this->getActiveCacheKey();
            $value = $this->getActiveCacheValue();
            return $this->setModelCache($key, $value, $duration, $dependency);
        }
        
        return false;
    }
    
    /**
     * 增加活动记录的缓存。
     * 
     * 如果缓存已经存在，则不会执行任何操作。
     * 
     * @param integer $duration 缓存的持续时间（秒）。
     * @param \yii\caching\Dependency $dependency 缓存的依赖项。
     * @return boolean 是否增加成功。
     * @see ModelCacheBehavior::addModelCache()
     */
    public function addActiveCache($duration = null, $dependency = null)
    {
        if (!$this->owner->getIsNewRecord()) {
            $key = $this->getActiveCacheKey();
            $value = $this->getActiveCacheValue();
            return $this->addModelCache($key, $value, $duration, $dependency);
        }
        
        return false;
    }

    /**
     * 删除活动记录的缓存。
     *
     * @return boolean 是否删除成功。
     * @see ModelCacheBehavior::deleteModelCache()
     */
    public function deleteActiveCache()
    {
        if (!$this->owner->getIsNewRecord()) {
            $key = $this->getActiveCacheKey();
            return $this->deleteModelCache($key);
        }
        
        return false;
    }
    
    /**
     * {@inheritdoc}
     */
    public function events()
    {
        return [
            BaseActiveRecord::EVENT_AFTER_INSERT => 'afterInsert',
            BaseActiveRecord::EVENT_BEFORE_UPDATE => 'beforeUpdate',
            BaseActiveRecord::EVENT_AFTER_UPDATE => 'afterUpdate',
            BaseActiveRecord::EVENT_BEFORE_DELETE => 'beforeDelete',
            BaseActiveRecord::EVENT_AFTER_DELETE => 'afterDelete',
        ];
    }
    
    /**
     * @param \yii\db\AfterSaveEvent $event
     * @see \yii\db\BaseActiveRecord::afterSave()
     */
    public function afterInsert($event)
    {
        // 删除可能已存在与新增数据相关的缓存。
        Yii::debug("Deletes cache value after insert.", __METHOD__);
        $this->deleteActiveCache();
    }

    /**
     * @param \yii\base\ModelEvent $event
     * @see \yii\db\BaseActiveRecord::beforeDelete()
     */
    public function beforeDelete($event)
    {
        // 保存临时的旧缓存键，用于删除成功后删除旧的缓存。
        $this->_oldCacheKey = $this->getActiveCacheKey();
    }
    
    /**
     * @see \yii\db\BaseActiveRecord::afterDelete()
     */
    public function afterDelete()
    {
        // 删除缓存。
        Yii::debug("Deletes cache value after delete.", __METHOD__);
        $this->deleteModelCache($this->_oldCacheKey);
    }
    
    /**
     * @param \yii\base\ModelEvent $event
     * @see \yii\db\BaseActiveRecord::beforeUpdate()
     */
    public function beforeUpdate($event)
    {
        // 保存临时的旧缓存键，用于更新缓存键后删除旧的缓存。
        $this->_oldCacheKey = $this->getActiveCacheKey();
    }
    
    /**
     * @param \yii\db\AfterSaveEvent $event
     * @see \yii\db\BaseActiveRecord::afterSave()
     */
    public function afterUpdate($event)
    {
        // 修改过的属性列表。
        $changedAttributes = $event->changedAttributes;
        if (empty($changedAttributes)) {
            return;
        }
        
        if ($this->validateChangedKeyAttributes($changedAttributes)) {
            // 缓存键被修改时，删除修改前的旧缓存。
            Yii::debug("Deletes old cache value after update.", __METHOD__);
            $this->deleteModelCache($this->_oldCacheKey);
        }
        
        // 数据修改后，删除已存在的缓存。
        Yii::debug("Deletes cache value after update.", __METHOD__);
        $this->deleteActiveCache();
    }
    
    /**
     * 验证缓存键是否更改。
     * 
     * @param array $changedAttributes 已更改的数据。
     * @return boolean 是否更改过。如果返回 `true`，则会删除缓存，反之则不会删除缓存。
     */
    protected function validateChangedKeyAttributes($changedAttributes)
    {
        $keyAttributes = $this->getKeyAttributes();
        foreach ($changedAttributes as $attribute => $value) {
            if (in_array($attribute, $keyAttributes)) {
                return true;
            }
        }
        
        return false;
    }

    /**
     * 确保缓存键属性值的格式是有效的。
     * 
     * 属性值 `$attribute` 不为数组时，返回一个使用 `$attribute` 为填充值，[[$keyAttributes]] 为键的列值数组。
     * 属性值 `$attribute` 为数组时：
     *     - 数组中的元素数量必需与设置的 [[$keyAttributes]] 数量相等。
     *     - 数组为索引数组时，可以按属性顺序传递缓存键。
     *     - 数组为关联数组时，必须存在与设置的 [[$keyAttributes]] 一样的属性。
     * 
     * @param mixed $attribute 属性值。可以是单一的属性值，或索引数组，或列值数组。
     * @return array|false 返回一个以 [[$keyAttributes]] 为键的列值数组。如果 `$attribute` 不正确，则返回 `false`。
     */
    public function ensureActiveKeyAttribute($attribute)
    {
        $keyAttributes = $this->getKeyAttributes();
        if (is_array($attribute)) {
            if (empty($attribute)) {
                // 不能为空数组。
                Yii::debug('The `$attribute` cannot be an empty array.');
                return false;
            } elseif (count($attribute) != count($keyAttributes)) {
                // 数组中的元素数量必需与设置的缓存键属性数量相等。
                Yii::debug('The number of `$attribute` and `$keyAttributes` is not equal.');
                return false;
            } elseif (ArrayHelper::isIndexed($attribute)) {
                // 使用索引数组，按属性顺序传递。
                return array_combine($keyAttributes, $attribute);
            }
            
            // 处理关联数组，确保数组元素的顺序与设置的缓存键属性顺序相同。
            $keys = [];
            foreach ($keyAttributes as $keyAttribute) {
                if (!array_key_exists($keyAttribute, $attribute)) {
                    // 关联数组中的属性名称与设置的缓存键属性不同。
                    Yii::debug('The key of `$attribute` and `$keyAttributes` are not equal.');
                    return false;
                }
                
                $keys[$keyAttribute] = $attribute[$keyAttribute];
            }
            
            return $keys;
        }
        
        // 返回一个使用 `$attribute` 为填充值，[[$keyAttributes]] 为键的列值数组。
        return array_fill_keys($keyAttributes, $attribute);
    }
    
    /**
     * 通过属性值，获取缓存。
     * 
     * @param mixed $attribute 属性值。参考 [[ensureActiveKeyAttribute()]]。
     * @return mixed 缓存的数据。
     * @see ensureActiveKeyAttribute()
     * @see ModelCacheBehavior::getModelCache()
     */
    public function getModelCacheByAttribute($attribute)
    {
        $key = $this->ensureActiveKeyAttribute($attribute);
        if ($key === false) {
            return false;
        }
        
        return $this->getModelCache($key);
    }
    
    /**
     * 通过属性值，判断缓存是否存在。
     * 
     * @param mixed $attribute 属性值。参考 [[ensureActiveKeyAttribute()]]。
     * @return boolean 是否存在。
     * @see ensureActiveKeyAttribute()
     * @see ModelCacheBehavior::existsModelCache()
     */
    public function existsModelCacheByAttribute($attribute)
    {
        $key = $this->ensureActiveKeyAttribute($attribute);
        if ($key === false) {
            return false;
        }
        
        return $this->existsModelCache($key);
    }

    /**
     * 通过属性值，设置缓存。
     * 
     * @param mixed $attribute 属性值。参考 [[ensureActiveKeyAttribute()]]。
     * @param mixed $value 需要缓存的数据。
     * @param integer $duration 缓存的持续时间（秒）。
     * @param \yii\caching\Dependency $dependency 缓存的依赖项。
     * @return boolean 是否设置成功。
     * @see ensureActiveKeyAttribute()
     * @see ModelCacheBehavior::setModelCache()
     */
    public function setModelCacheByAttribute($attribute, $value, $duration = null, $dependency = null)
    {
        $key = $this->ensureActiveKeyAttribute($attribute);
        if ($key === false) {
            return false;
        }
        
        return $this->setModelCache($key, $value, $duration, $dependency);
    }

    /**
     * 通过属性值，增加缓存。
     * 
     * @param mixed $attribute 属性值。参考 [[ensureActiveKeyAttribute()]]。
     * @param mixed $value 需要缓存的数据。
     * @param integer $duration 缓存的持续时间（秒）。
     * @param \yii\caching\Dependency $dependency 缓存的依赖项。
     * @return boolean 是否增加成功。
     * @see ensureActiveKeyAttribute()
     * @see ModelCacheBehavior::addModelCache()
     */
    public function addModelCacheByAttribute($attribute, $value, $duration = null, $dependency = null)
    {
        $key = $this->ensureActiveKeyAttribute($attribute);
        if ($key === false) {
            return false;
        }
        
        return $this->addModelCache($key, $value, $duration, $dependency);
    }
    
    /**
     * 通过属性值，删除缓存。
     * 
     * @param mixed $attribute 属性值。参考 [[ensureActiveKeyAttribute()]]。
     * @return boolean 是否删除成功。
     * @see ensureActiveKeyAttribute()
     * @see ModelCacheBehavior::deleteModelCache()
     */
    public function deleteModelCacheByAttribute($attribute)
    {
        $key = $this->ensureActiveKeyAttribute($attribute);
        if ($key === false) {
            return false;
        }
        
        return $this->deleteModelCache($key);
    }
    
    /**
     * 通过属性值，获取或者设置缓存。
     * 
     * @param mixed $attribute 属性值。参考 [[ensureActiveKeyAttribute()]]。
     * @param callable|\Closure $callable 回调方法。
     * @param integer $duration 缓存的持续时间（秒）。
     * @param \yii\caching\Dependency $dependency 缓存的依赖项。
     * @return mixed 属性值不正确时返回 `false`，否则返回 `$callable` 执行的结果。
     * @see ensureActiveKeyAttribute()
     * @see ModelCacheBehavior::getOrSetModelCache()
     */
    public function getOrSetModelCacheByAttribute($attribute, $callable, $duration = null, $dependency = null)
    {
        $key = $this->ensureActiveKeyAttribute($attribute);
        if ($key === false) {
            return false;
        }
        
        return $this->getOrSetModelCache($key, $callable, $duration, $dependency);
    }
}