<?php
/**
 * Markus Hofmann
 * 03.11.21 12:48
 * persisted_sanitized_routing
 */
$EM_CONF['extended_routing'] = [
    'title' => 'Extended routing mappers',
    'description' => 'Adds extended routing mappers to the routing feature for TYPO3',
    'category' => 'plugin',
    'author' => 'Markus Hofmann',
    'author_email' => 'typo3@calien.de',
    'state' => 'beta',
    'clearCacheOnLoad' => 0,
    'version' => '1.0.0',
    'constraints' => [
        'depends' => [
            'typo3' => '10.4.0-11.5.99',
        ],
        'conflicts' => [],
        'suggests' => [],
    ],
    'autoload' => [
        'PSR-4' => [
            'Calien\\ExtendedRouting\\' => 'Classes',
        ],
    ],
];
