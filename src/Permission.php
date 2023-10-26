<?php

namespace teamones\casbin;

use teamones\casbin\watcher\RedisWatcher;

class Permission
{
    /** @var \Casbin\Enforcer[] $_manager */
    protected static array $_manager = [];

    /**
     * @param string|null $driver
     * @return \Casbin\Enforcer
     * @throws CasbinException
     */
    public static function driver(?string $driver = 'default'): \Casbin\Enforcer
    {
        if (isset(static::$_manager[$driver])) {
            return static::$_manager[$driver];
        }

        static::$_manager[$driver] = Enforcer::instance($driver);

        $watcher = new RedisWatcher(config('redis.default'), $driver);
        static::$_manager[$driver]->setWatcher($watcher);
        $watcher->setUpdateCallback(function () use ($driver) {
            static::$_manager[$driver]->loadPolicy();
        });
        return static::$_manager[$driver];
    }

    /**
     * @desc: 获取所有驱动
     * @return Enforcer[]
     */
    public static function getAllDriver(): array
    {
        return static::$_manager;
    }


    /**
     * @desc: 静态调用
     * @param string $method
     * @param $arguments
     * @return mixed
     * @throws CasbinException
     * @author Tinywan(ShaoBo Wan)
     */
    public static function __callStatic(string $method, $arguments)
    {
        return self::driver()->{$method}(...$arguments);
    }
}