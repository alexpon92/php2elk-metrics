<?php
declare(strict_types=1);

namespace Php2ElkMetrics\MetricsProducer\Responses;

class Response
{
    /**
     * @var string
     */
    private $index;

    /**
     * @var string
     */
    private $docId;

    /**
     * Response constructor.
     *
     * @param string $index
     * @param string $docId
     */
    public function __construct(string $index, string $docId)
    {
        $this->index = $index;
        $this->docId = $docId;
    }

    /**
     * @return string
     */
    public function getIndex(): string
    {
        return $this->index;
    }

    /**
     * @return string
     */
    public function getDocId(): string
    {
        return $this->docId;
    }
}