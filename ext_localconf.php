<?php
defined('TYPO3_MODE') || die();

$boot = function ($_EXTKEY) {
    $settings = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf'][$_EXTKEY]);
    if (is_array($settings)) {
        $registerLogOffPostProcessing = false;
        $subtypes = [];

        if (isset($settings['enable_fe_sso']) && (bool)$settings['enable_fe_sso']) {
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
    }
};

$boot($_EXTKEY);
unset($boot);
