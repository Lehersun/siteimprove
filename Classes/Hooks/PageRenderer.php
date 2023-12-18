<?php

namespace Pixelant\PxaSiteimprove\Hooks;

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

use Pixelant\PxaSiteimprove\Service\ExtensionManagerConfigurationService;
use Pixelant\PxaSiteimprove\Utility\CompatibilityUtility;
use TYPO3\CMS\Backend\Controller\PageLayoutController;
use TYPO3\CMS\Core\Authentication\BackendUserAuthentication;
use TYPO3\CMS\Core\Cache\CacheManager;
use TYPO3\CMS\Core\Cache\Exception\NoSuchCacheException;
use TYPO3\CMS\Core\Cache\Frontend\FrontendInterface;
use TYPO3\CMS\Core\Page\PageRenderer as T3PageRenderer;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Lang\LanguageService;
use TYPO3\CMS\Core\Registry;

/**
 * Class which adds the necessary resources for Siteimprove (https://siteimprove.com/).
 */
class PageRenderer implements SingletonInterface
{
    // @codingStandardsIgnoreLine
    const SITEIMPROVE_REGISTRY_KEY = 'pxa_siteimprove';
    const SITEIMPROVE_REGISTRY_TOKEN_KEY = 'token';
    const SITEIMPROVE_TOKEN_GENERATOR = 'https://my2.siteimprove.com/auth/token?cms=TYPO3';

    /**
     * @var Registry
     */
    protected $registry;

    public function __construct()
    {
        $this->registry = GeneralUtility::makeInstance(Registry::class);
    }

