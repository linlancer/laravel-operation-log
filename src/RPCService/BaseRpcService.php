<?php
/**
 * Created by PhpStorm.
 * User: $_s
 * Date: 2020/6/30
 * Time: 20:44
 */

namespace LinLancer\Laravel\RPCService;


use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;
use Reprover\LaravelYar\YarService;

class BaseRpcService extends YarService
{
    const TABLE_MAPPING = [];

    const TYPE_RAW = 'raw';

    const TYPE_IN_RAW = 'InRaw';

    /**
     * 调用指定方法
     * @param array $data
     * @return array|mixed
     */
    public function point($data = [])
    {
        $funcName = array_shift($data);
        if (method_exists($this, $funcName))
            return call_user_func_array([$this, $funcName], [$data]);
        return [];
    }

    /**
     * @param array $data
     * @return mixed
     * @throws \Exception
     */
    public function get($data = [])
    {
        return $this->rpcGet(...array_values($data));
    }

    /**
     * @param array $data
     * @return array
     * @throws \Exception
     */
    public function getByPage($data = [])
    {
        return $this->rpcGetByPage(...array_values($data));
    }

    /**
     * @param string $table
     * @param        $field
     * @param $condition
     * @param bool   $byPage
     * @return mixed
     * @throws \Exception
     */
    protected function rpcGet(string $table, $field, $condition, $byPage = false)
    {
        if (!isset(self::TABLE_MAPPING[$table]))
            throw new \Exception([400, '不支持此方法']);
        /**
         * @var Model $model
         */
        $class = self::TABLE_MAPPING[$table];
        $model = new $class;

        $builder = $model->newQuery();

        $builder = $this->parseWhere($condition, $builder);

        return $byPage ? $builder : $builder->get()->toArray();
    }

    /**
     * @param string $table
     * @param        $field
     * @param $condition
     * @param        $page
     * @param        $pageSize
     * @return array
     * @throws \Exception
     */
    protected function rpcGetByPage(string $table, $field, $condition, $page, $pageSize)
    {
        $builder = $this->rpcGet($table, $field, $condition, true);
        return $this->builderToPaginator($builder, $field, $page, $pageSize);
    }

    protected function builderToPaginator(Builder $builder, $field, $page, $pageSize)
    {
        $total = $builder->toBase()->getCountForPagination();
        $results = $builder->forPage($page, $pageSize)->get($field) ? : collect();
        $paginator =  new LengthAwarePaginator($results, $total, $pageSize, $page);
        return $paginator->toArray();
    }

    protected function parseWhere($params, Builder $builder)
    {
        $params = unserialize(base64_decode($params));
        $baseQuery = $builder->getQuery();
        foreach ($params as $key => $param) {
            switch ($key) {
                case 'wheres':
                    $param = $this->slashWheres($param);
                    $baseQuery->mergeWheres($param, $params['bindings']);
                    break;
                case 'columns':
                    !empty($param) && $baseQuery->columns = $param;
                    break;
                case 'groups':
                    !empty($param) && $baseQuery->groupBy($param);
                    break;
                case 'bindinds':
                    break;
                default:
                    $baseQuery->$key = $param;
                    break;
            }

        }
        $builder->setQuery($baseQuery);
        return $builder;
    }

    private function slashWheres($wheres)
    {
        foreach ($wheres as &$where) {
            switch ($where['type']) {
                case self::TYPE_RAW:
                    $fieldStr = $where['sql'];
                    if (stripos($fieldStr, '`') !== false) {
                        $reg = '/`\w+`\./i';
                        $fieldStr = preg_replace($reg, '', $where['sql']);
                        $where['sql'] = $fieldStr;
                    }
                    break;
                case self::TYPE_IN_RAW:
                    $field = $where['column'];
                    if (stripos($field, '.') !== false) {
                        $reg = '/\w+\./i';
                        $field = preg_replace($reg, '', $field);
                        $where['column'] = $field;
                    }
                    break;
                default:
                    break;
            }
            if (stripos($where['column'], '#') !== false) {
                $where['column'] = str_replace('#', '.', $where['column']);
            }
        }
        return $wheres;
    }
}