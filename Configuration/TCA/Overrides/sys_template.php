<?php
defined('TYPO3_MODE') or die();

if (\Pixelant\PxaSiteimprove\Utility\CompatibilityUtility::typo3VersionIsGreaterThanOrEqualTo('8.0')) {
    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addStaticFile(
        'pxa_siteimprove',
        'Configuration/TypoScript/Deeplinking',
        'Siteimprove Deeplinking Tags'
    );
}

if (\Pixelant\PxaSiteimprove\Utility\CompatibilityUtility::getApplicationContext()->isDevelopment()) {
    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addStaticFile(
        'pxa_siteimprove',
        'Configuration/TypoScript/DeeplinkingDevelopment',
        'Siteimprove Deeplinking Development Tag'
    );
}

