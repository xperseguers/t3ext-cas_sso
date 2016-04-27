<?php
defined('TYPO3_MODE') || die();

$boot = function ($_EXTKEY) {
    $settings = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf'][$_EXTKEY]);
    if (is_array($settings)) {
        $subTypesArr = [];

        if (isset($settings['enable_fe_sso']) && (bool)$settings['enable_fe_sso']) {
            $GLOBALS['TYPO3_CONF_VARS']['SVCONF']['auth']['setup']['FE_fetchUserIfNoSession'] = 1;

            $subTypesArr[] = 'getUserFE';
            $subTypesArr[] = 'authUserFE';
        }
        if (isset($settings['enable_be_sso']) && (bool)$settings['enable_be_sso']) {
            $GLOBALS['TYPO3_CONF_VARS']['SVCONF']['auth']['setup']['BE_fetchUserIfNoSession'] = 1;

            $subTypesArr[] = 'getUserBE';
            $subTypesArr[] = 'authUserBE';
        }

        $subTypes = implode(',', $subTypesArr);

        if (!empty($subTypes)) {
            \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addService(
                $_EXTKEY,
                'auth' /* sv type */,
                \Causal\CasSso\Service\AuthenticationService::class /* sv key */,
                [
                    'title' => 'CAS SSO',
                    'description' => 'Central authentication service for SSO environment.',

                    'subtype' => $subTypes,

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
