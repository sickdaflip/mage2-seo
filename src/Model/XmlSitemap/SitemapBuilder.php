<?php
/**
 * Copyright (c) 2025 Flipdev. All rights reserved.
 */
declare(strict_types=1);

namespace FlipDev\Seo\Model\XmlSitemap;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\WriteInterface;
use Magento\Framework\Module\Dir;
use Magento\Framework\Module\Dir\Reader as ModuleReader;
use Magento\Framework\View\Asset\Repository as AssetRepository;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;
use Psr\Log\LoggerInterface;

class SitemapBuilder
{
    private const CONFIG_PATH_ENABLED = 'flipdev_seo/xml_sitemap/enabled';
    private const CONFIG_PATH_MAX_URLS = 'flipdev_seo/xml_sitemap/max_urls_per_sitemap';
    private const DEFAULT_MAX_URLS = 50000;

    private WriteInterface $directory;
    private string $xslUrl = '';
    private string $storeCode = '';
    private int $maxUrlsPerSitemap = self::DEFAULT_MAX_URLS;

    public function __construct(
        private ScopeConfigInterface $scopeConfig,
        private StoreManagerInterface $storeManager,
        private Filesystem $filesystem,
        private AssetRepository $assetRepository,
        private LoggerInterface $logger,
        private ProductGenerator $productGenerator,
        private CategoryGenerator $categoryGenerator,
        private CmsGenerator $cmsGenerator
    ) {
        $this->directory = $this->filesystem->getDirectoryWrite(DirectoryList::PUB);
    }

