<?php
/**
 * FlipDev SEO Extension
 * 
 * @category   FlipDev
 * @package    FlipDev_Seo
 * @author     Philipp Breitsprecher <philippbreitsprecher@gmail.com>
 * @copyright  Copyright (c) 2025 FlipDev
 * @license    MIT
 */

declare(strict_types=1);

namespace FlipDev\Seo\Controller\Adminhtml\Checklist;

class Index extends \Magento\Backend\App\Action
{
    
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('FlipDev_Seo::checklist');
    }

    /**
     * @return void
     */
    public function execute()
    {
        $this->_view->loadLayout();

        $this->_setActiveMenu('FlipDev_Seo::checklist');
        $this->_view->getPage()->getConfig()->getTitle()->prepend(__('SEO Checklist'));

        $this->_addBreadcrumb(__('Stores'), __('Stores'));
        $this->_addBreadcrumb(__('SEO Checklist'), __('SEO Checklist'));

        $this->_view->renderLayout();
    }
}
