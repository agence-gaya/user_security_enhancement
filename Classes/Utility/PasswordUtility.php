<?php
namespace GAYA\UserSecurityEnhancement\Utility;

use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Class PasswordUtility
 *
 * @package GAYA\UserSecurityEnhancement\Utility
 */
class PasswordUtility implements SingletonInterface
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
     * Vérify if password is valid
     *
     * @param string $password
     * @return bool
     */
    public function checkPasswordValidity($password)
    {
        $configuration = $this->configurationUtility->getConfiguration();

        if (!preg_match('/[a-z]{' . $configuration['tinyLettersNumber'] . ',}/', $password)
            || !preg_match('/[A-Z]{' . $configuration['capitalLettersNumber'] . ',}/', $password)
            || !preg_match('/[\W_]{' . $configuration['specialCharactersNumber'] . ',}/', $password)
            || !preg_match('/[1-9]{' . $configuration['digitsNumber'] . ',}/', $password)
            || strlen($password) < $configuration['passwordLength']) {

            return false;
        }
        return true;
    }

    /**
     * Vérify if password is in password history
     *
     * @param int $userId
     * @param string $password
     * @return bool
     */
    public function checkPasswordHistory($userId, $password)
    {
        $passwordHistoryNumber = $this->configurationUtility->getConfiguration('passwordHistory');

        if ($passwordHistoryNumber) {
            $res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('old_password_list', 'fe_users', 'uid = ' . (int)$userId, '', '', 1);
            $user = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res);
            if ($user) {
                $oldPasswords = GeneralUtility::trimExplode(';', $user['old_password_list']);
                foreach ($oldPasswords as $oldPassword) {
                    $objInstanceSaltedPW = \TYPO3\CMS\Saltedpasswords\Salt\SaltFactory::getSaltingInstance($oldPassword);

                    if (is_object($objInstanceSaltedPW)) {
                        if ($objInstanceSaltedPW->checkPassword($password, $oldPassword)) {
                            return false;
                        }
                    }
                }
            }
        }

        return true;
    }
}
