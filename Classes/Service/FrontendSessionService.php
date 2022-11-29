<?php

declare(strict_types=1);

namespace GAYA\UserSecurityEnhancement\Service;

use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\QueryBuilder;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use PDO;

class FrontendSessionService implements SingletonInterface
{
    /**
     * @param int|null $userUid
     */
    public function deleteUserSessions(int $userUid = null): void
    {
        $connectionFeSessions = GeneralUtility::makeInstance(ConnectionPool::class)->getConnectionForTable('fe_sessions');

        if ($userUid !== null) {
            /** @var QueryBuilder $queryBuilder */
            $queryBuilder = $connectionFeSessions->createQueryBuilder();
            $statement = $queryBuilder
                ->select('*')
                ->from('fe_sessions')
                ->where(
                    $queryBuilder->expr()->eq('ses_userid', $queryBuilder->createNamedParameter($userUid, PDO::PARAM_INT))
                )
                ->execute();

            while ($rec = $statement->fetch()) {
                // Remove user's session
                $this->deleteSession($rec['ses_id']);
            }
        } elseif (isset($GLOBALS['TSFE']->fe_user->user)) {
            /** @var QueryBuilder $queryBuilder */
            $queryBuilder = $connectionFeSessions->createQueryBuilder();
            $statement = $queryBuilder
                ->select('*')
                ->from('fe_sessions')
                ->where(
                    $queryBuilder->expr()->eq('ses_userid', $queryBuilder->createNamedParameter($GLOBALS['TSFE']->fe_user->user['uid'], PDO::PARAM_INT))
                )
                ->execute();

            while ($rec = $statement->fetch()) {
                // Remove user's session
                $this->deleteSession($rec['ses_id']);
            }
        }
    }

    /**
     * Delete session of a frontend user
     *
     * @param string $sessionId
     * @return \Doctrine\DBAL\Driver\Statement|int
     */
    protected function deleteSession(string $sessionId)
    {
        /** @var QueryBuilder $queryBuilder */
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getConnectionForTable('fe_sessions')->createQueryBuilder();
        return $queryBuilder
            ->delete('fe_sessions')
            ->where(
                $queryBuilder->expr()->eq('ses_id', $queryBuilder->createNamedParameter($sessionId))
            )
            ->execute();
    }
}
