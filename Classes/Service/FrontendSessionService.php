<?php
namespace GAYA\UserSecurityEnhancement\Service;

use TYPO3\CMS\Core\SingletonInterface;

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
        if ($userUid) {
            // Suppression de l'ensemble des sessions de l'utilisateur correspondant à l'identifiant.
            $res = $this->databaseConnection->exec_SELECTquery(
                '*',
                'fe_sessions',
                'ses_userid = ' . $this->databaseConnection->fullQuoteStr($userUid, 'fe_users')
            );

            while ($rec = $this->databaseConnection->sql_fetch_assoc($res)) {
                // Suppression des data de la session
                $this->deleteSessionData($rec['ses_id']);

                // Suppression de la session
                $this->deleteSession($rec['ses_id']);
            }
        } elseif (isset($GLOBALS['TSFE']->fe_user->user)) {
            // Si l'utilisateur est connecté, suppression de toutes ses sessions sauf celle en cours.
            $res = $this->databaseConnection->exec_SELECTquery(
                '*',
                'fe_sessions',
                'ses_userid = ' . $this->databaseConnection->fullQuoteStr($GLOBALS['TSFE']->fe_user->user['uid'], 'fe_users') . ' AND ses_id != ' . $this->databaseConnection->fullQuoteStr($GLOBALS['TSFE']->fe_user->user['ses_id'], 'fe_sessions')
            );

            while ($rec = $this->databaseConnection->sql_fetch_assoc($res)) {
                // Suppression des data de la session
                $this->deleteSessionData($rec['ses_id']);

                // Suppression de la session
                $this->deleteSession($rec['ses_id']);
            }
        }
    }

    /**
     * Delete session data of a frontend user
     *
     * @param $sessionId
     * @return mixed
     */
    protected function deleteSessionData($sessionId)
    {
        // Suppression des data de la session
        $this->databaseConnection->exec_DELETEquery(
            'fe_session_data',
            "hash = " . $this->databaseConnection->fullQuoteStr($sessionId, 'fe_sessions')
        );

        return $this->databaseConnection->sql_affected_rows();
    }

    /**
     * Delete session of a frontend user
     *
     * @param $sessionId
     * @return mixed
     */
    protected function deleteSession($sessionId)
    {
        $this->databaseConnection->exec_DELETEquery(
            'fe_sessions',
            "ses_id = " . $this->databaseConnection->fullQuoteStr($sessionId, 'fe_sessions')
        );

        return $this->databaseConnection->sql_affected_rows();
    }

}