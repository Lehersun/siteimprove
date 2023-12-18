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

use TYPO3\CMS\Backend\Controller\BackendController;
use TYPO3\CMS\Core\Authentication\AbstractUserAuthentication;
use TYPO3\CMS\Core\SingletonInterface;

class DeepLinkingHandler implements SingletonInterface
{
    /**
     * Store GoTo data in the user's session for use when the backend is rendered
     *
     * @param array $params
     * @param \TYPO3\CMS\Core\Authentication\AbstractUserAuthentication $pObj
     */
    public function storeGoToRequestInUserSession(array $params, AbstractUserAuthentication $pObj)
    {
        if ($GLOBALS['BE_USER'] !== null && $_REQUEST['tx_siteimprove_goto']) {
            $pObj->setAndSaveSessionData('tx_siteimprove_goto', $_REQUEST['tx_siteimprove_goto']);
        }
    }

    /**
     * Check the GoTo request and effectuate if it can be handled
     *
     * GoTo request format is "<type>:<argument[s]>".
     * "page:12:2" will open the Page (web_layout) module on page 12, language 2.
     *
     * @param array $conf
     * @param \TYPO3\CMS\Backend\Controller\BackendController $pObj
     */
    public function effectuateGoToRequest(array $conf, BackendController $pObj)
    {
        /**
         * @var \TYPO3\CMS\Core\Session\Backend\SessionBackendInterface $GLOBALS[BE_USER]
         */
        $goToSpecification = $GLOBALS['BE_USER']->getSessionData('tx_siteimprove_goto');
        $GLOBALS['BE_USER']->setAndSaveSessionData('tx_siteimprove_goto', null);

        if ($goToSpecification !== null && strpos($goToSpecification, ':') !== false) {
            list($type, $argument) = explode(':', $goToSpecification, 2);

            if (mb_strlen($type) > 0 && mb_strlen($argument) > 0) {
                switch ($type) {
                    case 'page':
                        $this->pageTypeHandler($argument);
                        break;
                }
            }
        }
    }

    /**
     * The handler for GoTo requests of the type "GoTo"
     *
     * @param $argument
     */
    protected function pageTypeHandler($argument)
    {
        list($pageId, $languageId) = explode(':', $argument);

        $GLOBALS['BE_USER']->uc['startModuleOnFirstLogin'] = 'web_layout->id='
            . (int)$pageId . '&SET[language]=' . (int)$languageId;
    }
}
