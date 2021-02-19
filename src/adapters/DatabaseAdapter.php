<?php

declare(strict_types=1);

namespace teamones\casbin\adapters;

use Casbin\Persist\Adapter as AdapterContract;
use Casbin\Model\Model;
use Casbin\Persist\AdapterHelper;

/**
 * DatabaseAdapter.
 */
class DatabaseAdapter implements AdapterContract
{

    use AdapterHelper;

    /**
     * @var \think\Model
     */
    protected $eloquent;

    /**
     * DatabaseAdapter constructor.
     * @param $ruleModel
     */
    public function __construct($ruleModel)
    {
        $this->eloquent = $ruleModel;
    }

    /**
     * savePolicyLine function.
     *
     * @param string $ptype
     * @param array $rule
     */
    public function savePolicyLine(string $ptype, array $rule): void
    {
        $col['ptype'] = $ptype;
        foreach ($rule as $key => $value) {
            $col['v' . strval($key)] = $value;
        }

        $this->eloquent->create($col);
    }

    /**
     * loads all policy rules from the storage.
     * @param Model $model
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function loadPolicy(Model $model): void
    {
        $rows = $this->eloquent->field('ptype,v0,v1,v2,v3,v4,v5')
            ->select()
            ->toArray();

        foreach ($rows as $row) {
            $line = implode(', ', array_filter($row, function ($val) {
                return '' != $val && !is_null($val);
            }));
            $this->loadPolicyLine(trim($line), $model);
        }
    }

    /**
     * saves all policy rules to the storage.
     *
     * @param Model $model
     */
    public function savePolicy(Model $model): void
    {
        foreach ($model['p'] as $ptype => $ast) {
            foreach ($ast->policy as $rule) {
                $this->savePolicyLine($ptype, $rule);
            }
        }

        foreach ($model['g'] as $ptype => $ast) {
            foreach ($ast->policy as $rule) {
                $this->savePolicyLine($ptype, $rule);
            }
        }
    }

    /**
     * adds a policy rule to the storage.
     * This is part of the Auto-Save feature.
     *
     * @param string $sec
     * @param string $ptype
     * @param array $rule
     */
    public function addPolicy(string $sec, string $ptype, array $rule): void
    {
        $this->savePolicyLine($ptype, $rule);
    }

    /**
     * This is part of the Auto-Save feature.
     * @param string $sec
     * @param string $ptype
     * @param array $rule
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function removePolicy(string $sec, string $ptype, array $rule): void
    {
        $instance = $this->eloquent->where('ptype', $ptype);

        foreach ($rule as $key => $value) {
            $instance->where('v' . strval($key), $value);
        }

        $modelRows = $instance->select()->toArray();

        foreach ($modelRows as $model) {
            $this->eloquent->where('id', $model['id'])->delete();
        }
    }

    /**
     * RemoveFilteredPolicy removes policy rules that match the filter from the storage.
     * This is part of the Auto-Save feature.
     *
     * @param string $sec
     * @param string $ptype
     * @param int $fieldIndex
     * @param string ...$fieldValues
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function removeFilteredPolicy(string $sec, string $ptype, int $fieldIndex, string ...$fieldValues): void
    {
        $instance = $this->eloquent->where('ptype', $ptype);
        foreach (range(0, 5) as $value) {
            if ($fieldIndex <= $value && $value < $fieldIndex + count($fieldValues)) {
                if ('' != $fieldValues[$value - $fieldIndex]) {
                    $instance->where('v' . strval($value), $fieldValues[$value - $fieldIndex]);
                }
            }
        }

        $modelRows = $instance->select()->toArray();

        foreach ($modelRows as $model) {
            $this->eloquent->where('id', $model['id'])->delete();
        }
    }

}