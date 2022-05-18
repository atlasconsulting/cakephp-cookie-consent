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
namespace Atlas\CookieConsent\Middleware;

use Cake\Core\InstanceConfigTrait;
use Cake\Http\Cookie\Cookie;
use Cake\Http\Response;
use Cake\Utility\Hash;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * Cookie consent middleware.
 */
class CookieConsentMiddleware implements MiddlewareInterface
{
    use InstanceConfigTrait;

    /**
     * Default configuration:
     *
     * - `cookieName` => the name of cookie used for trace user choices
     * - `searchIn` => the key of cookie value to analyze
     * - `remove` => array of cookies to remove divided by categories ('preferences', ...)
     *
     * @var array
     */
    protected $_defaultConfig = [
        'cookieName' => 'cc_cookie',
        'searchIn' => 'level',
        'remove' => [
            'preferences' => [],
            'analytics' => [],
            'targeting' => [],
        ],
    ];

    /**
     * Constructor.
     *
     * @param array $config The middleware configuration
     * @codeCoverageIgnore
     */
    public function __construct(array $config = [])
    {
        $this->setConfig($config);
    }

    /**
     * Remove cookies not accepted.
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request The request
     * @param \Psr\Http\Server\RequestHandlerInterface $handler The request handler
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $cookieConsent = Hash::get($request->getCookieParams(), $this->getConfig('cookieName'));

        if (empty($cookieConsent)) { // consent not given
            return $this->removeCookies($handler->handle($request), array_keys($this->getConfig('remove')));
        }

        $data = json_decode($cookieConsent, true);

        $choices = (array)Hash::get($data, $this->getConfig('searchIn'));
        $toRemove = array_diff(array_keys($this->getConfig('remove')), $choices);

        return $this->removeCookies($handler->handle($request), $toRemove);
    }

    /**
     * Remove cookies of certain categories.
     *
     * @param \Psr\Http\Message\ResponseInterface $response The response.
     * @param array $categories Categories of cookies to remove.
     * @return \Psr\Http\Message\ResponseInterface
     */
    protected function removeCookies(ResponseInterface $response, array $categories): ResponseInterface
    {
        if (!$response instanceof Response) {
            return $response;
        }

        foreach ($categories as $category) {
            $cookies = (array)$this->getConfig("remove.$category");
            foreach ($cookies as $cookie) {
                /** @var \Cake\Http\Response $response */
                $response = $response->withExpiredCookie(new Cookie($cookie));
            }
        }

        return $response;
    }
}
