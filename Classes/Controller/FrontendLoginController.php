<?php
namespace GAYA\UserSecurityEnhancement\Controller;

use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Plugin 'Website User Login' for the 'felogin' extension.
 */
class FrontendLoginController extends \TYPO3\CMS\Felogin\Controller\FrontendLoginController
{

    /**
     * This function checks the hash from link and checks the validity. If it's valid it shows the form for
     * changing the password and process the change of password after submit, if not valid it returns the error message
     *
     * @return string The content.
     */
    protected function changePassword()
    {
        /** @var \GAYA\UserSecurityEnhancement\Utility\NoticeUtility $noticeUtility */
        $noticeUtility = GeneralUtility::makeInstance(\GAYA\UserSecurityEnhancement\Utility\NoticeUtility::class);
        $subpartArray = ($linkpartArray = []);
        $done = false;
        $minLength = (int)$this->conf['newPasswordMinLength'] ?: 6;
        $subpart = $this->cObj->getSubpart($this->template, '###TEMPLATE_CHANGEPASSWORD###');
        $markerArray['###STATUS_HEADER###'] = $this->getDisplayText('change_password_header', $this->conf['changePasswordHeader_stdWrap.']);
        $markerArray['###STATUS_MESSAGE###'] = sprintf($this->getDisplayText(
            'change_password_message',
            $this->conf['changePasswordMessage_stdWrap.']
        ), $minLength);

        $markerArray['###BACKLINK_LOGIN###'] = '';
        $uid = $this->piVars['user'];
        $piHash = $this->piVars['forgothash'];
        $hash = explode('|', rawurldecode($piHash));
        if ((int)$uid === 0) {
            $markerArray['###STATUS_MESSAGE###'] = $this->getDisplayText(
                'change_password_notvalid_message',
                $this->conf['changePasswordNotValidMessage_stdWrap.']
            );
            $subpartArray['###CHANGEPASSWORD_FORM###'] = '';
        } else {
            $user = $this->pi_getRecord('fe_users', (int)$uid);
            $userHash = $user['felogin_forgotHash'];
            $compareHash = explode('|', $userHash);
            if (!$compareHash || !$compareHash[1] || $compareHash[0] < time() || $hash[0] != $compareHash[0] || md5($hash[1]) != $compareHash[1]) {
                $markerArray['###STATUS_MESSAGE###'] = $this->getDisplayText(
                    'change_password_notvalid_message',
                    $this->conf['changePasswordNotValidMessage_stdWrap.']
                );
                $subpartArray['###CHANGEPASSWORD_FORM###'] = '';
            } else {
                // All is fine, continue with new password
                $postData = GeneralUtility::_POST($this->prefixId);
                if (isset($postData['changepasswordsubmit'])) {
                    /** @var \GAYA\UserSecurityEnhancement\Utility\PasswordUtility $passwordUtility */
                    $passwordUtility = GeneralUtility::makeInstance(\GAYA\UserSecurityEnhancement\Utility\PasswordUtility::class);
                    if (!$passwordUtility->checkPasswordValidity($postData['password1'])) {
                        $markerArray['###STATUS_MESSAGE###'] = $this->cObj->stdWrap(
                            \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate('validator.password.notvalid', 'userSecurityEnhancement'),
                            $this->conf['changePasswordTooShortMessage_stdWrap.']
                        );
                    } elseif ($postData['password1'] != $postData['password2']) {
                        $markerArray['###STATUS_MESSAGE###'] = sprintf($this->getDisplayText(
                            'change_password_notequal_message',
                            $this->conf['changePasswordNotEqualMessage_stdWrap.']),
                            $minLength
                        );
                    } elseif (!$passwordUtility->checkPasswordHistory($user['uid'], $postData['password1'])) {
                        $markerArray['###STATUS_MESSAGE###'] = $this->cObj->stdWrap(
                            \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate('history.password.alreadyUsed', 'userSecurityEnhancement'),
                            $this->conf['changePasswordTooShortMessage_stdWrap.']
                        );
                    } else {
                        $newPass = $postData['password1'];
                        if ($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['felogin']['password_changed']) {
                            $_params = [
                                'user' => $user,
                                'newPassword' => $newPass,
                                'newPasswordUnencrypted' => $newPass
                            ];
                            foreach ($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['felogin']['password_changed'] as $_funcRef) {
                                if ($_funcRef) {
                                    GeneralUtility::callUserFunction($_funcRef, $_params, $this);
                                }
                            }
                            $newPass = $_params['newPassword'];
                        }
                        /** @var \GAYA\UserSecurityEnhancement\Utility\ConfigurationUtility $configurationUtility */
                        $configurationUtility = GeneralUtility::makeInstance(\GAYA\UserSecurityEnhancement\Utility\ConfigurationUtility::class);
                        $passwordHistoryNumber = $configurationUtility->getConfiguration('passwordHistory');

                        if ($user['old_password_list']) {
                            $passwordHistory = GeneralUtility::trimExplode(';', $user['old_password_list']);
                        } else {
                            $passwordHistory = array();
                        }

                        $passwordHistory[] = $user['password'];
                        $passwordHistory = array_slice($passwordHistory, -$passwordHistoryNumber);

                        // Save new password and clear DB-hash
                        $res = $this->databaseConnection->exec_UPDATEquery(
                            'fe_users',
                            'uid=' . $user['uid'],
                            ['password' => $newPass, 'felogin_forgotHash' => '', 'tstamp' => $GLOBALS['EXEC_TIME'], 'old_password_list' => implode(';', $passwordHistory)]
                        );
                        $markerArray['###STATUS_MESSAGE###'] = $this->getDisplayText(
                            'change_password_done_message',
                            $this->conf['changePasswordDoneMessage_stdWrap.']
                        );
                        $done = true;

                        // The password has been successfully changed, all opened sessions of the user gonna be deleted
                        /** @var \GAYA\UserSecurityEnhancement\Service\FrontendSessionService $frontendSessionService */
                        $frontendSessionService = GeneralUtility::makeInstance(\GAYA\UserSecurityEnhancement\Service\FrontendSessionService::class);
                        $frontendSessionService->deleteUserSessions($uid);

                        $subpartArray['###CHANGEPASSWORD_FORM###'] = '';
                        $markerArray['###BACKLINK_LOGIN###'] = $this->getPageLink(
                            $this->pi_getLL('ll_forgot_header_backToLogin', '', true),
                            [$this->prefixId . '[redirectReferrer]' => 'off']
                        );
                    }
                }
                if (!$done) {
                    // Change password form
                    $markerArray['###ACTION_URI###'] = $this->getPageLink('', [
                        $this->prefixId . '[user]' => $user['uid'],
                        $this->prefixId . '[forgothash]' => $piHash
                    ], true);
                    $markerArray['###LEGEND###'] = $this->pi_getLL('change_password', '', true);
                    $markerArray['###NEWPASSWORD1_LABEL###'] = $this->pi_getLL('newpassword_label1', '', true);
                    $markerArray['###NEWPASSWORD2_LABEL###'] = $this->pi_getLL('newpassword_label2', '', true);
                    $markerArray['###NEWPASSWORD1###'] = $this->prefixId . '[password1]';
                    $markerArray['###HELP_BLOCK###'] = $noticeUtility->getNotice();
                    $markerArray['###NEWPASSWORD2###'] = $this->prefixId . '[password2]';
                    $markerArray['###STORAGE_PID###'] = $this->spid;
                    $markerArray['###SEND_PASSWORD###'] = $this->pi_getLL('change_password', '', true);
                    $markerArray['###FORGOTHASH###'] = $piHash;
                }
            }
        }
        return $this->cObj->substituteMarkerArrayCached($subpart, $markerArray, $subpartArray, $linkpartArray);
    }

}
