<?php
namespace GAYA\UserSecurityEnhancement\Controller;

use GAYA\UserSecurityEnhancement\Domain\Repository\FrontendUserRepository;
use GAYA\UserSecurityEnhancement\Service\FrontendSessionService;
use Psr\EventDispatcher\EventDispatcherInterface;
use TYPO3\CMS\Core\Context\Exception\AspectNotFoundException;
use TYPO3\CMS\Core\Crypto\PasswordHashing\InvalidPasswordHashException;
use TYPO3\CMS\Core\Crypto\PasswordHashing\PasswordHashFactory;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Error\Error;
use TYPO3\CMS\Extbase\Error\Result;
use TYPO3\CMS\Extbase\Mvc\Exception\StopActionException;
use TYPO3\CMS\Extbase\Mvc\Exception\UnsupportedRequestTypeException;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;
use TYPO3\CMS\FrontendLogin\Service\RecoveryServiceInterface;

/**
 * Class PasswordRecoveryController
 * @package GAYA\UserSecurityEnhancement\Controller
 */
class PasswordRecoveryController extends \TYPO3\CMS\FrontendLogin\Controller\PasswordRecoveryController
{

    /**
     * @var FrontendUserRepository
     */
    protected $userRepository;

    /**
     * PasswordRecoveryController constructor.
     * @param EventDispatcherInterface $eventDispatcher
     * @param RecoveryServiceInterface $recoveryService
     * @param \TYPO3\CMS\FrontendLogin\Domain\Repository\FrontendUserRepository $userRepository
     */
    public function __construct(
        EventDispatcherInterface $eventDispatcher,
        RecoveryServiceInterface $recoveryService,
        \TYPO3\CMS\FrontendLogin\Domain\Repository\FrontendUserRepository $userRepository
    )
    {
        parent::__construct($eventDispatcher, $recoveryService, $userRepository);

        $objectManager = GeneralUtility::makeInstance(ObjectManager::class);
        $this->userRepository = $objectManager->get(FrontendUserRepository::class);
    }

    /**
	 * @param Result $originalResult
	 */
	protected function validateNewPassword(Result $originalResult): void
	{
		parent::validateNewPassword($originalResult);

		$originalResult = $this->request->getOriginalRequestMappingResults();

		$newPass = $this->request->getArgument('newPass');
		$hash = $this->request->getArgument('hash');
		if (!GeneralUtility::makeInstance(\GAYA\UserSecurityEnhancement\Utility\PasswordUtility::class)
			->checkPasswordHistory(GeneralUtility::hmac($hash), $newPass)
		) {
			$originalResult->addError(new Error(LocalizationUtility::translate('history.password.alreadyUsed', 'user_security_enhancement'), 1554935958));
		}

		//set the result from all validators
		$this->request->setOriginalRequestMappingResults($originalResult);
	}

	/**
	 * Change actual password. Hash $newPass and update the user with the corresponding $hash.
	 *
	 * @param string $newPass
	 * @param string $hash
	 *
	 * @throws InvalidPasswordHashException
	 * @throws StopActionException
	 * @throws UnsupportedRequestTypeException
	 * @throws AspectNotFoundException
	 */
	public function changePasswordAction(string $newPass, string $hash): void
	{
		$hashedPassword = GeneralUtility::makeInstance(PasswordHashFactory::class)
			->getDefaultHashInstance('FE')
			->getHashedPassword($newPass);

		$hashedPassword = $this->notifyPasswordChange($newPass, $hashedPassword, $hash);
		$forgotPasswordHash = GeneralUtility::hmac($hash);

		/** @var \GAYA\UserSecurityEnhancement\Utility\PasswordUtility $passwordUtility */
		$passwordUtility = GeneralUtility::makeInstance(\GAYA\UserSecurityEnhancement\Utility\PasswordUtility::class);
		$passwordHistory = $passwordUtility->getUpdatedPasswordHistory($forgotPasswordHash, $hashedPassword);

        $user = $this->userRepository->findOneByForgotPasswordHash($forgotPasswordHash);
		$this->userRepository->updatePasswordAndPasswordHistoryAndInvalidateHash($forgotPasswordHash, $hashedPassword, $passwordHistory);

		$this->addFlashMessage($this->getTranslation('change_password_done_message'));

        // The password has been successfully changed, all opened sessions of the user gonna be deleted
        if ($user['uid']) {
            /** @var FrontendSessionService $frontendSessionService */
            $frontendSessionService = GeneralUtility::makeInstance(FrontendSessionService::class);
            $frontendSessionService->deleteUserSessions($user['uid']);
        }

		$this->redirect('login', 'Login', 'felogin');
	}

}