<?php

namespace OpenCore\Utils;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Doctrine\DBAL\Query\QueryBuilder;
use Spot\Query;

class UrlQuery {

    const MAX_LIMIT = 100;
    const MAX_OFFSET = 1e8;

    public static function parseOrderBy(ServerRequestInterface $request, QueryBuilder $queryBuilder, array $columns, string $defaultColumn) {
        $queryParams = $request->getQueryParams();
        $value = isset($queryParams['sort']) ? $queryParams['sort'] : '+' . $defaultColumn;
        $dbName = null;
        $asc = true;
        $matches = null;
        if ($value && preg_match('/^(\+|\-)(\w+)$/', $value, $matches)) {
            $columnName = $matches[2];
            if (isset($columns[$columnName])) {
                $dbName = $columns[$columnName];
                $asc = ($matches[1] === '+');
            }
        }
        if (!$dbName) {
            $dbName = $columns[$defaultColumn];
        }
        return $queryBuilder->addOrderBy($dbName, $asc ? 'ASC' : 'DESC');
    }

    public static function pagenation(ServerRequestInterface $request, ResponseInterface $response, Query $collecton) {
        $total = count($collecton);
        self::parsePagenation($request, $collecton->builder());

        return [$response->withHeader('X-Total-Count', $total), $collecton];
    }

    public static function parsePagenation(ServerRequestInterface $request, QueryBuilder $queryBuilder) {
        $queryParams = $request->getQueryParams();
        $offset = isset($queryParams['offset']) ? (int) $queryParams['offset'] : 0;
        $limit = isset($queryParams['limit']) ? (int) $queryParams['limit'] : 0;
        if ($offset > 0 && $offset < self::MAX_OFFSET) {
            $queryBuilder->setFirstResult($offset);
        }
        if ($limit <= 0) {
            $limit = self::MAX_LIMIT;
        }
        $queryBuilder->setMaxResults($limit);
    }

}
