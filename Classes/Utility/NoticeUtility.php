<?php
namespace GAYA\UserSecurityEnhancement\Utility;

use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Class NoticeUtility
 *
 * @package GAYA\UserSecurityEnhancement\Utility
 */
class NoticeUtility implements SingletonInterface
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
     * Get the notice policies
     * @return string
     */
    public function getNotice()
    {
        $configuration = $this->configurationUtility->getConfiguration();

        $text = \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate('notice.password', 'userSecurityEnhancement') . '<ul>';

        if ($configuration['passwordLength']) {
            $text .= '<li>' . \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate('notice.password.length', 'userSecurityEnhancement', array($configuration['passwordLength'])) . '</li>';
        }
        if ($configuration['capitalLettersNumber']) {
            $text .= '<li>' . \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate('notice.password.capitalLettersNumber', 'userSecurityEnhancement', array($configuration['capitalLettersNumber'])) . '</li>';
        }
        if ($configuration['tinyLettersNumber']) {
            $text .= '<li>' . \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate('notice.password.tinyLettersNumber', 'userSecurityEnhancement', array($configuration['tinyLettersNumber'])) . '</li>';
        }
        if ($configuration['specialCharactersNumber']) {
            $text .= '<li>' . \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate('notice.password.specialCharactersNumber', 'userSecurityEnhancement', array($configuration['specialCharactersNumber'])) . '</li>';
        }
        if ($configuration['digitsNumber']) {
            $text .= '<li>' . \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate('notice.password.digitsNumber', 'userSecurityEnhancement', array($configuration['digitsNumber'])) . '</li>';
        }

        $text .= '</ul>';

        return $text;
    }
}
