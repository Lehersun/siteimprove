<?php

namespace Pixelant\PxaSiteimprove\Tests\Unit\Middleware;

use Pixelant\PxaSiteimprove\Middleware\StoreGoToRequestInUserSession;
use Pixelant\PxaSiteimprove\TestCase\UnitTestCase;
use Pixelant\PxaSiteimprove\Utility\CompatibilityUtility;
use TYPO3\CMS\Backend\Http\RequestHandler;
use TYPO3\CMS\Core\Authentication\BackendUserAuthentication;
use TYPO3\CMS\Core\Http\ServerRequest;

class StoreGoToRequestInUserSessionTest extends UnitTestCase
{
    /**
     * @var StoreGoToRequestInUserSession
     */
    protected $subject;

    /**
     * Create the subject
     *
     * @return void
     */
    protected function createSubject()
    {
        if ($this->typo3VersionSupportsMiddlewares()) {
            $this->subject = new StoreGoToRequestInUserSession();
        }
    }

    /**
     * @test
     */
    public function sessionDataIsSetCorrectly()
    {
        $this->createSubject();
        if ($this->typo3VersionSupportsMiddlewares()) {
            $expected = 'page:1:2';

            $backendUserAuthentication = $this->createCompatibleMock(BackendUserAuthentication::class);
            $backendUserAuthentication
                ->expects($this->once())
                ->method('setAndSaveSessionData')
                ->with(
                    $this->equalTo('tx_siteimprove_goto'),
                    $this->equalTo($expected)
                );

            $requestHandler = $this->createCompatibleMock(RequestHandler::class);

            $_REQUEST['tx_siteimprove_goto'] = $expected;
            $GLOBALS['BE_USER'] = $backendUserAuthentication;

            $this->subject->process(
                new ServerRequest(),
                $requestHandler
            );
        }
    }

    /**
     * @test
     */
    public function sessionDataIsNotSetIfNoArgumentSet()
    {
        $this->createSubject();
        if ($this->typo3VersionSupportsMiddlewares()) {
            $expected = 'page:1:2';

            $backendUserAuthentication = $this->createCompatibleMock(BackendUserAuthentication::class);
            $backendUserAuthentication
                ->expects($this->never())
                ->method('setAndSaveSessionData')
                ->with(
                    $this->equalTo('tx_siteimprove_goto'),
                    $this->equalTo($expected)
                );

            $requestHandler = $this->createCompatibleMock(RequestHandler::class);

            $GLOBALS['BE_USER'] = $backendUserAuthentication;

            $this->subject->process(
                new ServerRequest(),
                $requestHandler
            );
        }
    }

    /**
     * @test
     */
    public function sessionDataIsNotSetIfNoBeUser()
    {
        $this->createSubject();
        if ($this->typo3VersionSupportsMiddlewares()) {
            $expected = 'page:1:2';

            $backendUserAuthentication = $this->createCompatibleMock(BackendUserAuthentication::class);
            $backendUserAuthentication
                ->expects($this->never())
                ->method('setAndSaveSessionData')
                ->with(
                    $this->equalTo('tx_siteimprove_goto'),
                    $this->equalTo($expected)
                );

            $requestHandler = $this->createCompatibleMock(RequestHandler::class);

            $_REQUEST['tx_siteimprove_goto'] = $expected;
            $GLOBALS['BE_USER'] = null;

            $this->subject->process(
                new ServerRequest(),
                $requestHandler
            );
        }
    }

    protected function typo3VersionSupportsMiddlewares()
    {
        if (
            CompatibilityUtility::typo3VersionIsLessThan('9.5')
        ) {
            $this->markTestSkipped('Middleware. Not used in TYPO3 versions <9.5');
            return false;
        }

        return true;
    }
}
