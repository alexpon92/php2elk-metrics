<?php

declare(strict_types=1);

namespace Php2ElkMetrics\Metrics;

use DateTime;

abstract class BaseMetric
{
    /**
     * @var DateTime|null
     */
    protected $time;

    /**
     * Unique metric name
     * @return string
     */
    abstract public static function getName(): string;

    /**
     * Method to convert metric contents to array
     *
     * @return array
     */
    abstract public function arraySerialize(): array;

    /**
     * @return int|null
     */
    final public function getTimestamp(): ?int
    {
        if ($this->time && $this->time instanceof DateTime) {
            return $this->time->getTimestamp();
        }

        return null;
    }

    /**
     * @return DateTime|null
     */
    public function getTime(): ?DateTime
    {
        return $this->time;
    }
}