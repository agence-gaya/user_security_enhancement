<?php
namespace GAYA\UserSecurityEnhancement\Service;

use TYPO3\CMS\Core\Database\ConnectionPool;
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
        $connection = GeneralUtility::makeInstance(ConnectionPool::class)->getConnectionForTable('fe_users');
        $queryBuilder = $connection->createQueryBuilder();
        $queryBuilder
			->update('fe_users')
			->set('login_attempt_failure', $user['login_attempt_failure'])
			->set('login_blocked_endtime', $user['login_blocked_endtime'])
			->where(
				$queryBuilder->expr()->eq('uid', $queryBuilder->createNamedParameter($user['uid'], \PDO::PARAM_INT))
			)
            ->execute();
    }

    public function resetLoginAttemptFailure(&$user)
    {
        // User update
		$connection = GeneralUtility::makeInstance(ConnectionPool::class)->getConnectionForTable('fe_users');
		$queryBuilder = $connection->createQueryBuilder();
		$queryBuilder
			->update('fe_users')
			->set('login_attempt_failure', 0)
			->set('login_blocked_endtime', 0)
			->where(
				$queryBuilder->expr()->eq('uid', $queryBuilder->createNamedParameter($user['uid'], \PDO::PARAM_INT))
			)
            ->execute();
	}
}