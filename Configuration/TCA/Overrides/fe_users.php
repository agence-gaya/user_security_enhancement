<?php
$columns = array(

	'old_password_list' => array(
		'exclude' => 1,
		'label' => 'LLL:EXT:user_security_enhancement/Resources/Private/Language/locallang_db.xlf:fe_users.old_password_list',
		'config' => array(
			'type' => 'input',
			'eval' => 'trim',
		),
	),
    'lastlogin_attempt' => array(
        'exclude' => 1,
        'label' => 'LLL:EXT:user_security_enhancement/Resources/Private/Language/locallang_db.xlf:fe_users.lastlogin_attempt',
        'config' => array(
            'type' => 'input',
            'eval' => 'datetime',
            'size' => '13',
        ),
    ),
    'login_blocked_endtime' => array(
        'exclude' => 1,
        'label' => 'LLL:EXT:user_security_enhancement/Resources/Private/Language/locallang_db.xlf:fe_users.login_blocked_endtime',
        'config' => array(
            'type' => 'input',
            'eval' => 'datetime',
            'size' => '13',
        ),
    ),
    'login_attempt_failure' => array(
        'exclude' => 1,
        'label' => 'LLL:EXT:user_security_enhancement/Resources/Private/Language/locallang_db.xlf:fe_users.login_attempt_failure',
        'config' => array(
            'type' => 'input',
            'eval' => 'int,trim',
            'size' => '3',
        ),
    )
);

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTCAcolumns('fe_users', $columns);
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addToAllTCAtypes('fe_users', 'lastlogin_attempt,login_blocked_endtime,login_attempt_failure', '', 'after:endtime');