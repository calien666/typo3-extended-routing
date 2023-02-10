<?php

declare(strict_types=1);

namespace Calien\ExtendedRouting\Routing\Aspect;

use TYPO3\CMS\Core\Routing\Aspect\StaticMappableAspectInterface;
use TYPO3\CMS\Core\Site\SiteLanguageAwareInterface;
use TYPO3\CMS\Core\Site\SiteLanguageAwareTrait;

class DateTimeMapper implements StaticMappableAspectInterface, SiteLanguageAwareInterface
{
    use SiteLanguageAwareTrait;

    /**
     * @var array
     */
    protected array $settings = [];
    /**
     * @var string
     */
    protected $dateFormat = '';
    /**
     * @var array
     */
    protected $localeFormat;

    /**
     * DateTimeMapper constructor.
     * @param array $settings
     */
    public function __construct(array $settings)
    {
        $this->settings = $settings;
        $this->dateFormat = $settings['format'] ?? 'Y-m-d';
        $this->localeFormat = $settings['localeFormat'] ?? [];
    }

    /**
     * @inheritDoc
     */
    public function generate(string $value): ?string
    {
        $format = $this->retrieveLocaleFormat() ?? $this->dateFormat;
        $formatted = date($format, (int)$value);

        return $formatted ?? null;
    }

    public function resolve(string $value): ?string
    {
        $format = $this->retrieveLocaleFormat() ?? $this->dateFormat;
        $date = \DateTime::createFromFormat($format, $value);
        return $date ? $date->format('U') : null;
    }
    /**
     * Fetches the map of with the matching locale.
     *
     * @return string|null
     */
    protected function retrieveLocaleFormat(): ?string
    {
        $locale = $this->siteLanguage->getLocale();
        foreach ($this->localeFormat as $item) {
            $pattern = '#^' . $item['locale'] . '#i';
            if (preg_match($pattern, (string)$locale)) {
                return $item['format'];
            }
        }
        return null;
    }
}
