<?php

namespace WP2StaticGuzzleHttp\Test\Handler;

use WP2StaticGuzzleHttp\Handler\MockHandler;
use WP2StaticGuzzleHttp\Handler\Proxy;
use WP2StaticGuzzleHttp\Psr7\Request;
use WP2StaticGuzzleHttp\RequestOptions;
use PHPUnit\Framework\TestCase;

/**
 * @covers \WP2StaticGuzzleHttp\Handler\Proxy
 */
class ProxyTest extends TestCase
{
    public function testSendsToNonSync()
    {
        $a = $b = null;
        $m1 = new MockHandler([static function ($v) use (&$a) {
            $a = $v;
        }]);
        $m2 = new MockHandler([static function ($v) use (&$b) {
            $b = $v;
        }]);
        $h = Proxy::wrapSync($m1, $m2);
        $h(new Request('GET', 'http://foo.com'), []);
        self::assertNotNull($a);
        self::assertNull($b);
    }

    public function testSendsToSync()
    {
        $a = $b = null;
        $m1 = new MockHandler([static function ($v) use (&$a) {
            $a = $v;
        }]);
        $m2 = new MockHandler([static function ($v) use (&$b) {
            $b = $v;
        }]);
        $h = Proxy::wrapSync($m1, $m2);
        $h(new Request('GET', 'http://foo.com'), [RequestOptions::SYNCHRONOUS => true]);
        self::assertNull($a);
        self::assertNotNull($b);
    }

    public function testSendsToStreaming()
    {
        $a = $b = null;
        $m1 = new MockHandler([static function ($v) use (&$a) {
            $a = $v;
        }]);
        $m2 = new MockHandler([static function ($v) use (&$b) {
            $b = $v;
        }]);
        $h = Proxy::wrapStreaming($m1, $m2);
        $h(new Request('GET', 'http://foo.com'), []);
        self::assertNotNull($a);
        self::assertNull($b);
    }

    public function testSendsToNonStreaming()
    {
        $a = $b = null;
        $m1 = new MockHandler([static function ($v) use (&$a) {
            $a = $v;
        }]);
        $m2 = new MockHandler([static function ($v) use (&$b) {
            $b = $v;
        }]);
        $h = Proxy::wrapStreaming($m1, $m2);
        $h(new Request('GET', 'http://foo.com'), ['stream' => true]);
        self::assertNull($a);
        self::assertNotNull($b);
    }
}
