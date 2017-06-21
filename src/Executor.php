<?php

namespace Caster;

use Caster\DataFormat\DataFormat;
use Caster\Exception\ExecutorException;

class Executor
{
    private $dataFormat;

    public function __construct(DataFormat $dataFormat)
    {
        $this->dataFormat = $dataFormat;
    }

    public function findEx(
        string $queryName,
        array $params = [],
        int $limit = null,
        int $offset = null,
        string $dbType = 'slave'
    ) {
        $baseQuery = $this->dataFormat->getBaseQuery($queryName);
        $schema = get_class($this->dataFormat);
        $query = new Query($baseQuery, $schema);

        $query->setTableName($this->dataFormat->getTableName($params));
        if (!is_null($limit) && $limit > 0) {
            $query->setLimit($limit);
        }
        if (!is_null($offset) && $offset >= 0) {
            $query->setOffset($offset);
        }
        $query->setParameters($params);

        $database = $this->dataFormat->getDatabaseName($params);
        $conn = Connection::get($database, $dbType);
        $queryStr = $query->getEmulateQuery($conn);
        $result = $conn->query($queryStr);

        if ($result === false) {
            $error = $conn->error;
            $errorCode = $conn->errno;
            throw new ExecutorException(
                sprintf('cannot execution query : %s / %s / %s', $queryName, $errorCode, $error)
            );
        }

        if ($result instanceof \mysqli_result) {
            return $result->fetch_all(MYSQLI_ASSOC);
        }

        return (int)$result;
    }

    public function find(
        string $queryName,
        array $params = [],
        int $limit = null,
        int $offset = null,
        bool $useMaster = false
    ) {
        return $this->findEx($queryName, $params, $limit, $offset, $useMaster ? 'master' : 'slave');
    }

    public function findFirst(
        string $queryName,
        array $params = null,
        $useMaster = false
    ) {
        $res = $this->findEx($queryName, $params, 1, 0, $useMaster ? 'master' : 'slave');

        if (is_array($res) && count($res) > 0) {
            return array_shift($res);
        }

        return [];
    }

    public function exec(string $queryName, array $params = [], array $hint = [])
    {
        $params += $hint;

        $baseQuery = $this->dataFormat->getBaseQuery($queryName);
        $schema = get_class($this->dataFormat);
        $query = new Query($baseQuery, $schema);

        $query->setTableName($this->dataFormat->getTableName($params));
        $query->setParameters($params);

        $database = $this->dataFormat->getDatabaseName($params);
        $conn = Connection::get($database, 'master');
        $queryStr = $query->getEmulateQuery($conn);
        $result = $conn->real_query($queryStr);

        if ($result === false) {
            $error = $conn->error;
            $errorCode = $conn->errno;
            throw new ExecutorException(
                sprintf('cannot execution query : %s / %s / %s', $queryName, $errorCode, $error)
            );
        }

        return (int)$conn->affected_rows;
    }
}
