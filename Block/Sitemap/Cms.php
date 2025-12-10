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

namespace FlipDev\Seo\Block\Sitemap;

use Magento\Framework\View\Element\Template\Context;
use Magento\Framework\View\Element\Template;
use FlipDev\Seo\Model\Sitemap\CmsPages;

class Cms extends Template
{
    
    private $cmsPages;

    /**
     * @param Context $context
     * @param CmsPages $cmsPages
     */
    public function __construct(Context $context, CmsPages $cmsPages)
    {
        $this->cmsPages = $cmsPages;
        parent::__construct($context);
    }

    /**
     * @return \Magento\Cms\Api\Data\PageInterface[]
     */
    public function getCmsPages()
    {
        return $this->cmsPages->getCmsPages();
    }
}