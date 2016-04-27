<?php
/*
 * This file is part of the TYPO3 CMS project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */

namespace Causal\CasSso\Service;

use TYPO3\CMS\Core\Utility\GeneralUtility;
use Causal\CasSso\Service\CentralAuthenticationService;

/**
 * This class is responsible for providing CAS SSO to TYPO3.
 *
 * @author      Xavier Perseguers <xavier@causal.ch>
 * @license     http://www.gnu.org/copyleft/gpl.html
 */
class AuthenticationService extends \TYPO3\CMS\Sv\AuthenticationService
{

    /**
     * true - this service was able to authenticate the user
     */
    const STATUS_AUTHENTICATION_SUCCESS_CONTINUE = true;

    /**
     * 200 - authenticated and no more checking needed
     */
    const STATUS_AUTHENTICATION_SUCCESS_BREAK = 200;

    /**
     * false - this service was the right one to authenticate the user but it failed
     */
    const STATUS_AUTHENTICATION_FAILURE_BREAK = false;

    /**
     * 100 - just go on. User is not authenticated but there's still no reason to stop
     */
    const STATUS_AUTHENTICATION_FAILURE_CONTINUE = 100;

    const EXT_KEY = 'cas_sso';

    /**
     * @var CentralAuthenticationService
     */
    protected $service;

    /**
     * @var array
     */
    protected $settings;

    /**
     * @var bool
     */
    protected $isAuthenticated = false;

    /**
     * Default constructor
     */
    public function __construct()
    {
        $settings = $GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf'][static::EXT_KEY];
        $this->settings = $settings ? unserialize($settings) : [];

        $this->service = GeneralUtility::makeInstance(CentralAuthenticationService::class);
        if (!$this->service->initialize($this->settings)) {
            // Invalidate the service
            $this->service = null;
        }
    }

    /**
     * Tries to authenticate via SSO and if successful, simply put the username into
     * superglobal $_SERVER['REMOTE_USER'].
     *
     * @return false
     */
    public function getUser()
    {
        if ($this->service === null) {
            // CAS is not available
            return false;
        }

        $authenticatedUser = $this->service->login();
        if (!empty($authenticatedUser)) {
            $_SERVER['REMOTE_USER'] = $authenticatedUser;
            $this->isAuthenticated = true;
        }

        // Always return false here to ensure the TYPO3 user may then be fetched using superglobal
        // $_SERVER['REMOTE_USER'], e.g., using EXT:ig_ldap_sso_auth with "SSO" enabled
        return false;
    }

    /**
     * Authenticates a user (Check various conditions for the user that might invalidate its
     * authentication, e.g., password match, domain, IP, etc.).
     *
     * @param array $user Data of user.
     * @return int|false
     */
    public function authUser(array $user)
    {
        if ($this->service === null) {
            // CAS is not available
            return static::STATUS_AUTHENTICATION_FAILURE_CONTINUE;
        }

        if ($this->isAuthenticated) {
            // From our point of view the user is properly authenticated but it may get
            // further checked by other authentication services (e.g., domain-based restriction)
            return static::STATUS_AUTHENTICATION_SUCCESS_CONTINUE;
        }

        // This service could not authorize the user via SSO
        return static::STATUS_AUTHENTICATION_FAILURE_CONTINUE;
    }

}
