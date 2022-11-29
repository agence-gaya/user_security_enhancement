<?php

declare(strict_types=1);

namespace GAYA\UserSecurityEnhancement\Middleware;

use GAYA\UserSecurityEnhancement\Service\FrontendLoginService;
use GAYA\UserSecurityEnhancement\Utility\LoginUtility;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use TYPO3\CMS\Core\Authentication\AbstractUserAuthentication;
use TYPO3\CMS\Core\Authentication\LoginType;
use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Core\Context\UserAspect;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\QueryBuilder;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Frontend\Authentication\FrontendUserAuthentication;

/**
 * Class FrontendUserSecurity
 * @package GAYA\UserSecurityEnhancement\Middleware
 */
class FrontendUserSecurity implements MiddlewareInterface
{
    /**
     * Login type, used for services.
     * @var string
     */
    public $loginType = 'FE';

    /**
     * Form field with login-name
     * @var string
     */
    public $formfield_uname = 'user';

    /**
     * Form field with password
     * @var string
     */
    public $formfield_uident = 'pass';

    /**
     * Form field with status: *'login', 'logout'. If empty login is not verified.
     * @var string
     */
    public $formfield_status = 'logintype';

    /**
     * @param ServerRequestInterface $request
     * @param RequestHandlerInterface $handler
     * @return ResponseInterface
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $loginData = $this->getLoginFormData();

        if ($loginData['status'] === LoginType::LOGIN && $loginData['uname']) {
            // Load the user
            $user = $this->loadFeUserByUsername($loginData['uname']);

            if ($user['uid']) {
                /** @var FrontendLoginService $frontendLoginService */
                $frontendLoginService = GeneralUtility::makeInstance(FrontendLoginService::class);

                $frontendUser = $request->getAttribute('frontend.user');
                if ($frontendUser instanceof FrontendUserAuthentication && !$frontendUser->loginFailure) {
                    /** @var LoginUtility $loginUtility */
                    $loginUtility = GeneralUtility::makeInstance(LoginUtility::class);

                    if ($loginUtility->isUserBlocked($user)) {
                        // Auth is OK but the user is blocked
                        $frontendLoginService->updateLoginAttemptFailure($user);
                        $frontendUser->logoff();
                        $this->setFrontendUserAspect($frontendUser);
                        $request = $request->withAttribute('frontend.user', $frontendUser);
                    } else {
                        // Auth is OK, security fields are reset
                        $frontendLoginService->resetLoginAttemptFailure($user);
                    }
                } else {
                    // Auth failure. Update user security fields
                    $frontendLoginService->updateLoginAttemptFailure($user);
                }
            }
        }

        return $handler->handle($request);
    }

    /**
     * @return array
     */
    protected function getLoginFormData(): array
    {
        $loginData = [
            'status' => GeneralUtility::_GP($this->formfield_status),
            'uname'  => GeneralUtility::_POST($this->formfield_uname),
            'uident' => GeneralUtility::_POST($this->formfield_uident),
        ];

        return $loginData;
    }

    /**
     * @param AbstractUserAuthentication $user
     */
    protected function setFrontendUserAspect(AbstractUserAuthentication $user): void
    {
        $context = GeneralUtility::makeInstance(Context::class);
        $context->setAspect('frontend.user', GeneralUtility::makeInstance(UserAspect::class, $user));
    }

    /**
     * @param string $username
     * @return array|null
     */
    protected function loadFeUserByUsername(string $username)
    {
        /** @var QueryBuilder $queryBuilder */
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable('fe_users');

        $row = $queryBuilder
            ->select('*')
            ->from('fe_users')
            ->where(
                $queryBuilder->expr()->eq('username', $queryBuilder->createNamedParameter($username)),
            )
            ->setMaxResults(1)
            ->execute()
            ->fetch();

        return is_array($row) ? $row : null;
    }
}
