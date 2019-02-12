<?php
namespace GAYA\UserSecurityEnhancement\Utility;

use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Class LoginUtility
 *
 * @package GAYA\UserSecurityEnhancement\Utility
 */
class LoginUtility implements SingletonInterface
{

    /**
     * configurationUtility
     *
     * @var \GAYA\UserSecurityEnhancement\Utility\ConfigurationUtility
     */
    protected $configurationUtility = NULL;

    public function __construct()
    {
        $this->configurationUtility = GeneralUtility::makeInstance(\GAYA\UserSecurityEnhancement\Utility\ConfigurationUtility::class);
    }

    /**
     * @param $user
     * @return bool
     */
    public function isUserBlocked($user)
    {
        $configuration = $this->configurationUtility->getConfiguration();

        if ($configuration['authenticationFailureAttempts']) {
            if (
                $user['login_attempt_failure'] >= $configuration['authenticationFailureAttempts']
                && $user['login_blocked_endtime'] > time()
            ) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param int $loginAttempt
     * @return float|int Blocking time in minutes
     */
    public function getLoginBlockingTime($loginAttempt)
    {
        $configuration = $this->configurationUtility->getConfiguration();

        $waitAt = $configuration['authenticationFailureAttempts']; // Number of attempts before the user is locked
        $waitTime = $configuration['authenticationFailureLock']; // Lock duration in minutes
        $waitMax = $configuration['authenticationFailureMaxLock']; // Maximum lock duration

        if ($loginAttempt < $waitAt) {
            return 0;
        }

        // We use bit shifting for calculate blocking time :
        // $waitTime x 1 / $waitTime x 2 / $waitTime x 4 / $waitTime x 8 / $waitTime x 16 / ...
        $wait = (1 << ($loginAttempt - $waitAt)) * $waitTime;
        if ($waitMax > 0 && ($wait <= 0 || $wait > $waitMax)) {
            $wait = $waitMax;
        }

        return $wait;
    }

}
