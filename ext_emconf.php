<?php

/***************************************************************
 * Extension Manager/Repository config file for ext "cas_sso".
 *
 ***************************************************************/

$EM_CONF[$_EXTKEY] = array(
    'title' => 'Central Authentication Service (CAS)',
    'description' => 'This extension provides SSO support for TYPO3 by delegating the authentication of frontend and/or backend users to a CAS server.',
    'category' => 'services',
    'shy' => 0,
    'version' => '1.0.0-dev',
    'dependencies' => '',
    'conflicts' => '',
    'priority' => '',
    'loadOrder' => '',
    'module' => '',
    'state' => 'stable',
    'uploadfolder' => 0,
    'createDirs' => '',
    'modify_tables' => '',
    'clearcacheonload' => 0,
    'lockType' => '',
    'author' => 'Xavier Perseguers',
    'author_email' => 'xavier@causal.ch',
    'author_company' => 'Causal Sarl',
    'CGLcompliance' => '',
    'CGLcompliance_note' => '',
    'constraints' => array(
        'depends' => array(
            'php' => '5.5.0-7.0.99',
            'typo3' => '8.7.0-8.7.99',
        ),
        'conflicts' => array(),
        'suggests' => array(
            'ig_ldap_sso_auth' => '3.1.0-',
        ),
    ),
    '_md5_values_when_last_written' => '',
    'suggests' => array(),
    'autoload' => array(
        'psr-4' => array('Causal\\CasSso\\' => 'Classes')
    ),
);
