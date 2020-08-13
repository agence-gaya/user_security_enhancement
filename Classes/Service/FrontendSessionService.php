<?php
namespace GAYA\UserSecurityEnhancement\Service;

use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\QueryBuilder;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class FrontendSessionService implements SingletonInterface
{
    /**
     * @var \TYPO3\CMS\Core\Database\DatabaseConnection
     */
    protected $databaseConnection = null;

    public function __construct()
    {
        $this->databaseConnection = $GLOBALS['TYPO3_DB'];
    }
    /**
     * @param int $userUid
     */
    public function deleteUserSessions($userUid = null)
    {
        $connectionFeSessions = GeneralUtility::makeInstance(ConnectionPool::class)->getConnectionForTable('fe_sessions');

        if ($userUid) {
            /** @var QueryBuilder $queryBuilder */
            $queryBuilder = $connectionFeSessions->createQueryBuilder();
            $statement = $queryBuilder
                ->select('*')
                ->from('fe_sessions')
                ->where(
                    $queryBuilder->expr()->eq('ses_userid', $queryBuilder->createNamedParameter($userUid, \PDO::PARAM_INT))
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
                    $queryBuilder->expr()->eq('ses_userid', $queryBuilder->createNamedParameter($GLOBALS['TSFE']->fe_user->user['uid'], \PDO::PARAM_INT))
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
     * @param $sessionId
     * @return mixed
     */
    protected function deleteSession($sessionId)
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