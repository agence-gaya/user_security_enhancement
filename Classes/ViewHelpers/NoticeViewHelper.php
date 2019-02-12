<?php
namespace GAYA\UserSecurityEnhancement\ViewHelpers;

/**
 * Class NoticeViewHelper
 *
 * @package GAYA\UserSecurityEnhancement\ViewHelpers
 */
class NoticeViewHelper extends \TYPO3\CMS\Fluid\Core\ViewHelper\AbstractViewHelper
{

    /**
     * noticeUtility
     *
     * @var \GAYA\UserSecurityEnhancement\Utility\NoticeUtility
     * @inject
     */
    protected $noticeUtility = NULL;

    public function render()
    {
        return $this->noticeUtility->getNotice();
    }

}
