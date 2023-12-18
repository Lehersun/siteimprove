<?php

namespace Pixelant\PxaSiteimprove\Service;

/*
 * This file is part of the TYPO3 CMS project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */

use Pixelant\PxaSiteimprove\Utility\CompatibilityUtility;
use TYPO3\CMS\Core\Configuration\ExtensionConfiguration;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Service class to get the settings from Extension Manager
 */
class ExtensionManagerConfigurationService
{
    /**
     * Parse settings and return it as an array
     *
     * @return array unserialized extconf settings
     */
    public static function getSettings()
    {
        $settings = [];
        if (CompatibilityUtility::typo3VersionIsLessThan('9.0')) {
            // @extensionScannerIgnoreLine
            $settingsString = $GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['pxa_siteimprove'];
            if (isset($settingsString)) {
                $settings = unserialize($settingsString);
            }
        } else {
            $settings = GeneralUtility::makeInstance(ExtensionConfiguration::class)->get('pxa_siteimprove');
        }

        if (!is_array($settings)) {
            $settings = [];
        }


        return $settings;
    }
}
