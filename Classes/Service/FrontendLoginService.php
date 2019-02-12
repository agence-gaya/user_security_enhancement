<?php
namespace GAYA\UserSecurityEnhancement\Service;

use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class FrontendLoginService implements SingletonInterface
{
    /**
     * @var \TYPO3\CMS\Core\Database\DatabaseConnection
     */
    protected $databaseConnection = null;

    /**
     * configurationUtility
     *
     * @var \GAYA\UserSecurityEnhancement\Utility\ConfigurationUtility
     */
    protected $configurationUtility = NULL;

    /**
     * @var \GAYA\UserSecurityEnhancement\Utility\LoginUtility
     */
    protected $loginUtility;

    public function __construct()
    {
        $this->configurationUtility = GeneralUtility::makeInstance(\GAYA\UserSecurityEnhancement\Utility\ConfigurationUtility::class);
        $this->loginUtility = GeneralUtility::makeInstance(\GAYA\UserSecurityEnhancement\Utility\LoginUtility::class);
        $this->databaseConnection = $GLOBALS['TYPO3_DB'];
    }

    public function updateLoginAttemptFailure(&$user)
    {
        $configuration = $this->configurationUtility->getConfiguration();

        if ($configuration['authenticationFailureAttempts']) {
            $user['login_attempt_failure']++;
            if ($user['login_attempt_failure'] >= $configuration['authenticationFailureAttempts']) {
                $blockingTime = $this->loginUtility->getLoginBlockingTime($user['login_attempt_failure']);
                $user['login_blocked_endtime'] = time() + $blockingTime * 60;
            }
        }

        // User update
        $this->databaseConnection->exec_UPDATEquery(
            'fe_users',
            'uid = ' . $this->databaseConnection->fullQuoteStr($user['uid'], 'fe_users'),
            array(
                'login_attempt_failure' => $user['login_attempt_failure'],
                'login_blocked_endtime' => $user['login_blocked_endtime']
            )
        );
    }

    public function resetLoginAttemptFailure(&$user)
    {
        // User update
        $this->databaseConnection->exec_UPDATEquery(
            'fe_users',
            'uid = ' . $this->databaseConnection->fullQuoteStr($user['uid'], 'fe_users'),
            array(
                'login_attempt_failure' => 0,
                'login_blocked_endtime' => 0
            )
        );
    }
}