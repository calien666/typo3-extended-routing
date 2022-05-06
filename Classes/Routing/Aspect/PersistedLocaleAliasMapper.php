<?php

/**
 * Markus Hofmann
 * 06.05.22 09:46
 * extended-routing
 */

declare(strict_types=1);

namespace Calien\ExtendedRouting\Routing\Aspect;

use TYPO3\CMS\Core\Database\Connection;
use TYPO3\CMS\Core\Routing\Aspect\PersistedAliasMapper;
use TYPO3\CMS\Core\Site\SiteLanguageAwareTrait;
/**
 * Classic usage when using a "URL segment" (e.g. slug) field within a database table.
 *
 * Example:
 *   routeEnhancers:
 *     EventsPlugin:
 *       type: Extbase
 *       extension: Events2
 *       plugin: Pi1
 *       routes:
 *         - { routePath: '/events/{event}', _controller: 'Event::detail', _arguments: {'event': 'event_name'}}
 *       defaultController: 'Events2::list'
 *       aspects:
 *         event:
 *           type: PersistedAliasMapper
 *           tableName: 'tx_events2_domain_model_event'
 *           routeFieldName: 'path_segment'
 *           routeValuePrefix: '/'
 */
class PersistedLocaleAliasMapper extends PersistedAliasMapper
{
    use SiteLanguageAwareTrait;

    /**
     * @var array
     */
    protected $localeMap = [];

    /**
     * @param array $settings
     * @throws \InvalidArgumentException
     */
    public function __construct(array $settings)
    {
        $tableName = $settings['tableName'] ?? null;
        $routeFieldName = $settings['routeFieldName'] ?? null;
        $routeValuePrefix = $settings['routeValuePrefix'] ?? '';

        $localeMap = $settings['localeMap'] ?? [];


        if (!is_string($tableName)) {
            throw new \InvalidArgumentException(
                'tableName must be string',
                1537277133
            );
        }
        if (!is_string($routeFieldName)) {
            throw new \InvalidArgumentException(
                'routeFieldName name must be string',
                1537277134
            );
        }
        if (!is_string($routeValuePrefix) || strlen($routeValuePrefix) > 1) {
            throw new \InvalidArgumentException(
                '$routeValuePrefix must be string with one character',
                1537277136
            );
        }

        $this->settings = $settings;
        $this->tableName = $tableName;
        $this->routeFieldName = $routeFieldName;
        $this->routeValuePrefix = $routeValuePrefix;
        $this->languageFieldName = $GLOBALS['TCA'][$this->tableName]['ctrl']['languageField'] ?? null;
        $this->languageParentFieldName = $GLOBALS['TCA'][$this->tableName]['ctrl']['transOrigPointerField'] ?? null;
        $this->persistenceFieldNames = $this->buildPersistenceFieldNames();
        $this->slugUniqueInSite = $this->isSlugUniqueInSite($this->tableName, $this->routeFieldName);
        $this->localeMap = $localeMap;

    }

    /**
     * {@inheritdoc}
     */
    public function resolve(string $value): ?string
    {
        $this->modify();

        $value = $this->routeValuePrefix . $this->purgeRouteValuePrefix($value);
        $result = $this->findByRouteFieldValue($value);

        if ($result[$this->languageParentFieldName] ?? null > 0) {
            return (string)$result[$this->languageParentFieldName];
        }
        if (isset($result['uid'])) {
            return (string)$result['uid'];
        }
        return null;
    }

    /**
     * @return string[]
     */
    protected function buildPersistenceFieldNames(): array
    {

        return array_filter([
            'uid',
            'pid',
            $this->routeFieldName,
            $this->languageFieldName,
            $this->languageParentFieldName,
        ]);
    }

    /**
     * @param string|null $value
     * @return string
     */
    protected function purgeRouteValuePrefix(?string $value): ?string
    {
        if (empty($this->routeValuePrefix) || $value === null) {
            return $value;
        }
        return ltrim($value, $this->routeValuePrefix);
    }

    protected function findByIdentifier(string $value): ?array
    {

        $locale = $this->siteLanguage->getLocale();
        foreach ($this->localeMap as $item) {
            $pattern = '#^' . $item['locale'] . '#i';
            if (preg_match($pattern, $locale)) {
                $persistenceFieldNames1 = array('uid', 'pid', (string)$item['field'] ?? null);
            }
        }
        if (!isset($persistenceFieldNames1)) {
            $persistenceFieldNames1 = $this->persistenceFieldNames;
        }
        //\TYPO3\CMS\Extbase\Utility\DebuggerUtility::var_dump($locale);


        $queryBuilder = $this->createQueryBuilder();
        $result = $queryBuilder
            ->select(...$persistenceFieldNames1)
            //->select(...$this->persistenceFieldNames)
            ->where($queryBuilder->expr()->eq(
                'uid',
                $queryBuilder->createNamedParameter($value, \PDO::PARAM_INT)
            ))
            ->execute()
            ->fetch();

        if ($locale == 'en_GB.UTF-8' || $locale == 'en_US.UTF-8') {
            $result['path_segment'] = $result['path_segment_en'];
        }

        return $result !== false ? $result : null;
    }

