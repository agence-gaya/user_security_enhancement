<?php
if (!defined('TYPO3_MODE')) {
	die('Access denied.');
}

$GLOBALS['TYPO3_CONF_VARS']['SYS']['Objects']['TYPO3\CMS\Felogin\Controller\FrontendLoginController']['className'] = 'GAYA\UserSecurityEnhancement\Controller\FrontendLoginController';
$GLOBALS['TYPO3_CONF_VARS']['SYS']['Objects']['TYPO3\CMS\Saltedpasswords\SaltedPasswordService']['className'] = 'GAYA\UserSecurityEnhancement\Service\UseSaltedPasswordService';