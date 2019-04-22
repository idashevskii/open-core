<?php

namespace OpenCore\Db;

use Doctrine\DBAL\Logging\SQLLogger;
use OpenCore\Log\LogUtils;
use Monolog\Logger;

class SqlLoggerDebug implements SQLLogger {

    private $logger;
    private $query = null;
    private $start = null;
    private $trace = null;

    public function __construct(Logger $logger) {
        $this->logger = $logger;
    }

    /**
     * {@inheritdoc}
     */
    public function startQuery($sql, array $params = null, array $types = null) {

        $trace = debug_backtrace();
        array_shift($trace); // remove this caller

        $this->query = [
            'sql' => $sql,
            'params' => $params,
            'types' => $types,
            'duration' => 0,
        ];
        $this->trace = '# ' . implode('# ', LogUtils::traceToLines($trace));
        $this->start = microtime(true);
    }

    /**
     * {@inheritdoc}
     */
    public function stopQuery() {
        $query = $this->query;
        $query['duration'] = microtime(true) - $this->start;
        $this->logger->debug(json_encode($query, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . '. Stack Trace: ' . $this->trace);
    }

}
