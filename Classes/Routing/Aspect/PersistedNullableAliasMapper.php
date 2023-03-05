<?php

declare(strict_types=1);

namespace Calien\ExtendedRouting\Routing\Aspect;

use InvalidArgumentException;
use TYPO3\CMS\Core\Routing\Aspect\PersistedAliasMapper;

/**
 * Allows Creating speaking URL parts if no uid is set,
 * e.g. news without category
 * Example:
 *   routeEnhancers:
 *     EventsPlugin:
 *       type: Extbase
 *       extension: News
 *       plugin: Pi1
 *       routes:
 *          - routePath: '/{category}/{title}'
 *            _controller: 'News::single'
 *            _arguments:
 *              category: category
 *              title: news
 *       defaultController: 'News::detail'
 *       aspects:
 *         category:
 *           type: PersistedNullableAliasMapper
 *           default: 'no-category'
 *           tableName: 'tx_news_domain_model_category'
 *           routeFieldName: 'path_segment'
 *           routeValuePrefix: '/'
 */
class PersistedNullableAliasMapper extends PersistedAliasMapper
{
    protected ?string $default = null;

    /**
     * @param array<string, mixed> $settings
     * @throws InvalidArgumentException
     */
    public function __construct(array $settings)
    {
        $default = $settings['default'] ?? null;
        if (!is_string($default)) {
            throw new InvalidArgumentException(
                'Default is required and can therefore be not empty',
                1648045415002
            );
        }
        $this->default = $default;
        parent::__construct($settings);
    }

    /**
     * {@inheritdoc}
     */
    public function generate(string $value): ?string
    {
        $result = parent::generate($value);
        if (is_null($result)) {
            return $this->default;
        }
        return $result;
    }

    public function resolve(string $value): ?string
    {
        $result = parent::resolve($value);
        if (is_null($result)) {
            return '0';
        }
        return $result;
    }
}
