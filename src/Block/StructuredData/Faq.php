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

namespace FlipDev\Seo\Block\StructuredData;

use Magento\Framework\View\Element\Template\Context;
use FlipDev\Seo\Helper\Data as SeoHelper;

class Faq extends \FlipDev\Seo\Block\Template
{
    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Magento\Cms\Model\BlockFactory
     */
    protected $blockFactory;

    /**
     * @var \Magento\Cms\Model\Template\FilterProvider
     */
    protected $filterProvider;

    /**
     * @param Context $context
     * @param SeoHelper $flipDevSeoHelper
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Cms\Model\BlockFactory $blockFactory
     * @param \Magento\Cms\Model\Template\FilterProvider $filterProvider
     * @param array $data
     */
    public function __construct(
        Context $context,
        SeoHelper $flipDevSeoHelper,
        \Magento\Framework\Registry $registry,
        \Magento\Cms\Model\BlockFactory $blockFactory,
        \Magento\Cms\Model\Template\FilterProvider $filterProvider,
        array $data = []
    ) {
        $this->registry = $registry;
        $this->blockFactory = $blockFactory;
        $this->filterProvider = $filterProvider;
        parent::__construct($context, $flipDevSeoHelper, $data);
    }

    /**
     * Get FAQ items
     *
     * Format expected:
     * - From product attribute (JSON): [{"question": "...", "answer": "..."}, ...]
     * - From product attribute (simple): Q: Question?\nA: Answer\n\nQ: Question2?\nA: Answer2
     *
     * @return array
     */
    public function getFaqItems(): array
    {
        $items = [];

        // Try product FAQ attribute first
        $product = $this->registry->registry('product');
        if ($product) {
            $faqAttribute = $this->helper->getConfig('flipdev_seo/faq_sd/product_attribute');
            if ($faqAttribute) {
                $faqData = $product->getData($faqAttribute);
                if ($faqData) {
                    $items = $this->parseFaqData($faqData);
                }
            }
        }

        // Try category FAQ attribute
        $category = $this->registry->registry('current_category');
        if (empty($items) && $category && !$product) {
            $faqAttribute = $this->helper->getConfig('flipdev_seo/faq_sd/category_attribute');
            if ($faqAttribute) {
                $faqData = $category->getData($faqAttribute);
                if ($faqData) {
                    $items = $this->parseFaqData($faqData);
                }
            }
        }

        return $items;
    }

    /**
     * Parse FAQ data from various formats
     *
     * @param string $data
     * @return array
     */
    protected function parseFaqData(string $data): array
    {
        $items = [];

        // Try JSON format first
        $jsonData = json_decode($data, true);
        if (is_array($jsonData)) {
            foreach ($jsonData as $item) {
                if (isset($item['question']) && isset($item['answer'])) {
                    $items[] = [
                        'question' => $this->helper->cleanString($item['question']),
                        'answer' => $this->helper->cleanString(strip_tags($item['answer'])),
                    ];
                }
            }
            return $items;
        }

        // Try Q&A format: Q: Question?\nA: Answer
        $pattern = '/Q:\s*(.+?)\s*(?:\n|\r\n)A:\s*(.+?)(?=(?:\n|\r\n)Q:|$)/si';
        if (preg_match_all($pattern, $data, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $match) {
                $items[] = [
                    'question' => $this->helper->cleanString(trim($match[1])),
                    'answer' => $this->helper->cleanString(strip_tags(trim($match[2]))),
                ];
            }
        }

        return $items;
    }

    /**
     * Check if FAQ data exists
     *
     * @return bool
     */
    public function hasFaq(): bool
    {
        return !empty($this->getFaqItems());
    }

    /**
     * Get structured data array
     *
     * @return array
     */
    public function getStructuredData(): array
    {
        $items = $this->getFaqItems();
        $mainEntity = [];

        foreach ($items as $item) {
            $mainEntity[] = [
                '@type' => 'Question',
                'name' => $item['question'],
                'acceptedAnswer' => [
                    '@type' => 'Answer',
                    'text' => $item['answer'],
                ],
            ];
        }

        return [
            '@context' => 'https://schema.org',
            '@type' => 'FAQPage',
            'mainEntity' => $mainEntity,
        ];
    }
}
