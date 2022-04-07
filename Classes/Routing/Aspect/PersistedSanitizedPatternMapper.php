<?php

declare(strict_types=1);

namespace Calien\ExtendedRouting\Routing\Aspect;

use TYPO3\CMS\Core\DataHandling\SlugHelper;
use TYPO3\CMS\Core\Routing\Aspect\PersistedPatternMapper;
use TYPO3\CMS\Core\Site\SiteLanguageAwareTrait;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class PersistedSanitizedPatternMapper extends PersistedPatternMapper
{
    use SiteLanguageAwareTrait;

    protected const PATTERN_RESULT = '#\{(?P<fieldName>[^}]+?)(?P<sanitized>\|sanitized)?\}#';

    /**
     * @var string[]
     */
    protected $routeFieldResultSanitized;

    /**
     * @var SlugHelper|null
     */
    protected $slugHelper;

    /**
     * @var array
     */
    protected $localeMap = [];

    /**
     * @param array $settings
     */
    public function __construct(
        array $settings
    )
    {
        $tableName = $settings['tableName'] ?? null;
        $routeFieldPattern = $settings['routeFieldPattern'] ?? null;
        $routeFieldResult = $settings['routeFieldResult'] ?? null;
        $localeMap = $settings['localeMap'] ?? [];

        if (!is_string($tableName)) {
            throw new \InvalidArgumentException('tableName must be string', 1633614787090);
        }
        if (!is_string($routeFieldPattern)) {
            throw new \InvalidArgumentException('routeFieldPattern must be string', 1633614790631);
        }
        if (!is_string($routeFieldResult)) {
            throw new \InvalidArgumentException('routeFieldResult must be string', 1633614793784);
        }
        if (!preg_match_all(static::PATTERN_RESULT, $routeFieldResult, $routeFieldResultNames)) {
            throw new \InvalidArgumentException(
                'routeFieldResult must contain substitutable field names',
                1633614799805
            );
        }
        $this->settings = $settings;
        $this->tableName = $tableName;
        $this->routeFieldPattern = $routeFieldPattern;
        $this->routeFieldResult = $routeFieldResult;
        $this->routeFieldResultNames = $routeFieldResultNames['fieldName'] ?? [];
        $this->languageFieldName = $GLOBALS['TCA'][$this->tableName]['ctrl']['languageField'] ?? null;
        $this->languageParentFieldName = $GLOBALS['TCA'][$this->tableName]['ctrl']['transOrigPointerField'] ?? null;
        $this->slugUniqueInSite = $this->hasSlugUniqueInSite($this->tableName, ...$this->routeFieldResultNames);
        $this->routeFieldResultSanitized = $routeFieldResultNames['sanitized'] ?? [];
        $this->localeMap = $localeMap;
    }

    /**
     * {@inheritdoc}
     */
    public function resolve(string $value): ?string
    {
        $this->modify();
        if (!preg_match('#' . $this->routeFieldPattern . '#', $value, $matches)) {
            return null;
        }
        $values = $this->filterNamesKeys($matches);
        $result = $this->findByRouteFieldValues($values);
        if ($result[$this->languageParentFieldName] ?? null > 0) {
            return (string)$result[$this->languageParentFieldName];
        }
        if (isset($result['uid'])) {
            return (string)$result['uid'];
        }
        return null;
    }

    /**
     * @param array|null $result
     * @return string|null
     * @throws \InvalidArgumentException
     */
    protected function createRouteResult(?array $result): ?string
    {
        if ($result === null) {
            return $result;
        }
        $this->modify();
        $substitutes = [];
        foreach ($this->routeFieldResultNames as $i => $fieldName) {
            if (!isset($result[$fieldName])) {
                return null;
            }
            if ($this->routeFieldResultSanitized[$i] !== '') {
                $result[$fieldName] = $this->getSlugHelper()->sanitize((string)$result[$fieldName]);
                $routeFieldName = '{' . $fieldName . '|sanitized}';
            } else {
                $routeFieldName = '{' . $fieldName . '}';
            }
            $substitutes[$routeFieldName] = $result[$fieldName];
        }
        return str_replace(
            array_keys($substitutes),
            array_values($substitutes),
            $this->routeFieldResult
        );
    }

    /**
     * @return SlugHelper
     */
    protected function getSlugHelper(): SlugHelper
    {
        if ($this->slugHelper === null) {
            $this->slugHelper = GeneralUtility::makeInstance(
                SlugHelper::class,
                $this->tableName,
                '',
                []
            );
        }

        return $this->slugHelper;
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
                $localizedFieldName = (string)$item['field'];
                $this->routeFieldPattern = str_replace($this->routeFieldResultNames[0], $localizedFieldName, $this->routeFieldPattern);
                $this->routeFieldResult = str_replace($this->routeFieldResultNames[0], $localizedFieldName, $this->routeFieldResult);
                $this->routeFieldResultNames[0] = $localizedFieldName;
            }
        }

    }
}