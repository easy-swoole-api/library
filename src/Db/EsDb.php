<?php
/**
 * Created by PhpStorm.
 * User: hlh XueSi <1592328848@qq.com>
 * Date: 2023/5/8
 * Time: 12:11 上午
 */
declare(strict_types=1);

namespace EasyApi\Library\Db;

use EasyApi\Db\Query;
use EasySwoole\Mysqli\Client;
use EasySwoole\Mysqli\Config;
use EasySwoole\Mysqli\QueryBuilder;

class EsDb
{
    /**
     * @param ...$args
     *
     * @return EsDb
     * @author: XueSi <1592328848@qq.com>
     * @date  : 2023/5/8 12:12 上午
     */
    public static function factory(...$args)
    {
        return new self(...$args);
    }

    /**
     * @var array
     */
    protected $config;

    /**
     * @var Config
     */
    protected $mysqliConfig;

    /**
     * @var Client
     */
    protected $client;

    /**
     * @var string
     */
    protected $table;

    /**
     * @var Query
     */
    protected $query;

    public function __construct(string $connectionName = 'default')
    {
        $config = config("database.{$connectionName}");
        $this->config = $config;
        $configObject = new Config($config);
        $this->mysqliConfig = $configObject;
        $this->client = new Client($configObject);
    }

    public function setClient(Client $client)
    {
        $this->client = $client;
        return $this;
    }

    /**
     * @return string
     */
    public function getTable()
    {
        return $this->table;
    }

    public function table(string $table)
    {
        $this->table = $table;

        return $this;
    }

    public function limit(int $limit, ?int $offset = null)
    {
        $this->client->queryBuilder()->limit($limit, $offset);

        return $this;
    }

    public function field($fields)
    {
        $this->client->queryBuilder()->fields($fields);

        return $this;
    }

    public function order(...$args)
    {
        $orders = array_shift($args);

        if ($orders) {
            if (is_string($orders)) {
                $orderArr = explode(',', $orders);
                foreach ($orderArr as $item) {
                    $temp = explode(' ', $item);
                    $this->client->queryBuilder()->orderBy($temp[0], $temp[1]);
                    parent::order($temp[0], $temp[1]);
                }
            } else if (is_array($orders)) {
                foreach ($orders as $field => $order) {
                    if (is_numeric($field)) {
                        $field = $order;
                        $order = 'ASC';
                    }
                    $this->client->queryBuilder()->orderBy($field, $order);
                }
            }
        }

        return $this;
    }

    private function handleWhere(array $where)
    {
        foreach ($where as $field => $value) {
            if (is_array($value)) {
                $this->client->queryBuilder()->where($field, ...$value);
            } else {
                $this->client->queryBuilder()->where($field, $value);
            }
        }
    }

    public function where(array $where)
    {
        $this->handleWhere($where);

        return $this;
    }

    public function insert(array $data)
    {
        $this->client->queryBuilder()->insert($this->table, $data);

        return $this->client->execBuilder();
    }

    public function insertAll(array $data)
    {
        $this->client->queryBuilder()->insertAll($this->table, $data);

        return $this->client->execBuilder();
    }

    public function delete(array $where = [], $numRows = null)
    {
        if (!empty($where)) {
            $this->handleWhere($where);
        }

        $this->client->queryBuilder()->delete($this->table, $numRows);

        return $this->client->execBuilder();
    }

    public function update(array $update, array $where = [], $numRows = null)
    {
        if (!empty($where)) {
            $this->handleWhere($where);
        }

        $this->client->queryBuilder()->update($this->table, $update, $numRows);

        return $this->client->execBuilder();
    }

    public function find(array $where = [])
    {
        if (!empty($where)) {
            $this->handleWhere($where);
        }

        $this->client->queryBuilder()->limit(1)->get($this->table);

        $result = $this->client->execBuilder();

        if ($result !== false) {
            return $result[0] ?? false;
        }

        return false;
    }

    public function select(array $where = [], $numRows = null, $columns = null)
    {
        if (!empty($where)) {
            $this->handleWhere($where);
        }

        $this->client->queryBuilder()->get($this->table, $numRows, $columns);

        return $this->client->execBuilder();
    }

    public static function execute(string $sql, string $connection = 'default')
    {
        $esDb = self::factory($connection);
        return $esDb->raw($sql);
    }

    public function raw(string $sql, bool $isRaw = true, array $bindParams = [])
    {
        if ($isRaw) {
            return $this->client->rawQuery($sql);
        } else {
            $this->client->queryBuilder()->raw($sql, $bindParams);
            return $this->client->execBuilder();
        }
    }

    public function rawByBuilder(QueryBuilder $builder, bool $isRaw = true)
    {
        if ($isRaw) {
            return $this->client->rawQuery($builder->getLastQuery());
        } else {
            $this->client->queryBuilder()->raw($builder->getLastPrepareQuery(), $builder->getLastBindParams());
            return $this->client->execBuilder();
        }
    }

    private function startTransaction()
    {
        $builder = new QueryBuilder();
        $builder->startTransaction();

        return $this->client->rawQuery($builder->getLastQuery());
    }

    private function commit()
    {
        $builder = new QueryBuilder();
        $builder->commit();

        return $this->client->rawQuery($builder->getLastQuery());
    }

    private function rollback()
    {
        $builder = new QueryBuilder();
        $builder->rollback();

        return $this->client->rawQuery($builder->getLastQuery());
    }

    public function transaction(callable $callable)
    {
        try {
            $this->startTransaction();
            $result = call_user_func($callable, $this->client);
            $this->commit();
        } catch (\Throwable $throwable) {
            $this->rollback();
            $result = false;
        }

        return $result;
    }
}