    /**
     * Check if custom sitemap is enabled
     */
    public function isEnabled(int $storeId): bool
    {
        return $this->scopeConfig->isSetFlag(
            self::CONFIG_PATH_ENABLED,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Get max URLs per sitemap from config
     */
    private function getMaxUrlsPerSitemap(int $storeId): int
    {
        $value = $this->scopeConfig->getValue(
            self::CONFIG_PATH_MAX_URLS,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );

        return $value ? (int) $value : self::DEFAULT_MAX_URLS;
    }

    /**
     * Get store code for filename prefix
     */
    private function getStoreCode(int $storeId): string
    {
        try {
            $store = $this->storeManager->getStore($storeId);
            return $store->getCode();
        } catch (\Exception $e) {
            return 'store' . $storeId;
        }
    }

    /**
     * Get prefixed filename with store code
     */
    private function getPrefixedFilename(string $filename): string
    {
        return $this->storeCode . '-' . $filename;
    }

    /**
     * Generate all sitemaps for a store
     */
    public function generateForStore(int $storeId): array
    {
        if (!$this->isEnabled($storeId)) {
            return [];
        }

        $generatedFiles = [];
        $sitemaps = [];

        try {
            $this->storeCode = $this->getStoreCode($storeId);
            $this->xslUrl = $this->getXslUrl($storeId);
            $this->maxUrlsPerSitemap = $this->getMaxUrlsPerSitemap($storeId);

            // Generate product sitemaps (may be split into multiple files)
            if ($this->productGenerator->isEnabled($storeId)) {
                $items = $this->productGenerator->generate($storeId);
                if (!empty($items)) {
                    $files = $this->writeChunkedXml('products', $items);
                    $sitemaps = array_merge($sitemaps, $files);
                    $generatedFiles = array_merge($generatedFiles, $files);
                }
            }

            // Generate category sitemaps (may be split into multiple files)
            if ($this->categoryGenerator->isEnabled($storeId)) {
                $items = $this->categoryGenerator->generate($storeId);
                if (!empty($items)) {
                    $files = $this->writeChunkedXml('categories', $items);
                    $sitemaps = array_merge($sitemaps, $files);
                    $generatedFiles = array_merge($generatedFiles, $files);
                }
            }

            // Generate CMS sitemaps (may be split into multiple files)
            if ($this->cmsGenerator->isEnabled($storeId)) {
                $items = $this->cmsGenerator->generate($storeId);
                if (!empty($items)) {
                    $files = $this->writeChunkedXml('cms', $items);
                    $sitemaps = array_merge($sitemaps, $files);
                    $generatedFiles = array_merge($generatedFiles, $files);
                }
            }

            // Generate sitemap index
            if (!empty($sitemaps)) {
                $indexFilename = $this->getPrefixedFilename('sitemap.xml');
                $this->writeSitemapIndex($sitemaps, $storeId, $indexFilename);
                $generatedFiles[] = $indexFilename;
            }

        } catch (\Exception $e) {
            $this->logger->error('FlipDev_Seo: Error generating sitemap', [
                'store_id' => $storeId,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }

        return $generatedFiles;
    }

    /**
     * Write items to chunked sitemap files if they exceed the limit
     *
     * @param string $type Type identifier (products, categories, cms)
     * @param array $items All items to write
     * @return array List of generated filenames
     */
    private function writeChunkedXml(string $type, array $items): array
    {
        $files = [];
        $chunks = array_chunk($items, $this->maxUrlsPerSitemap);
        $totalChunks = count($chunks);

        foreach ($chunks as $index => $chunk) {
            $partNumber = $index + 1;

            // Only add number suffix if there are multiple parts
            if ($totalChunks > 1) {
                $filename = $this->getPrefixedFilename("sitemap-{$type}-{$partNumber}.xml");
            } else {
                $filename = $this->getPrefixedFilename("sitemap-{$type}.xml");
            }

            $this->writeXml($filename, $chunk);
            $files[] = $filename;
        }

        return $files;
    }

    /**
     * Generate sitemaps for all stores
     */
    public function generateForAllStores(): array
    {
        $results = [];

        foreach ($this->storeManager->getStores() as $store) {
            if (!$store->getIsActive()) {
                continue;
            }

            $storeId = (int) $store->getId();
            try {
                $files = $this->generateForStore($storeId);
                $results[$storeId] = [
                    'success' => true,
                    'files' => $files
                ];
            } catch (\Exception $e) {
                $results[$storeId] = [
                    'success' => false,
                    'error' => $e->getMessage()
                ];
            }
        }

        return $results;
    }

    /**
     * Write sitemap XML file
     */
    private function writeXml(string $filename, array $items): void
    {
        $xml = new \XMLWriter();
        $xml->openMemory();
        $xml->setIndent(true);
        $xml->setIndentString('    ');
        $xml->startDocument('1.0', 'UTF-8');

        // Add XSL stylesheet reference
        if ($this->xslUrl) {
            $xml->writePi('xml-stylesheet', 'type="text/xsl" href="' . $this->xslUrl . '"');
        }

        $xml->startElement('urlset');
        $xml->writeAttribute('xmlns', 'http://www.sitemaps.org/schemas/sitemap/0.9');
        $xml->writeAttribute('xmlns:image', 'http://www.google.com/schemas/sitemap-image/1.1');
        $xml->writeAttribute('xmlns:video', 'http://www.google.com/schemas/sitemap-video/1.1');
        $xml->writeAttribute('xmlns:xhtml', 'http://www.w3.org/1999/xhtml');

        foreach ($items as $item) {
            $xml->startElement('url');

            $xml->writeElement('loc', $item['loc']);

            if (!empty($item['lastmod'])) {
                $xml->writeElement('lastmod', $item['lastmod']);
            }

            if (!empty($item['changefreq'])) {
                $xml->writeElement('changefreq', $item['changefreq']);
            }

            if (!empty($item['priority'])) {
                $xml->writeElement('priority', $item['priority']);
            }

            // Add images
            if (!empty($item['images'])) {
                foreach ($item['images'] as $image) {
                    $xml->startElement('image:image');
                    $xml->writeElement('image:loc', $image['loc']);
                    if (!empty($image['title'])) {
                        $xml->writeElement('image:title', $image['title']);
                    }
                    $xml->endElement(); // image:image
                }
            }

            // Add videos
            if (!empty($item['videos'])) {
                foreach ($item['videos'] as $video) {
                    $xml->startElement('video:video');
                    $xml->writeElement('video:thumbnail_loc', $video['thumbnail_loc']);
                    $xml->writeElement('video:title', $video['title']);
                    $xml->writeElement('video:description', $video['description']);
                    if (!empty($video['player_loc'])) {
                        $xml->startElement('video:player_loc');
                        $xml->writeAttribute('allow_embed', 'yes');
                        $xml->text($video['player_loc']);
                        $xml->endElement(); // video:player_loc
                    }
                    $xml->endElement(); // video:video
                }
            }

            // Add hreflang links
            if (!empty($item['hreflang'])) {
                foreach ($item['hreflang'] as $link) {
                    $xml->startElement('xhtml:link');
                    $xml->writeAttribute('rel', 'alternate');
                    $xml->writeAttribute('hreflang', $link['hreflang']);
                    $xml->writeAttribute('href', $link['href']);
                    $xml->endElement(); // xhtml:link
                }
            }

            $xml->endElement(); // url
        }

        $xml->endElement(); // urlset
        $xml->endDocument();

        $this->directory->writeFile($filename, $xml->outputMemory());
    }

    /**
     * Write sitemap index file
     */
    private function writeSitemapIndex(array $sitemaps, int $storeId, string $filename = 'sitemap.xml'): void
    {
        $baseUrl = $this->getBaseUrl($storeId);

        $xml = new \XMLWriter();
        $xml->openMemory();
        $xml->setIndent(true);
        $xml->setIndentString('    ');
        $xml->startDocument('1.0', 'UTF-8');

        // Add XSL stylesheet reference
        if ($this->xslUrl) {
            $xml->writePi('xml-stylesheet', 'type="text/xsl" href="' . $this->xslUrl . '"');
        }

        $xml->startElement('sitemapindex');
        $xml->writeAttribute('xmlns', 'http://www.sitemaps.org/schemas/sitemap/0.9');

        foreach ($sitemaps as $sitemap) {
            $xml->startElement('sitemap');
            $xml->writeElement('loc', $baseUrl . '/' . $sitemap);
            $xml->writeElement('lastmod', date('c'));
            $xml->endElement(); // sitemap
        }

        $xml->endElement(); // sitemapindex
        $xml->endDocument();

        $this->directory->writeFile($filename, $xml->outputMemory());
    }

    /**
     * Get base URL for store
     */
    private function getBaseUrl(int $storeId): string
    {
        try {
            $store = $this->storeManager->getStore($storeId);
            return rtrim($store->getBaseUrl(), '/');
        } catch (\Exception $e) {
            return '';
        }
    }

    /**
     * Get XSL stylesheet URL
     */
    private function getXslUrl(int $storeId): string
    {
        try {
            // Copy XSL to pub folder if not exists
            $this->deployXslFile();

            $store = $this->storeManager->getStore($storeId);
            return rtrim($store->getBaseUrl(), '/') . '/sitemap.xsl';
        } catch (\Exception $e) {
            return '';
        }
    }

    /**
     * Deploy XSL file to pub folder
     */
    private function deployXslFile(): void
    {
        $xslFilename = 'sitemap.xsl';

        if ($this->directory->isExist($xslFilename)) {
            return;
        }

        // XSL content as fallback (embedded)
        $xslContent = $this->getXslContent();
        $this->directory->writeFile($xslFilename, $xslContent);
    }

    /**
     * Get XSL stylesheet content
     */
    private function getXslContent(): string
    {
        return '<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="2.0"
                xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
                xmlns:sitemap="http://www.sitemaps.org/schemas/sitemap/0.9"
                xmlns:image="http://www.google.com/schemas/sitemap-image/1.1"
                xmlns:video="http://www.google.com/schemas/sitemap-video/1.1"
                xmlns:xhtml="http://www.w3.org/1999/xhtml">
    <xsl:output method="html" encoding="UTF-8" indent="yes"/>
    <xsl:template match="/">
        <html>
            <head>
                <title><xsl:choose><xsl:when test="sitemap:sitemapindex">XML Sitemap Index</xsl:when><xsl:otherwise>XML Sitemap</xsl:otherwise></xsl:choose></title>
                <style type="text/css">
                    :root{--primary:#1a1a2e;--secondary:#16213e;--accent:#0f3460;--highlight:#e94560;--text:#333;--light:#f5f5f5;--border:#ddd}*{box-sizing:border-box}body{font-family:-apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,sans-serif;font-size:14px;color:var(--text);background:var(--light);margin:0;padding:0;line-height:1.6}.header{background:linear-gradient(135deg,var(--primary),var(--accent));color:#fff;padding:30px 20px;text-align:center}.header h1{margin:0 0 10px;font-size:28px;font-weight:600}.header p{margin:5px 0;opacity:.9;font-size:15px}.header a{color:#fff;text-decoration:underline}.container{max-width:1200px;margin:0 auto;padding:20px}.stats{display:flex;gap:20px;margin-bottom:20px;flex-wrap:wrap}.stat-box{background:#fff;border-radius:8px;padding:20px;flex:1;min-width:150px;box-shadow:0 2px 4px rgba(0,0,0,.1)}.stat-box .number{font-size:32px;font-weight:700;color:var(--highlight)}.stat-box .label{color:#666;font-size:13px}table{width:100%;background:#fff;border-collapse:collapse;border-radius:8px;overflow:hidden;box-shadow:0 2px 4px rgba(0,0,0,.1)}th{background:var(--secondary);color:#fff;padding:15px;text-align:left;font-weight:500}td{padding:12px 15px;border-bottom:1px solid var(--border)}tr:last-child td{border-bottom:none}tr:hover td{background:#f8f9fa}a{color:var(--accent);text-decoration:none}a:hover{color:var(--highlight);text-decoration:underline}.url-cell{word-break:break-all;max-width:500px}.priority{text-align:center}.priority-high{color:#28a745;font-weight:700}.priority-medium{color:#ffc107}.priority-low{color:#6c757d}.images-count{background:var(--accent);color:#fff;padding:2px 8px;border-radius:12px;font-size:12px}.hreflang-list{font-size:12px;color:#666}.hreflang-list span{display:inline-block;background:#e9ecef;padding:2px 6px;border-radius:3px;margin:2px}.footer{text-align:center;padding:20px;color:#666;font-size:13px}.footer a{color:var(--accent)}
                </style>
            </head>
            <body>
                <div class="header">
                    <h1><xsl:choose><xsl:when test="sitemap:sitemapindex">XML Sitemap Index</xsl:when><xsl:otherwise>XML Sitemap</xsl:otherwise></xsl:choose></h1>
                    <p>Generated by <strong>FlipDev SEO</strong> for Magento 2</p>
                    <p style="font-size:13px;opacity:.8">Author: Philipp Breitsprecher</p>
                </div>
                <div class="container"><xsl:apply-templates/></div>
                <div class="footer">This XML sitemap helps search engines discover and index pages on your website.<br/>FlipDev SEO by <a href="mailto:philippbreitsprecher@gmail.com">Philipp Breitsprecher</a></div>
            </body>
        </html>
    </xsl:template>
    <xsl:template match="sitemap:sitemapindex">
        <div class="stats"><div class="stat-box"><div class="number"><xsl:value-of select="count(sitemap:sitemap)"/></div><div class="label">Sitemaps</div></div></div>
        <table><thead><tr><th>Sitemap</th><th style="width:200px">Last Modified</th></tr></thead><tbody>
            <xsl:for-each select="sitemap:sitemap"><tr><td class="url-cell"><a href="{sitemap:loc}"><xsl:value-of select="sitemap:loc"/></a></td><td><xsl:value-of select="substring(sitemap:lastmod,1,10)"/></td></tr></xsl:for-each>
        </tbody></table>
    </xsl:template>
    <xsl:template match="sitemap:urlset">
        <div class="stats"><div class="stat-box"><div class="number"><xsl:value-of select="count(sitemap:url)"/></div><div class="label">URLs</div></div><xsl:if test="sitemap:url/image:image"><div class="stat-box"><div class="number"><xsl:value-of select="count(sitemap:url/image:image)"/></div><div class="label">Images</div></div></xsl:if><xsl:if test="sitemap:url/video:video"><div class="stat-box"><div class="number"><xsl:value-of select="count(sitemap:url/video:video)"/></div><div class="label">Videos</div></div></xsl:if></div>
        <table><thead><tr><th>URL</th><xsl:if test="sitemap:url/sitemap:priority"><th style="width:80px">Priority</th></xsl:if><xsl:if test="sitemap:url/sitemap:changefreq"><th style="width:100px">Change Freq</th></xsl:if><xsl:if test="sitemap:url/sitemap:lastmod"><th style="width:120px">Last Modified</th></xsl:if><xsl:if test="sitemap:url/image:image"><th style="width:80px">Images</th></xsl:if><xsl:if test="sitemap:url/video:video"><th style="width:80px">Videos</th></xsl:if></tr></thead><tbody>
            <xsl:for-each select="sitemap:url"><tr>
                <td class="url-cell"><a href="{sitemap:loc}"><xsl:value-of select="sitemap:loc"/></a><xsl:if test="xhtml:link"><div class="hreflang-list"><xsl:for-each select="xhtml:link[@rel=&apos;alternate&apos;]"><span><xsl:value-of select="@hreflang"/></span></xsl:for-each></div></xsl:if></td>
                <xsl:if test="../sitemap:url/sitemap:priority"><td class="priority"><xsl:value-of select="sitemap:priority"/></td></xsl:if>
                <xsl:if test="../sitemap:url/sitemap:changefreq"><td><xsl:value-of select="sitemap:changefreq"/></td></xsl:if>
                <xsl:if test="../sitemap:url/sitemap:lastmod"><td><xsl:value-of select="substring(sitemap:lastmod,1,10)"/></td></xsl:if>
                <xsl:if test="../sitemap:url/image:image"><td style="text-align:center"><xsl:if test="image:image"><span class="images-count"><xsl:value-of select="count(image:image)"/></span></xsl:if></td></xsl:if>
                <xsl:if test="../sitemap:url/video:video"><td style="text-align:center"><xsl:if test="video:video"><span class="images-count" style="background:#e94560"><xsl:value-of select="count(video:video)"/></span></xsl:if></td></xsl:if>
            </tr></xsl:for-each>
        </tbody></table>
    </xsl:template>
</xsl:stylesheet>';
    }
}
