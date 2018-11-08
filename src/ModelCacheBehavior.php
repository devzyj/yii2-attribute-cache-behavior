<?php
/**
 * @link https://github.com/devzyj/yii2-attribute-cache-behavior
 * @copyright Copyright (c) 2018 Zhang Yan Jiong
 * @license http://opensource.org/licenses/BSD-3-Clause
 */
namespace devzyj\behaviors;

use Yii;
use yii\base\InvalidConfigException;
use yii\helpers\ArrayHelper;
use yii\helpers\Json;

/**
 * ModelCacheBehavior 提供了一些会对缓存键进行处理的缓存方法。
 * 
 * For example:
 * 
 * ```php
 * // User.php
 * class User extends \yii\base\Model
 * {
 *     public function behaviors()
 *     {
 *         return [
 *             [
 *                 'class' => 'devzyj\behaviors\ModelCacheBehavior',
 *                 //'cache' => 'cache',
 *                 //'defaultDuration' => 604800,
 *                 //'baseModelCacheKey' => ['User', 'Model'],
 *             ],
 *         ];
 *     }
 * }
 * 
 * // Usage
 * // get cache
 * User::instance()->getModelCache($key);
 * 
 * // exists cache
 * User::instance()->existsModelCache($key);
 * 
 * // set cache
 * User::instance()->setModelCache($key, $value, $duration, $dependency);
 * 
 * // add cache
 * User::instance()->addModelCache($key, $value, $duration, $dependency);
 * 
 * // delete cache
 * User::instance()->deleteModelCache($key);
 * 
 * // get or set cache
 * User::instance()->getOrSetModelCache($key, function ($behavior) use ($key) {
 *     $cacheKey = $behavior->processModelCacheKey($key);
 *     
 *     // todo some...
 *     
 *     return 'Cache Data';
 * }, $duration, $dependency);
 * ```
 * 
 * @author ZhangYanJiong <zhangyanjiong@163.com>
 * @since 1.0
 */
class ModelCacheBehavior extends \yii\base\Behavior
{
    /**
     * @var \yii\caching\CacheInterface|string 缓存组件。如果没有设置，则使用 [[Yii::$app->getCache()]]。
     */
    public $cache;
    
    /**
     * @var integer 缓存的默认持续时间（秒）。为 0 时，表示永久有效。
     * 如果没有设置，则使用 [[$cache]] 中的默认值。
     */
    public $defaultDuration;

    /**
     * @var array 基础的缓存键。加工缓存键时使用。在设置了 [[$processModelCacheKey]] 后无效。
     */
    public $baseModelCacheKey = [];
    
    /**
     * @var callable 加工缓存键的回调方法，方法应该返回处理后的缓存键。
     *
     * ```php
     * function ($key, $behavior) {
     *     // $key 缓存键。
     *     // $behavior 行为对像。
     * }
     * ```
     * 
     * 在设置回调方法后 [[$baseModelCacheKey]] 将会无效。
     */
    public $processModelCacheKey;
    
    /**
     * {@inheritdoc}
     */
    public function init()
    {
        // cache
        if ($this->cache === null) {
            $this->cache = Yii::$app->getCache();
        } elseif (is_string($this->cache)) {
            $this->cache = Yii::$app->get($this->cache);
        } elseif (!$this->cache instanceof \yii\caching\CacheInterface) {
            throw new InvalidConfigException(get_class($this) . '::$cache is invalid.');
        }
        
        parent::init();
    }

    /**
     * 加工缓存键。
     * 
     * @param mixed $key 需要进行加工的缓存键。
     * @return array 加工后的缓存键。返回 [[$baseModelCacheKey]] 和 `$key` 合并后的数据。
     */
    public function processModelCacheKey($key)
    {
        if ($this->processModelCacheKey) {
            return call_user_func($this->processModelCacheKey, $key, $this);
        }
        
        return ArrayHelper::merge($this->baseModelCacheKey, is_array($key) ? $key : [$key]);
    }

    /**
     * 获取缓存。
     * 
     * @param mixed $key 缓存键。可以是一个简单的字符串或复杂的数据结构。
     * @return mixed 缓存的数据。如果缓存的数据不存在，或相关依赖项已更改，则返回 `false`。
     * @see \yii\caching\CacheInterface::get()
     */
    public function getModelCache($key)
    {
        $cacheKey = $this->processModelCacheKey($key);
        Yii::debug('Gets cache value for key ' . Json::encode($cacheKey), __METHOD__);
        return $this->cache->get($cacheKey);
    }

