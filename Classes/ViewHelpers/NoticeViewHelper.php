<?php
namespace GAYA\UserSecurityEnhancement\ViewHelpers;

use GAYA\UserSecurityEnhancement\Utility\NoticeUtility;

/**
 * Class NoticeViewHelper
 *
 * @package GAYA\UserSecurityEnhancement\ViewHelpers
 */
class NoticeViewHelper extends \TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper
{
    /**
     * noticeUtility
     *
     * @var NoticeUtility
     * @TYPO3\CMS\Extbase\Annotation\Inject
     */
    protected $noticeUtility = NULL;

    public function render()
    {
        return $this->noticeUtility->getNotice();
    }

}
