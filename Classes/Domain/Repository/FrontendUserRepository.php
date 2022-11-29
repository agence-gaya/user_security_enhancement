<?php

declare(strict_types=1);

namespace GAYA\UserSecurityEnhancement\Domain\Repository;

/**
 * Class FrontendUserRepository
 * @package GAYA\UserSecurityEnhancement\Domain\Repository
 */
class FrontendUserRepository extends \TYPO3\CMS\FrontendLogin\Domain\Repository\FrontendUserRepository
{
    /**
     * @param string $feloginForgotHash
     * @return string|null
     */
    public function findOldPasswordListByFeloginForgotHash(string $feloginForgotHash)
    {
        $queryBuilder = $this->connection->createQueryBuilder();
        $query = $queryBuilder
            ->select('old_password_list')
            ->from($this->getTable())
            ->where(
                $queryBuilder->expr()->eq('felogin_forgotHash', $queryBuilder->createNamedParameter($feloginForgotHash))
            )
        ;

        $column = $query->execute()->fetchColumn();
        return $column === false || $column === '' ? null : (string)$column;
    }

    /**
     * Change the password for a user and update his password history based on forgot password hash.
     *
     * @param string $forgotPasswordHash
     * @param string $hashedPassword
     * @param string $passwordHistory
     *
     * @throws \TYPO3\CMS\Core\Context\Exception\AspectNotFoundException
     */
    public function updatePasswordAndPasswordHistoryAndInvalidateHash(string $forgotPasswordHash, string $hashedPassword, string $passwordHistory)
    {
        $queryBuilder = $this->connection->createQueryBuilder();

        $currentTimestamp = $this->context->getPropertyFromAspect('date', 'timestamp');
        $query = $queryBuilder
            ->update($this->getTable())
            ->set('password', $hashedPassword)
            ->set('felogin_forgotHash', $this->connection->quote(''), false)
            ->set('tstamp', $currentTimestamp)
            ->set('old_password_list', $passwordHistory)
            ->where(
                $queryBuilder->expr()->eq('felogin_forgotHash', $queryBuilder->createNamedParameter($forgotPasswordHash))
            )
        ;
        $query->execute();
    }

    /**
     * @param string $username
     * @return array|null
     */
    public function findOneByUsername(string $username)
    {
        if ($username === '') {
            return null;
        }

        $queryBuilder = $this->connection->createQueryBuilder();
        $query = $queryBuilder
            ->select('*')
            ->from($this->getTable())
            ->where(
                $queryBuilder->expr()->eq('username', $queryBuilder->createNamedParameter($username))
            )
            ->setMaxResults(1);

        $row = $query->execute()->fetch();
        return is_array($row) ? $row : null;
    }
}
