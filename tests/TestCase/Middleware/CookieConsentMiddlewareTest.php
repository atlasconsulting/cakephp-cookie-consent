<?php
declare(strict_types=1);

/**
 * Cookie Consent plugin for CakePHP
 * Copyright (c) Atlas Srl (https://atlasconsulting.it)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @see https://opensource.org/licenses/mit-license.php MIT License
 */
namespace Test\Atlas\Middleware;

use Atlas\CookieConsent\Middleware\CookieConsentMiddleware;
use Cake\Http\Cookie\Cookie;
use Cake\Http\Response;
use Cake\Http\ServerRequestFactory;
use Cake\TestSuite\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * Test case for {@see \Atlas\CookieConsent\Middleware\CookieConsentMiddleware}.
 *
 * @coversDefaultClass \Atlas\CookieConsent\Middleware\CookieConsentMiddleware
 */
class CookieConsentMiddlewareTest extends TestCase
{
    /**
     * Fake request handler
     *
     * @var \Psr\Http\Server\RequestHandlerInterface
     */
    protected $requestHandler;

    /**
     * @inheritDoc
     */
    public function setUp(): void
    {
        parent::setUp();

        $this->requestHandler = new class () implements RequestHandlerInterface {
            /**
             * Implementation of handle method
             *
             * @param ServerRequestInterface $request The request.
             * @return ResponseInterface
             */
            public function handle(ServerRequestInterface $request): ResponseInterface
            {
                $response = new Response();
                $cookies = $request->getCookieParams();
                foreach ($cookies as $name => $value) {
                    $response = $response->withCookie(new Cookie($name, $value));
                }

                return $response;
            }
        };
    }

    /**
     * @inheritDoc
     */
    public function tearDown(): void
    {
        parent::tearDown();

        unset($this->requestHandler);
    }

    /**
     * Data provider for testProcess.
     *
     * @return array
     */
    public function cookieProvider(): array
    {
        return [
            'cc_cookie missing' => [
                [],
                ['to-accept-cookie' => 'hello'],
                [],
            ],
            'cc_cookie remove analytics' => [
                ['analytic-cookie', 'targeting-cookie'],
                [
                    'to-accept-cookie' => 'hello',
                    'analytic-cookie' => 'hello analytic',
                    'targeting-cookie' => 'hello targeting',
                    'cc_cookie' => json_encode([
                        'level' => [
                            'preferences',
                        ],
                    ]),
                ],
                [
                    'remove' => [
                        'analytics' => ['analytic-cookie'],
                        'targeting' => ['targeting-cookie'],
                    ],
                ],
            ],
        ];
    }

    /**
     * Test that cookie without consent are expired.
     *
     * @param array $expiredExpected List of cookies expected as expired
     * @param array $cookies Array of request cookies
     * @param array $conf Middleware configuration
     * @return void
     * @dataProvider cookieProvider()
     * @covers ::process()
     * @covers ::removeCookies()
     */
    public function testProcess(array $expiredExpected, array $cookies, array $conf): void
    {
        $request = ServerRequestFactory::fromGlobals(null, null, null, $cookies);
        $middleware = new CookieConsentMiddleware($conf);
        /** @var \Cake\Http\Response $response */
        $response = $middleware->process($request, $this->requestHandler);

        static::assertEquals(count($cookies), $response->getCookieCollection()->count());

        foreach ($response->getCookieCollection() as $cookie) {
            /** @var \Cake\Http\Cookie\Cookie $cookie */
            if (in_array($cookie->getName(), $expiredExpected)) {
                static::assertTrue($cookie->isExpired());
            } else {
                static::assertFalse($cookie->isExpired());
            }
        }
    }
}
