<?php

namespace WP2StaticGuzzleHttp\Test\Handler;

use WP2StaticGuzzleHttp\Exception\ConnectException;
use WP2StaticGuzzleHttp\Handler\CurlHandler;
use WP2StaticGuzzleHttp\Promise\FulfilledPromise;
use WP2StaticGuzzleHttp\Psr7;
use WP2StaticGuzzleHttp\Psr7\Request;
use WP2StaticGuzzleHttp\Psr7\Response;
use WP2StaticGuzzleHttp\Tests\Server;
use WP2StaticGuzzleHttp\Utils;
use PHPUnit\Framework\TestCase;

/**
 * @covers \WP2StaticGuzzleHttp\Handler\CurlHandler
 */
class CurlHandlerTest extends TestCase
{
    protected function getHandler($options = [])
    {
        return new CurlHandler($options);
    }

    public function testCreatesCurlErrors()
    {
        $handler = new CurlHandler();
        $request = new Request('GET', 'http://localhost:123');

        $this->expectException(ConnectException::class);
        $this->expectExceptionMessage('cURL');
        $handler($request, ['timeout' => 0.001, 'connect_timeout' => 0.001])->wait();
    }

    public function testReusesHandles()
    {
        Server::flush();
        $response = new response(200);
        Server::enqueue([$response, $response]);
        $a = new CurlHandler();
        $request = new Request('GET', Server::$url);
        self::assertInstanceOf(FulfilledPromise::class, $a($request, []));
        self::assertInstanceOf(FulfilledPromise::class, $a($request, []));
    }

    public function testDoesSleep()
    {
        $response = new response(200);
        Server::enqueue([$response]);
        $a = new CurlHandler();
        $request = new Request('GET', Server::$url);
        $s = Utils::currentTime();
        $a($request, ['delay' => 0.1])->wait();
        self::assertGreaterThan(0.0001, Utils::currentTime() - $s);
    }

    public function testCreatesCurlErrorsWithContext()
    {
        $handler = new CurlHandler();
        $request = new Request('GET', 'http://localhost:123');
        $called = false;
        $p = $handler($request, ['timeout' => 0.001, 'connect_timeout' => 0.001])
            ->otherwise(static function (ConnectException $e) use (&$called) {
                $called = true;
                self::assertArrayHasKey('errno', $e->getHandlerContext());
            });
        $p->wait();
        self::assertTrue($called);
    }

    public function testUsesContentLengthWhenOverInMemorySize()
    {
        Server::flush();
        Server::enqueue([new Response()]);
        $stream = Psr7\Utils::streamFor(\str_repeat('.', 1000000));
        $handler = new CurlHandler();
        $request = new Request(
            'PUT',
            Server::$url,
            ['Content-Length' => 1000000],
            $stream
        );
        $handler($request, [])->wait();
        $received = Server::received()[0];
        self::assertEquals(1000000, $received->getHeaderLine('Content-Length'));
        self::assertFalse($received->hasHeader('Transfer-Encoding'));
    }
}
