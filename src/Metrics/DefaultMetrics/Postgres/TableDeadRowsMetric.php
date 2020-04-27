<?php
declare(strict_types=1);

namespace Php2ElkMetrics\Metrics\DefaultMetrics\Postgres;

use Php2ElkMetrics\Metrics\BaseMetric;
use DateTime;

class TableDeadRowsMetric extends BaseMetric
{
    /**
     * @var string
     */
    private $dbName;

    /**
     * @var string
     */
    private $tableName;

    /**
     * @var float
     */
    private $percent;

    /**
     * TableDeadRows constructor.
     *
     * @param string        $dbName
     * @param string        $tableName
     * @param float         $percent
     * @param DateTime|null $time
     */
    public function __construct(string $dbName, string $tableName, float $percent, ?DateTime $time)
    {
        $this->dbName    = $dbName;
        $this->tableName = $tableName;
        $this->percent   = $percent;
        $this->time      = $time;
    }

    public static function getName(): string
    {
        return 'postgres_table_dead_rows';
    }

    public function arraySerialize(): array
    {
        return [
            'db_name'    => $this->dbName,
            'table_name' => $this->tableName,
            'percent'    => $this->percent
        ];
    }

    /**
     * @return string
     */
    public function getDbName(): string
    {
        return $this->dbName;
    }

    /**
     * @return string
     */
    public function getTableName(): string
    {
        return $this->tableName;
    }

    /**
     * @return float
     */
    public function getPercent(): float
    {
        return $this->percent;
    }
}