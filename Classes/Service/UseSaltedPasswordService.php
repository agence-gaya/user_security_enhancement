<?php
namespace GAYA\UserSecurityEnhancement\Service;

use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Class UseSaltedPasswordService
 *
 * @package GAYA\UserSecurityEnhancement\Service
 */
class UseSaltedPasswordService extends \TYPO3\CMS\Saltedpasswords\SaltedPasswordService
{

    /**
     * Method adds a further authUser method.
     *
     * Will return one of following authentication status codes:
     * - 0 - authentication failure
     * - 100 - just go on. User is not authenticated but there is still no reason to stop
     * - 200 - the service was able to authenticate the user
     *
     * @param array Array containing FE user data of the logged user.
     * @return int Authentication statuscode, one of 0,100 and 200
     */
    public function authUser(array $user)
    {
        /** @var \GAYA\UserSecurityEnhancement\Service\FrontendLoginService $frontendLoginService */
        $frontendLoginService = GeneralUtility::makeInstance(\GAYA\UserSecurityEnhancement\Service\FrontendLoginService::class);

        /** @var \GAYA\UserSecurityEnhancement\Utility\LoginUtility $loginUtility */
        $loginUtility = GeneralUtility::makeInstance(\GAYA\UserSecurityEnhancement\Utility\LoginUtility::class);

        $OK = parent::authUser($user);
        if ($OK) {
            if ($OK == 200) {
                // The auth is OK but the user is locked
                if ($loginUtility->isUserBlocked($user)) {
                    $frontendLoginService->updateLoginAttemptFailure($user);
                    $OK = 0;
                } else {
                    $frontendLoginService->resetLoginAttemptFailure($user);
                }
            }
        } else {
            // Update the user failure data
            $frontendLoginService->updateLoginAttemptFailure($user);
        }

        return $OK;
    }

}