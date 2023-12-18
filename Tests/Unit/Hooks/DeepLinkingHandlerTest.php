<?php

namespace Pixelant\PxaSiteimprove\Tests\Unit\Hooks;

use Pixelant\PxaSiteimprove\Hooks\DeepLinkingHandler;
use Pixelant\PxaSiteimprove\TestCase\UnitTestCase;
use Pixelant\PxaSiteimprove\Utility\CompatibilityUtility;
use TYPO3\CMS\Backend\Controller\BackendController;
use TYPO3\CMS\Core\Authentication\BackendUserAuthentication;

class DeepLinkingHandlerTest extends UnitTestCase
{
    /**
     * @var DeepLinkingHandler
     */
    protected $subject;

    /**
     * Create the subject
     *
     * @return void
     */
    protected function createSubject()
    {
        $this->subject = new DeepLinkingHandler();
    }

    /**
     * @test
     */
    public function storeGoToRequestInUserSessionCorrectly()
    {
        $this->createSubject();
        if ($this->typo3VersionSupportsDeeplinking()) {
            $expected = 'page:1:2';

            $backendUserAuthentication = $this->createCompatibleMock(BackendUserAuthentication::class);
            $backendUserAuthentication
                ->expects($this->once())
                ->method('setAndSaveSessionData')
                ->with(
                    $this->equalTo('tx_siteimprove_goto'),
                    $this->equalTo($expected)
                );

            $_REQUEST['tx_siteimprove_goto'] = $expected;
            $GLOBALS['BE_USER'] = [];

            $this->subject->storeGoToRequestInUserSession(
                [],
                $backendUserAuthentication
            );
        }
    }

    /**
     * @test
     */
    public function storeGoToRequestInUserSessionSkipsWhenNoUserSet()
    {
        $this->createSubject();
        if ($this->typo3VersionSupportsDeeplinking()) {
            $expected = 'page:1:2';

            $backendUserAuthentication = $this->createCompatibleMock(BackendUserAuthentication::class);
            $backendUserAuthentication
                ->expects($this->never())
                ->method('setAndSaveSessionData')
                ->with(
                    $this->equalTo('tx_siteimprove_goto'),
                    $this->equalTo($expected)
                );

            $_REQUEST['tx_siteimprove_goto'] = $expected;
            $GLOBALS['BE_USER'] = null;

            $this->subject->storeGoToRequestInUserSession(
                [],
                $backendUserAuthentication
            );
        }
    }

    /**
     * @test
     */
    public function storeGoToRequestInUserSessionSkipsWhenNoArgumentSet()
    {
        $this->createSubject();
        if ($this->typo3VersionSupportsDeeplinking()) {
            $expected = 'page:1:2';

            $backendUserAuthentication = $this->createCompatibleMock(BackendUserAuthentication::class);
            $backendUserAuthentication
                ->expects($this->never())
                ->method('setAndSaveSessionData')
                ->with(
                    $this->equalTo('tx_siteimprove_goto'),
                    $this->equalTo($expected)
                );

            $GLOBALS['BE_USER'] = [];

            $this->subject->storeGoToRequestInUserSession(
                [],
                $backendUserAuthentication
            );
        }
    }

    /**
     * @test
     */
    public function effectuateGoToRequestCallsPageTypeHandlerCorrectly()
    {
        $this->createSubject();
        if ($this->typo3VersionSupportsDeeplinking()) {
            $expected = 'web_layout->id=1&SET[language]=2';

            $backendUserAuthentication = $this->createCompatibleMock(BackendUserAuthentication::class);

            $backendUserAuthentication
                ->expects($this->once())
                ->method('getSessionData')
                ->willReturn('page:1:2');

            $backendController = $this->createCompatibleMock(BackendController::class);

            $GLOBALS['BE_USER'] = $backendUserAuthentication;
            $GLOBALS['BE_USER']->uc = [];

            $this->subject->effectuateGoToRequest(
                [],
                $backendController
            );

            $this->assertEquals(
                $expected,
                $GLOBALS['BE_USER']->uc['startModuleOnFirstLogin']
            );
        }
    }

    protected function typo3VersionSupportsDeeplinking()
    {
        if (
            CompatibilityUtility::typo3VersionIsLessThan('8.0')
            || CompatibilityUtility::typo3VersionIsGreaterThanOrEqualTo('9.5')
        ) {
            $this->markTestSkipped('Only used in TYPO3 v8');
            return false;
        }

        return true;
    }
}
