<?php
defined('TYPO3_MODE') || die();

$boot = function ($_EXTKEY) {
    $settings = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf'][$_EXTKEY]);
    if (is_array($settings)) {
        $registerLogOffPostProcessing = false;
        $subtypes = [];

        // Compatibility with EXT:cabag_loginas
        $cabloginasParameters = \TYPO3\CMS\Core\Utility\GeneralUtility::_GET('tx_cabagloginas');
        $simulateFrontendUser = is_array($cabloginasParameters) && !empty($cabloginasParameters['userid']);

        if (!$simulateFrontendUser && isset($settings['enable_fe_sso']) && (bool)$settings['enable_fe_sso']) {
            $GLOBALS['TYPO3_CONF_VARS']['SVCONF']['auth']['setup']['FE_fetchUserIfNoSession'] = 1;
            $registerLogOffPostProcessing = true;

            $subtypes[] = 'getUserFE';
            $subtypes[] = 'authUserFE';
        }
        if (isset($settings['enable_be_sso']) && (bool)$settings['enable_be_sso']) {
            $GLOBALS['TYPO3_CONF_VARS']['SVCONF']['auth']['setup']['BE_fetchUserIfNoSession'] = 1;
            $registerLogOffPostProcessing = true;

            $subtypes[] = 'getUserBE';
            $subtypes[] = 'authUserBE';
        }

        if ($registerLogOffPostProcessing) {
            $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_userauth.php']['logoff_post_processing'][] = \Causal\CasSso\Service\AuthenticationService::class . '->logout';
        }

        // If the request comes from EXT:crawler then we should silently disable SSO
        $userAgent = \TYPO3\CMS\Core\Utility\GeneralUtility::getIndpEnv('HTTP_USER_AGENT');
        if (stripos($userAgent, 'TYPO3 crawler') !== false) {
            $subtypes = [];
        }

        if (!empty($subtypes)) {
            \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addService(
                $_EXTKEY,
                'auth' /* sv type */,
                \Causal\CasSso\Service\AuthenticationService::class /* sv key */,
                [
                    'title' => 'CAS SSO',
                    'description' => 'Central authentication service for SSO environment.',

                    'subtype' => implode(',', $subtypes),

                    'available' => true,
                    'priority' => 90, // Higher priority than EXT:ig_ldap_sso_auth (80)
                    'quality' => 80,

                    'os' => '',
                    'exec' => '',

                    'className' => \Causal\CasSso\Service\AuthenticationService::class,
                ]
            );
        }

        // Under some circumstances, the header "X_FORWARDED_PORT" may be missing but instead
        // appended to X_FORWARDED_HOST. This is valid according to rfc7239#5.3 and rfc7230#2.7.1
        // but may do more harm than good
        if (isset($_SERVER['HTTP_X_FORWARDED_HOST']) && !empty($settings['fix_x_forwarded_port'])) {
            // explode the host list separated by comma and use the first host
            $hosts = explode(',', $_SERVER['HTTP_X_FORWARDED_HOST']);
            if (!empty($hosts)) {
                list($forwardedHost, $forwardedPort) = explode(':', $hosts[0]);
                if (!empty($forwardedPort)) {
                    $_SERVER['HTTP_X_FORWARDED_PORT'] = $forwardedPort;
                    $hosts[0] = $forwardedHost;
                    $_SERVER['HTTP_X_FORWARDED_HOST'] = implode(',', $hosts);

                    if (!isset($_SERVER['HTTP_X_FORWARDED_PROTO'])) {
                        $_SERVER['HTTP_X_FORWARDED_PROTO'] = (int)$forwardedPort === 443 ? 'https' : 'http';
                    }
                }
            }
        }
    }
};

$boot($_EXTKEY);
unset($boot);