    protected function findByRouteFieldValue(string $value): ?array
    {
        $languageAware = $this->languageFieldName !== null && $this->languageParentFieldName !== null;

        $queryBuilder = $this->createQueryBuilder();
        $constraints = [
            $queryBuilder->expr()->eq(
                $this->routeFieldName,
                $queryBuilder->createNamedParameter($value, \PDO::PARAM_STR)
            ),
        ];

        $languageIds = null;
        if ($languageAware) {
            $languageIds = $this->resolveAllRelevantLanguageIds();
            $constraints[] = $queryBuilder->expr()->in(
                $this->languageFieldName,
                $queryBuilder->createNamedParameter($languageIds, Connection::PARAM_INT_ARRAY)
            );
        }


        $locale = $this->siteLanguage->getLocale();
        foreach ($this->localeMap as $item) {
            $pattern = '#^' . $item['locale'] . '#i';
            if (preg_match($pattern, $locale)) {
//                \TYPO3\CMS\Extbase\Utility\DebuggerUtility::var_dump($item['field']);
                $persistenceFieldNames1 = array('uid', 'pid', (string)$item['field'] ?? null);
                //$this->persistenceFieldNames[2] = (string)$item['field'] ?? null;
                //\TYPO3\CMS\Extbase\Utility\DebuggerUtility::var_dump($persistenceFieldNames1);
                //\TYPO3\CMS\Extbase\Utility\DebuggerUtility::var_dump((string)$item['field']);
                //$localizedFieldName = (string)$item['field'];
                //$this->routeFieldPattern = str_replace($this->routeFieldResultNames[0], $localizedFieldName, $this->routeFieldPattern);
                //$this->routeFieldResult = str_replace($this->routeFieldResultNames[0], $localizedFieldName, $this->routeFieldResult);
                //$this->routeFieldResultNames[0] = $localizedFieldName;
            }
        }


        $results = $queryBuilder
            ->select(...$this->persistenceFieldNames)
            //->select(...$persistenceFieldNames1)
            ->where(...$constraints)
            ->execute()
            ->fetchAll();

        // limit results to be contained in rootPageId of current Site
        // (which is defining the route configuration currently being processed)
        if ($this->slugUniqueInSite) {
            $results = array_values($this->filterContainedInSite($results));
        }
        // return first result record in case table is not language aware
        if (!$languageAware) {
            return $results[0] ?? null;
        }
        // post-process language fallbacks
        return $this->resolveLanguageFallback($results, $this->languageFieldName, $languageIds);
    }

    protected function createQueryBuilder(): QueryBuilder
    {
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)
            ->getQueryBuilderForTable($this->tableName)
            ->from($this->tableName);
        $queryBuilder->setRestrictions(
            GeneralUtility::makeInstance(FrontendRestrictionContainer::class, $this->context)
        );
        // Frontend Groups are not available at this time (initialized via TSFE->determineId)
        // So this must be excluded to allow access restricted records
        $queryBuilder->getRestrictions()->removeByType(FrontendGroupRestriction::class);
        return $queryBuilder;
    }

    /**
     * @param array|null $record
     * @return array|null
     */
    protected function resolveOverlay(?array $record): ?array
    {
        $languageId = $this->siteLanguage->getLanguageId();
        if ($record === null || $languageId === 0) {
            return $record;
        }

        $pageRepository = $this->createPageRepository();
        if ($this->tableName === 'pages') {
            return $pageRepository->getPageOverlay($record, $languageId);
        }
        return $pageRepository
            ->getRecordOverlay($this->tableName, $record, $languageId) ?: null;
    }

    /**
     * @return PageRepository
     */
    protected function createPageRepository(): PageRepository
    {
        $context = clone GeneralUtility::makeInstance(Context::class);
        $context->setAspect(
            'language',
            LanguageAspectFactory::createFromSiteLanguage($this->siteLanguage)
        );
        return GeneralUtility::makeInstance(
            PageRepository::class,
            $context
        );
    }


    /**
     * modify
     * @return void
     */
    protected function modify(): void
    {
        $locale = $this->siteLanguage->getLocale();
        foreach ($this->localeMap as $item) {
            $pattern = '#^' . $item['locale'] . '#i';
            if (preg_match($pattern, $locale)) {
                $this->routeFieldName = (string)$item['field'];
                $localizedFieldName = (string)$item['field'];
                $this->routeFieldPattern = str_replace($this->routeFieldResultNames[0], $localizedFieldName, $this->routeFieldPattern);
                $this->routeFieldResult = str_replace($this->routeFieldResultNames[0], $localizedFieldName, $this->routeFieldResult);
                $this->routeFieldResultNames[0] = $localizedFieldName;
            }
        }

    }
}