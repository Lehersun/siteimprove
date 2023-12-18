<?php

namespace Pixelant\PxaSiteimprove\Tests\Functional\Controller;

use Nimut\TestingFramework\TestCase\FunctionalTestCase;
use Pixelant\PxaSiteimprove\Controller\AjaxBackendController;
use Pixelant\PxaSiteimprove\Utility\CompatibilityUtility;
use TYPO3\CMS\Core\Http\ServerRequest;
use TYPO3\CMS\Core\TimeTracker\NullTimeTracker;

class AjaxBackendControllerTest extends FunctionalTestCase
{
    /**
     * @var AjaxBackendController
     */
    protected $subject;

    /**
     * @var string
     */
    protected $rootPath = '/home/runner/work/pxa_siteimprove/pxa_siteimprove/';

    /**
     * Create the setup for the test case
     *
     * @return void
     * @throws \Nimut\TestingFramework\Exception\Exception
     */
    protected function createSetup()
    {
        if (CompatibilityUtility::typo3VersionIsLessThan('8.0')) {
            $GLOBALS['TT'] = new NullTimeTracker();
        }

        if (CompatibilityUtility::typo3VersionIsGreaterThanOrEqualTo('9.5')) {
            $this->importDataSet($this->rootPath . 'Tests/Fixtures/Database/pages.xml');
        } else {
            $this->importDataSet($this->rootPath . 'Tests/Fixtures/Database/pages-legacy.xml');
        }

        if (CompatibilityUtility::typo3VersionIsGreaterThanOrEqualTo('10.0')) {
            $this->setUpFrontendRootPage(1);
        } else {
            $this->setUpFrontendRootPage(
                1,
                [$this->rootPath . '.Build/vendor/nimut/testing-framework/res/Fixtures/TypoScript/JsonRenderer.ts']
            );
        }

        $this->setUpBackendUserFromFixture(1);

        $this->subject = new AjaxBackendController();
    }

    /**
     * @test
     */
    public function getPageLinkActionReturnsCorrectUrl()
    {
        $this->createSetup();

        $request = (new ServerRequest())->withQueryParams(['id' => 2]);
        $response = $this->subject->getPageLinkAction($request);
        $body = (string)$response->getBody();

        if (CompatibilityUtility::typo3VersionIsGreaterThanOrEqualTo('9.5')) {
            $expected = '{"pageUrl":"\/dummy-1-2"}';
        } else {
            $testUrl = str_replace(
                '/',
                '\/',
                'http:/' . $this->rootPath . '.Build/bin/index.php?id=2'
            );
            $expected = '{"pageUrl":"' . $testUrl . '"}';
        }

        $this->assertEquals(
            $expected,
            $body
        );
    }
}
