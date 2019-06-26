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

use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;


/**
 * This class is the actual proxy to CAS server.
 *
 * @author      Xavier Perseguers <xavier@causal.ch>
 * @license     http://www.gnu.org/copyleft/gpl.html
 */
class CentralAuthenticationService
{

    const EXT_KEY = 'cas_sso';

    /**
     * Initializes this service.
     *
     * @param array $settings
     * @return bool Returns true if the service could be initialized, otherwise false
     */
    public function initialize(array $settings)
    {
        // Basic checks
        $requiredSettings = ['cas_host', 'cas_context', 'cas_port'];
        $isReady = true;
        foreach ($requiredSettings as $key) {
            $isReady &= isset($settings[$key]) && !empty($settings[$key]);
        }

        if (!$isReady) {
            return false;
        }

        // Load the phpCAS framework
        $phpCasPath =  GeneralUtility::getIndpEnv('TYPO3_DOCUMENT_ROOT') . '/../vendor/apereo/phpcas/CAS.php';
        require_once $phpCasPath;

        $isDevelopment = true; //GeneralUtility::getApplicationContext() === 'Development';
        if ($isDevelopment) {
            $logFileName = PATH_site . 'typo3temp/logs/cas_sso.log';
            \phpCas::setDebug($logFileName);

            // Enable verbose error messages
            \phpCAS::setVerbose(true);
        }

        // Initialize phpCAS
        \phpCAS::client(CAS_VERSION_2_0, $settings['cas_host'], (int)$settings['cas_port'], $settings['cas_context']);

        // set the language to french
        \phpCAS::setLang(PHPCAS_LANG_FRENCH);

        if (!empty($settings['ca_cert_path'])) {
            \phpCAS::setCasServerCACert(GeneralUtility::getFileAbsFileName($settings['ca_cert_path']));
        } else {
            // For quick testing you can disable SSL validation of the CAS server
            // THIS SETTING IS NOT RECOMMENDED FOR PRODUCTION.
            // VALIDATING THE CAS SERVER IS CRUCIAL TO THE SECURITY OF THE CAS PROTOCOL!
            \phpCAS::setNoCasServerValidation();
        }

        return true;
    }

    /**
     * Forces the authentication against CAS and returns the authenticated user's login name.
     *
     * @return string The user's login name
     */
    public function login()
    {
        \phpCAS::forceAuthentication();
        return \phpCAS::getUser();
    }

    /**
     * Logs out the user from CAS server.
     *
     * @param string $redirectUri
     * @return void
     */
    public function logout($redirectUri = '')
    {
        if (empty($redirectUri)) {
            \phpCAS::logout();
        } else {
            \phpCAS::logoutWithRedirectService($redirectUri);
        }
    }

}
