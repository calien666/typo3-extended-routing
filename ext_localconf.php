<?php

(static function () {
    $GLOBALS['TYPO3_CONF_VARS']['SYS']['routing']['aspects']['PersistedSanitizedPatternMapper']
        = \Calien\ExtendedRouting\Routing\Aspect\PersistedSanitizedPatternMapper::class;
    $GLOBALS['TYPO3_CONF_VARS']['SYS']['routing']['aspects']['DateTimeMapper']
        = \Calien\ExtendedRouting\Routing\Aspect\DateTimeMapper::class;
    $GLOBALS['TYPO3_CONF_VARS']['SYS']['routing']['aspects']['PersistedNullableAliasMapper']
        = \Calien\ExtendedRouting\Routing\Aspect\PersistedNullableAliasMapper::class;
    $GLOBALS['TYPO3_CONF_VARS']['SYS']['routing']['aspects']['PersistedDisabledAliasMapper']
        = \Calien\ExtendedRouting\Routing\Aspect\PersistedDisabledAliasMapper::class;
})();