<?php

declare(strict_types=1);

namespace Calien\ExtendedRouting\Routing\Aspect;

use TYPO3\CMS\Core\Database\Connection;
use TYPO3\CMS\Core\Routing\Aspect\PersistedAliasMapper;

/**
 * Allows creating speaking URL parts even if records
 * are disabled by enablecolumns in TCA
 */
class PersistedDisabledAliasMapper extends PersistedAliasMapper
{
    protected function findByIdentifier(string $value): ?array
    {
        $result = parent::findByIdentifier($value);
        if (is_null($result)) {
            $queryBuilder = $this->createQueryBuilder();
            $queryBuilder->getRestrictions()->removeAll();
            $result = $queryBuilder
                ->select(...$this->persistenceFieldNames)
                ->where($queryBuilder->expr()->eq(
                    'uid',
                    $queryBuilder->createNamedParameter($value, Connection::PARAM_INT)
                ))
                ->execute()
                ->fetch();
        }
        return $result !== false ? $result : null;
    }
}