    /**
     * 缓存是否存在。
     * 
     * 注意：此方法不检查与缓存数据相关联的依赖项是否已更改。因此，调用 [[get()]] 可能返回 `false`，而 [[exist()]] 返回 `true`。
     * 
     * @param mixed $key 缓存键。可以是一个简单的字符串或复杂的数据结构。
     * @return boolean 是否存在。
     * @see \yii\caching\CacheInterface::exists()
     */
    public function existsModelCache($key)
    {
        $cacheKey = $this->processModelCacheKey($key);
        Yii::debug('Checks cache exists for key ' . Json::encode($cacheKey), __METHOD__);
        return $this->cache->exists($cacheKey);
    }
    
    /**
     * 设置缓存。
     *
     * @param mixed $key 缓存键。可以是一个简单的字符串或复杂的数据结构。
     * @param mixed $value 需要缓存的数据。如果需要缓存 `false`，请使用数组 `[false]` 进行缓存，可以避免和 [[getModelCache()]] 中的 `false` 返回值冲突。
     * @param integer $duration 缓存的持续时间（秒）。如果为 `null` 则使用默认值 [[$defaultDuration]]。
     * @param \yii\caching\Dependency $dependency 缓存的依赖项。如果依赖项发生了变化，那么使用 [[getModelCache()]] 时，缓存将失效。
     * @return boolean 是否设置成功。
     * @see \yii\caching\CacheInterface::set()
     */
    public function setModelCache($key, $value, $duration = null, $dependency = null)
    {
        if ($duration === null) {
            $duration = $this->defaultDuration;
        }

        $cacheKey = $this->processModelCacheKey($key);
        Yii::debug('Sets cache value for key ' . Json::encode($cacheKey), __METHOD__);
        return $this->cache->set($cacheKey, $value, $duration, $dependency);
    }
    
    /**
     * 增加缓存。
     * 
     * 如果缓存已经存在，则不会执行任何操作。
     * 
     * @param mixed $key 缓存键。可以是一个简单的字符串或复杂的数据结构。
     * @param mixed $value 需要缓存的数据。如果需要缓存 `false`，请使用数组 `[false]` 进行缓存，可以避免和 [[getModelCache()]] 中的 `false` 返回值冲突。
     * @param integer $duration 缓存的持续时间（秒）。默认为永不过期。
     * @param \yii\caching\Dependency $dependency 缓存的依赖项。如果依赖项发生了变化，那么使用 [[getModelCache()]] 时，缓存将失效。
     * @return boolean 是否增加成功。
     * @see \yii\caching\CacheInterface::add()
     */
    public function addModelCache($key, $value, $duration = 0, $dependency = null)
    {
        $cacheKey = $this->processModelCacheKey($key);
        Yii::debug('Adds cache value for key ' . Json::encode($cacheKey), __METHOD__);
        return $this->cache->add($cacheKey, $value, $duration, $dependency);
    }
    
    /**
     * 删除缓存。
     *
     * @param mixed $key 缓存键。可以是一个简单的字符串或复杂的数据结构。
     * @return boolean 是否删除成功。
     * @see \yii\caching\CacheInterface::delete()
     */
    public function deleteModelCache($key)
    {
        $cacheKey = $this->processModelCacheKey($key);
        Yii::debug('Deletes cache value for key ' . Json::encode($cacheKey), __METHOD__);
        return $this->cache->delete($cacheKey);
    }
    
    /**
     * 获取或者设置缓存。
     * 
     * Usage example:
     *
     * ```php
     * $this->getOrSetModelCache($key, function ($behavior) use ($key) {
     *     $cacheKey = $behavior->processModelCacheKey($key);
     *     
     *     // todo some...
     *     
     *     return 'cache data';
     * }, $duration, $dependency);
     * ```
     *
     * @param mixed $key 缓存键。可以是一个简单的字符串或复杂的数据结构。
     * @param callable|\Closure $callable 回调方法。用于生成缓存的值。如果 `$callable` 返回 `false`，则该值不会被缓存。
     * @param integer $duration 缓存的持续时间（秒）。如果为 `null` 则使用默认值 [[$defaultDuration]]。
     * @param \yii\caching\Dependency $dependency 缓存的依赖项。如果依赖项发生了变化，那么使用 [[getModelCache()]] 时，缓存将失效。
     * @return mixed 返回 `$callable` 执行的结果。
     */
    public function getOrSetModelCache($key, $callable, $duration = null, $dependency = null)
    {
        if (($value = $this->getModelCache($key)) !== false) {
            return $value;
        }
        
        $value = call_user_func($callable, $this);
        $this->setModelCache($key, $value, $duration, $dependency);
        return $value;
    }
}