    /**
     * Wrapper function called by hook (\TYPO3\CMS\Core\Page\PageRenderer->render-preProcess)
     *
     * @param array $parameters An array of available parameters
     * @param T3PageRenderer $pageRenderer The parent object that triggered this hook
     * @throws \Exception
     */
    public function addResources(array $parameters, T3PageRenderer $pageRenderer)
    {
        $uriPaths = parse_url(GeneralUtility::getIndpEnv('REQUEST_URI'));
        // Add the resources only to the 'Page' module
        if (
            (
                isset($GLOBALS['SOBE'])
                && $GLOBALS['SOBE'] instanceof PageLayoutController
            )
            ||
            (
                isset($uriPaths['path'])
                && $uriPaths['path'] === '/typo3/module/web/layout'
            )
        ) {
            // Check if the user has enabled Siteimprove in the user settings,
            // and it is not disabled for the user group
            if (
                CompatibilityUtility::isEnabledForBEUser()
            ) {
                $settings = ExtensionManagerConfigurationService::getSettings();
                $debugMode = (isset($settings['debugMode'])) ? (bool)$settings['debugMode'] : false;
                $domain = '';
                $url = '';
                $pageId = isset($GLOBALS['SOBE']) ? (int)$GLOBALS['SOBE']->id : (int)GeneralUtility::_GP('id');

                if ($pageId > 0) {
                    $domain = CompatibilityUtility::getFirstDomainInRootline($pageId);

                    if ($domain !== '') {
                        $debugScript = '';
                        if ($debugMode === true) {
                            $debugScript = "if (window._si !== undefined) { window._si.push(['showlog','']); }";
                        }

                        $token = $this->handleToken();

                        $siteimproveOnDomReady = "
                        require(['jquery'], function($) {
                            $(document).ready(function() {
                                var _si = window._si || [];
                                var token = '" . $token . "';
                                $.ajax({
                                    url: TYPO3.settings.ajaxUrls['pixelant_siteimprove_getpagelink'],
                                    data: {
                                        id: " . (int)$pageId . "
                                    }
                                })
                                .done(function(data) {
                                    if (token) {
                                        _si.push(['domain', '" . $domain .
                                "', token, function() { console.log('Domain logged: " . $domain . "'); }]);
                                        _si.push(['input', data.pageUrl, token, function() {
                                            console.log('Inputted url: ' + data.pageUrl);
                                        }])
                                    }
                                });
                                " . $debugScript . '
                            });
                        });';

                        // Add overlay.js none concatenated
                        $pageRenderer->addJsFooterLibrary(
                            'SiteimproveOverlay',
                            'https://cdn.siteimprove.net/cms/overlay.js',
                            'text/javascript',
                            false,
                            true,
                            '',
                            true
                        );
                        $pageRenderer->addJsFooterInlineCode('siteimproveOnDomReady', $siteimproveOnDomReady);
                    }
                }
            }
        }
    }

    /**
     * Gets the current backend user
     *
     * @return BackendUserAuthentication
     */
    public function getBackendUser()
    {
        return $GLOBALS['BE_USER'];
    }

    /**
     * Getter for language service
     *
     * @return LanguageService
     */
    public function getLanguageService()
    {
        return $GLOBALS['LANG'];
    }

    /**
     * Get eid url to fetch FE page ID url
     *
     * @param $pageUid
     * @param $domain
     * @return string
     */
    protected function getEidUrl($pageUid, $domain)
    {
        //Define scheme
        $reverseProxyIP = explode(',', $GLOBALS['TYPO3_CONF_VARS']['SYS']['reverseProxyIP']);
        $reverseProxySSL = explode(',', $GLOBALS['TYPO3_CONF_VARS']['SYS']['reverseProxySSL']);
        $ipOfProxyOrClient = $_SERVER['REMOTE_ADDR'];

        if (
            (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ||
            (in_array($ipOfProxyOrClient, $reverseProxyIP) && isset($ipOfProxyOrClient) &&
                (in_array($ipOfProxyOrClient, $reverseProxySSL) || $reverseProxySSL[0] === '*'))
        ) {
            $scheme = 'https';
        } else {
            $scheme = 'http';
        }

        if (!empty($domain)) {
            $port = GeneralUtility::getIndpEnv('TYPO3_PORT');
            if (!empty($port)) {
                // Check if the domain already contains a port if so do not add port
                if (!preg_match('/\:\b/', $domain)) {
                    $domain .= ':' . $port;
                }
            }
        } else {
            $domain = GeneralUtility::getIndpEnv('HTTP_HOST');
        }

        $eidUrl = sprintf(
            '%s://%s/index.php?eID=pxa_siteimprove&id=%s',
            $scheme,
            $domain,
            (int) $pageUid
        );

        return $eidUrl;
    }

    /**
     * @return  FrontendInterface
     * @throws NoSuchCacheException
     */
    protected function getCache()
    {
        return GeneralUtility::makeInstance(CacheManager::class)->getCache('cache_pxasiteimprove_urls');
    }

    /**
     * Get the saved token from TYPO3 System Registry
     *
     * @return string
     */
    protected function getSavedToken()
    {
        return $this->registry->get(
            self::SITEIMPROVE_REGISTRY_KEY,
            self::SITEIMPROVE_REGISTRY_TOKEN_KEY
        ) ?: '';
    }

    /**
     * Handle the token saving and generation for Siteimprove
     *
     * @return string
     */
    protected function handleToken()
    {
        $token = $this->getSavedToken();
        if (!$token) {
            $token = $this->generateToken();
            $this->saveToken($token);
        }

        return $token;
    }

    /**
     * Save the token in TYPO3 System Registry
     *
     * @param string $token
     */
    protected function saveToken($token)
    {
        $this->registry->set(
            self::SITEIMPROVE_REGISTRY_KEY,
            self::SITEIMPROVE_REGISTRY_TOKEN_KEY,
            $token
        );
    }

    /**
     * Generate a token from Siteimprove service
     *
     * @return string
     */
    protected function generateToken()
    {
        $tokenData = json_decode(file_get_contents(self::SITEIMPROVE_TOKEN_GENERATOR), true);
        return $tokenData['token'];
    }
}
