<?php

$columns = [

    'old_password_list' => [
        'exclude' => 1,
        'label' => 'LLL:EXT:user_security_enhancement/Resources/Private/Language/locallang_db.xlf:fe_users.old_password_list',
        'config' => [
            'type' => 'input',
            'eval' => 'trim',
        ],
    ],
    'login_blocked_endtime' => [
        'exclude' => 1,
        'label' => 'LLL:EXT:user_security_enhancement/Resources/Private/Language/locallang_db.xlf:fe_users.login_blocked_endtime',
        'config' => [
            'type' => 'input',
            'eval' => 'datetime',
            'size' => '13',
        ],
    ],
    'login_attempt_failure' => [
        'exclude' => 1,
        'label' => 'LLL:EXT:user_security_enhancement/Resources/Private/Language/locallang_db.xlf:fe_users.login_attempt_failure',
        'config' => [
            'type' => 'input',
            'eval' => 'int,trim',
            'size' => '3',
        ],
    ],
];

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTCAcolumns('fe_users', $columns);
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addToAllTCAtypes('fe_users', 'login_blocked_endtime,login_attempt_failure', '', 'after:endtime');
