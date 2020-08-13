<?php
namespace GAYA\UserSecurityEnhancement\Utility;

use TYPO3\CMS\Core\Crypto\PasswordHashing\PasswordHashFactory;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;

/**
 * Class PasswordUtility
 *
 * @package GAYA\UserSecurityEnhancement\Utility
 */
class PasswordUtility implements SingletonInterface
{

	/**
	 * @var \GAYA\UserSecurityEnhancement\Domain\Repository\FrontendUserRepository
	 */
	protected $frontendUserRepository;

    /**
     * configurationUtility
     *
     * @var \GAYA\UserSecurityEnhancement\Utility\ConfigurationUtility
     */
    protected $configurationUtility = NULL;

    public function __construct()
    {
        $this->configurationUtility = GeneralUtility::makeInstance(\GAYA\UserSecurityEnhancement\Utility\ConfigurationUtility::class);

        $objectManager = GeneralUtility::makeInstance(ObjectManager::class);
        $this->frontendUserRepository = $objectManager->get(\GAYA\UserSecurityEnhancement\Domain\Repository\FrontendUserRepository::class);
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
	 * This version is compatible with the extbase version of felogin
	 *
	 * @param string $forgotPasswordHash
	 * @param string $password
	 * @return bool
	 */
    public function checkPasswordHistory($forgotPasswordHash, $password)
	{
		$passwordHistoryNumber = $this->configurationUtility->getConfiguration('passwordHistory');
		if ($passwordHistoryNumber) {
			$oldPasswordList = $this->frontendUserRepository->findOldPasswordListByFeloginForgotHash($forgotPasswordHash);
			if ($oldPasswordList) {
				$oldPasswords = GeneralUtility::trimExplode(';', $oldPasswordList);
				$hashInstance = GeneralUtility::makeInstance(PasswordHashFactory::class)
						->getDefaultHashInstance('FE');
				if (is_object($hashInstance)) {
					foreach ($oldPasswords as $oldPassword) {
						if ($hashInstance->checkPassword($password, $oldPassword)) {
							return false;
						}
					}
				}
			}
		}

		return true;
	}

	/**
	 * Returns the updated password history
	 *
	 * @param string $forgotPasswordHash
	 * @param string $hashedPassword
	 * @return string
	 */
	public function getUpdatedPasswordHistory($forgotPasswordHash, $hashedPassword)
	{
		$passwordHistory = [];
		$table = 'fe_users';

		$connection = GeneralUtility::makeInstance(ConnectionPool::class)->getConnectionForTable($table);
		$queryBuilder = $connection->createQueryBuilder();
		$row = $queryBuilder
			->select('old_password_list')
			->from($table)
			->where(
				$queryBuilder->expr()->eq('felogin_forgotHash', $queryBuilder->createNamedParameter($forgotPasswordHash))
			)
			->execute()
			->fetch();

		if (isset($row['old_password_list']) && strlen($row['old_password_list'])) {
			$passwordHistory = GeneralUtility::trimExplode(';', $row['old_password_list']);
		}
		$passwordHistory[] = $hashedPassword;

		/** @var \GAYA\UserSecurityEnhancement\Utility\ConfigurationUtility $configurationUtility */
		$configurationUtility = GeneralUtility::makeInstance(\GAYA\UserSecurityEnhancement\Utility\ConfigurationUtility::class);
		$passwordHistoryNumber = $configurationUtility->getConfiguration('passwordHistory');
		$passwordHistory = array_slice($passwordHistory, -$passwordHistoryNumber);

		return implode(';', $passwordHistory);
	}
}
