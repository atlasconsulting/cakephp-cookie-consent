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
namespace Atlas\CookieConsent;

use Cake\Core\BasePlugin;

/**
 * Plugin for CookieConsent
 */
class Plugin extends BasePlugin
{
    /**
     * Do bootstrapping or not
     *
     * @var bool
     */
    protected $bootstrapEnabled = false;

    /**
     * Load routes or not
     *
     * @var bool
     */
    protected $routesEnabled = false;

    /**
     * Enable middleware
     *
     * @var bool
     */
    protected $middlewareEnabled = false;
}
