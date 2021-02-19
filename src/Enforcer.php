<?php

namespace teamones\casbin;

use Casbin\Enforcer as BaseEnforcer;
use Casbin\Model\Model;
use InvalidArgumentException;
use teamones\casbin\adapters\DatabaseAdapter;

/**
 * Enforcer
 */
class Enforcer
{

    /**
     * @var null
     */
    protected static $_instance = null;

    /**
     * @param string $type
     * @return \Casbin\Enforcer|null
     * @throws \Casbin\Exceptions\CasbinException
     */
    public static function instance($type = 'default')
    {
        if (empty(self::$_instance)) {

            $config = config('casbin', []);
            if (!isset($config[$type])) {
                throw new \RuntimeException("Casbin {$type} config not found.");
            }

            // 加载casbin model 配置
            $model = new Model();
            $configType = $config[$type]['model']['config_type'];
            if ('file' == $configType) {
                $model->loadModel($config[$type]['model']['config_file_path']);
            } elseif ('text' == $configType) {
                $model->loadModelFromText($config[$type]['model']['config_text']);
            }

            // 实例化casbin adapter 适配器
            if (empty($config['adapter_rule_model']) && !class_exists($config['adapter_rule_model'])) {
                throw new InvalidArgumentException("Enforcer adapter is not defined.");
            }
            $ruleModel = new $config['adapter_rule_model']();
            $adapter = new DatabaseAdapter($ruleModel);
            
            self::$_instance = new BaseEnforcer($model, $adapter, false);
        }
        return self::$_instance;
    }

    /**
     * @param $name
     * @param $arguments
     * @return mixed
     * @throws \Casbin\Exceptions\CasbinException
     */
    public static function __callStatic($name, $arguments)
    {
        return static::instance('default')->{$name}(... $arguments);
    }

}