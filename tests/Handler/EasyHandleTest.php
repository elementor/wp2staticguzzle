<?php

namespace WP2StaticGuzzleHttp\Test\Handler;

use WP2StaticGuzzleHttp\Handler\EasyHandle;
use PHPUnit\Framework\TestCase;

/**
 * @covers \WP2StaticGuzzleHttp\Handler\EasyHandle
 */
class EasyHandleTest extends TestCase
{
    public function testEnsuresHandleExists()
    {
        $easy = new EasyHandle;
        unset($easy->handle);

        $this->expectException(\BadMethodCallException::class);
        $this->expectExceptionMessage('The EasyHandle has been released');
        $easy->handle;
    }
}
