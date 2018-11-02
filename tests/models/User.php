<?php
/**
 * @link https://github.com/devzyj/yii2-cache-behavior
 * @copyright Copyright (c) 2018 Zhang Yan Jiong
 * @license http://opensource.org/licenses/BSD-3-Clause
 */
namespace devzyj\behaviors\tests\models;

/**
 * User class.
 * 
 * @property int $id ID
 * @property int $id2 ID2
 * @property string $name Name
 * 
 * @author ZhangYanJiong <zhangyanjiong@163.com>
 * @since 1.0
 */
class User extends \yii\db\ActiveRecord
{
    use \devzyj\behaviors\ActiveCacheBehaviorTrait;
    
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'devzyj_test_user';
    }
    
    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            [
                'class' => 'devzyj\behaviors\ActiveCacheBehavior',
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'id2'], 'integer'],
            [['name'], 'string'],
        ];
    }
}