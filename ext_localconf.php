<?php

if (!defined('TYPO3_MODE')) {
    die('Access denied.');
}

# Extbase version
$GLOBALS['TYPO3_CONF_VARS']['SYS']['Objects']['TYPO3\CMS\FrontendLogin\Controller\PasswordRecoveryController']['className'] = 'GAYA\UserSecurityEnhancement\Controller\PasswordRecoveryController';
$GLOBALS['TYPO3_CONF_VARS']['SYS']['Objects']['TYPO3\CMS\FrontendLogin\Controller\FrontendUserRepository']['className'] = 'GAYA\UserSecurityEnhancement\Domain\Repository\FrontendUserRepository';
