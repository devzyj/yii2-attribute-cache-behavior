<?php
/**
 * @link https://github.com/devzyj/yii2-attribute-cache-behavior
 * @copyright Copyright (c) 2018 Zhang Yan Jiong
 * @license http://opensource.org/licenses/BSD-3-Clause
 */
namespace devzyj\behaviors;

use Yii;

/**
 * ActiveCacheBehaviorTrait
 * 
 * @author ZhangYanJiong <zhangyanjiong@163.com>
 * @since 1.0
 */
trait ActiveCacheBehaviorTrait
{
    /**
     * 通过属性值，从缓存或者数据表中查找并返回单个活动记录模型实例。
     *
     * @param mixed $attribute 属性值。参考 [[ensureActiveKeyAttribute()]]。
     * @param integer $duration 设置缓存的持续时间（秒）
     * @param \yii\caching\Dependency $dependency 设置缓存的依赖项。
     * @return \yii\db\ActiveRecord|null 返回匹配条件的 ActiveRecord 实例，如果属性值不正确或者没有匹配，则返回 `null`。
     * @see ActiveCacheBehavior::ensureActiveKeyAttribute()
     * @see ActiveCacheBehavior::getOrSetModelCacheByAttribute()
     */
    public static function findOrSetOneByAttribute($attribute, $duration = null, $dependency = null)
    {
        $data = static::instance()->getOrSetModelCacheByAttribute($attribute, function ($behavior) use ($attribute) {
            /* @var $behavior \devzyj\behaviors\ActiveCacheBehavior */
            $condition = $behavior->ensureActiveKeyAttribute($attribute);
            
            /* @var $model \yii\db\ActiveRecord */
            Yii::debug("Cache value does not exist, querying the data from the database.", __METHOD__);
            $model = static::findOne($condition);
            return $model ? $model->getActiveCacheValue() : false;
        }, $duration, $dependency);
        
        if ($data === false) {
            return null;
        }
        
        $model = Yii::createObject(static::className());
        static::populateRecord($model, $data);
        $model->afterFind();
        return $model;
    }
}