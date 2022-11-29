<?php

declare(strict_types=1);

namespace GAYA\UserSecurityEnhancement\Service;

use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use GAYA\UserSecurityEnhancement\Utility\ConfigurationUtility;
use GAYA\UserSecurityEnhancement\Utility\LoginUtility;
use PDO;

class FrontendLoginService implements SingletonInterface
{
    /**
     * configurationUtility
     *
     * @var ConfigurationUtility
     */
    protected $configurationUtility = null;

    /**
     * @var LoginUtility
     */
    protected $loginUtility;

    public function __construct()
    {
        $this->configurationUtility = GeneralUtility::makeInstance(ConfigurationUtility::class);
        $this->loginUtility = GeneralUtility::makeInstance(LoginUtility::class);
    }

    /**
     * @param array $user
     */
    public function updateLoginAttemptFailure(array &$user): void
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
                $queryBuilder->expr()->eq('uid', $queryBuilder->createNamedParameter($user['uid'], PDO::PARAM_INT))
            )
            ->execute();
    }

    /**
     * @param array $user
     */
    public function resetLoginAttemptFailure(array &$user): void
    {
        // User update
        $connection = GeneralUtility::makeInstance(ConnectionPool::class)->getConnectionForTable('fe_users');
        $queryBuilder = $connection->createQueryBuilder();
        $queryBuilder
            ->update('fe_users')
            ->set('login_attempt_failure', 0)
            ->set('login_blocked_endtime', 0)
            ->where(
                $queryBuilder->expr()->eq('uid', $queryBuilder->createNamedParameter($user['uid'], PDO::PARAM_INT))
            )
            ->execute();
    }
}
