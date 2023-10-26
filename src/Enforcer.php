<?php

namespace teamones\casbin;

use Casbin\Enforcer as BaseEnforcer;
use Casbin\Model\Model;
use InvalidArgumentException;
use teamones\casbin\adapters\DatabaseAdapter;


/**
 * @see \Casbin\Enforcer
 * @mixin Enforcer
 * @method static bool enforce(mixed ...$rvals) 权限检查，输入参数通常是(sub, obj, act)
 * @method static bool addPolicy(mixed ...$params) 当前策略添加授权规则
 * @method static bool addPolicies(mixed ...$params) 当前策略添加授权规则
 * @method static bool hasPolicy(mixed ...$params) 确定是否存在授权规则
 * @method static bool removePolicy(mixed ...$params) 当前策略移除授权规则
 * @method static array getAllRoles() 获取所有角色
 * @method static array getPolicy() 获取所有的角色的授权规则
 * @method static bool updatePolicies(array $oldPolices, array $newPolicies) 更新策略
 * @method static bool removePolicies(array $rules) 删除策略
 * @method static array getRolesForUser(string $name, string ...$domain) 获取用户具有的角色
 * @method static array getUsersForRole(string $name, string ...$domain) 获取具有角色的用户
 * @method static bool hasRoleForUser(string $name, string $role, string ...$domain) 确定用户是否具有角色
 * @method static bool addRoleForUser(string $user, string $role, string ...$domain) 给用户添加角色
 * @method static bool addRolesForUser(string $user, array $roles, string ...$domain)
 * @method static bool addPermissionForUser(string $user, string ...$permission) 赋予权限给某个用户或角色
 * @method static bool addPermissionsForUser(string $user, array ...$permissions) 赋予用户或角色多个权限。 如果用户或角色已经有一个权限，则返回 false (不会受影响)
 * @method static bool deleteRoleForUser(string $user, string $role, string $domain) 删除用户的角色
 * @method static bool deleteUser(string $user) 删除用户
 * @method static bool deleteRolesForUser(string $user, string ...$domain) 删除某个用户的所有角色
 * @method static bool deleteRole(string $role) 删除单个角色
 * @method static bool deletePermission(string ...$permission) 删除权限
 * @method static bool deletePermissionForUser(string $name, string $permission) 删除用户或角色的权限。如果用户或角色没有权限则返回 false(不会受影响)。
 * @method static bool deletePermissionsForUser(string $name) 删除用户或角色的权限。如果用户或角色没有任何权限（也就是不受影响），则返回false。
 * @method static array getPermissionsForUser(string $name) 获取用户或角色的所有权限
 * @method static bool hasPermissionForUser(string $user, string ...$permission) 决定某个用户是否拥有某个权限
 * @method static array getImplicitRolesForUser(string $name, string ...$domain) 获取用户具有的隐式角色
 * @method static array getImplicitPermissionsForUser(string $username, string ...$domain) 获取用户具有的隐式权限
 * @method static array getImplicitUsersForRole(string $name, string ...$domain) 获取具有隐式用户的角色
 * @method static array getImplicitResourcesForUser(string $user, string ...$domain) 获取具有隐式资源的用户
 * @method static array getImplicitUsersForPermission(string ...$permission) 获取隐式用户的权限
 * @method static array getAllUsersByDomain(string $domain) 获取域中的所有用户
 * @method static array getUsersForRoleInDomain(string $name, string $domain) 获取在域内具有传入角色的用户
 * @method static array getRolesForUserInDomain(string $name, string $domain) 获取域中用户具有的所有角色
 * @method static array getPermissionsForUserInDomain(string $name, string $domain) 获取域中用户具有的所有权限
 * @method static bool addRoleForUserInDomain(string $user, string $role, string $domain) 给域中的用户添加角色
 * @method static bool deleteRoleForUserInDomain(string $user, string $role, string $domain) 删除域中用户的角色
 * @method static bool deleteRolesForUserInDomain(string $user, string $domain) 删除域中用户的所有角色
 * @method static bool deleteAllUsersByDomain(string $domain) 删除域中的所有用户
 * @method static bool deleteDomains(string ...$domain) 删除域
 * @method static bool addFunction(string $name, \Closure $func) 添加一个自定义函数
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
            if (empty($config[$type]['adapter']) && empty($config[$type]['adapter']['type']) && empty($config[$type]['adapter']['class']) && !class_exists($config[$type]['adapter']['class'])) {
                throw new InvalidArgumentException("Enforcer adapter is not defined.");
            }

            switch ($config[$type]['adapter']['type']) {
                case 'model':
                    // 使用支持 think-orm 的适配器
                    $ruleModel = new $config[$type]['adapter']['class']();
                    $adapter = new DatabaseAdapter($ruleModel);
                    break;
                case 'adapter':
                    // 使用自定义适配器
                    $adapter = new $config[$type]['adapter']['class']();
                    break;
                default:
                    throw new InvalidArgumentException("Only model and adapter are supported.");
                    break;
            }

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