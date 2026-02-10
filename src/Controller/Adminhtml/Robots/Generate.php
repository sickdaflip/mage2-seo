<?php
/**
 * Copyright (c) 2025 Flipdev. All rights reserved.
 */
declare(strict_types=1);

namespace FlipDev\Seo\Controller\Adminhtml\Robots;

use FlipDev\Seo\Model\RobotsTxt\Generator;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;

class Generate extends Action
{
    public const ADMIN_RESOURCE = 'FlipDev_Seo::settings';

    public function __construct(
        Context $context,
        private JsonFactory $resultJsonFactory,
        private Generator $generator
    ) {
        parent::__construct($context);
    }

    public function execute()
    {
        $result = $this->resultJsonFactory->create();

        try {
            if ($this->generator->generate()) {
                return $result->setData([
                    'success' => true,
                    'message' => __('robots.txt generated successfully!')
                ]);
            } else {
                return $result->setData([
                    'success' => false,
                    'message' => __('robots.txt management is disabled.')
                ]);
            }
        } catch (\Exception $e) {
            return $result->setData([
                'success' => false,
                'message' => __('Error: %1', $e->getMessage())
            ]);
        }
    }
}
