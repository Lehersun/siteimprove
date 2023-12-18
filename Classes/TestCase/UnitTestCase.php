<?php

namespace Pixelant\PxaSiteimprove\TestCase;

use Nimut\TestingFramework\TestCase\UnitTestCase as NimutUnitTestCase;

/**
 * Compatibility class with older versions of PhpUnit
 *
 * Can be removed with support for PHP 5.5
 *
 * @package Pixelant\PxaSiteimprove\TestCase
 */
class UnitTestCase extends NimutUnitTestCase
{
    /**
     * @inheritDoc
     */
    protected function createCompatibleMock($originalClassName)
    {
        if (method_exists(parent::class, 'createMock')) {
            return parent::createMock($originalClassName);
        }

        return $this->getMock($originalClassName);
    }
}
