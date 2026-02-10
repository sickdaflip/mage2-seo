<?php
/**
 * Copyright (c) 2025 Flipdev. All rights reserved.
 */
declare(strict_types=1);

namespace FlipDev\Seo\Console\Command;

use FlipDev\Seo\Model\XmlSitemap\SitemapBuilder;
use Magento\Framework\Console\Cli;
use Magento\Store\Model\StoreManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class GenerateSitemapCommand extends Command
{
    private const OPTION_STORE = 'store';

    public function __construct(
        private SitemapBuilder $sitemapBuilder,
        private StoreManagerInterface $storeManager,
        ?string $name = null
    ) {
        parent::__construct($name);
    }

    /**
     * @inheritDoc
     */
    protected function configure(): void
    {
        $this->setName('flipdev:sitemap:generate')
            ->setDescription('Generate XML sitemaps (FlipDev SEO)')
            ->addOption(
                self::OPTION_STORE,
                's',
                InputOption::VALUE_OPTIONAL,
                'Store ID (optional, generates for all stores if not specified)'
            );

        parent::configure();
    }

    /**
     * @inheritDoc
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $storeId = $input->getOption(self::OPTION_STORE);

        try {
            if ($storeId !== null) {
                $storeId = (int) $storeId;
                $output->writeln("<info>Generating sitemap for store ID: {$storeId}</info>");

                if (!$this->sitemapBuilder->isEnabled($storeId)) {
                    $output->writeln("<comment>Custom sitemap is disabled for this store.</comment>");
                    return Cli::RETURN_SUCCESS;
                }

                $files = $this->sitemapBuilder->generateForStore($storeId);
                $this->outputResults($output, $storeId, $files);
            } else {
                $output->writeln("<info>Generating sitemaps for all stores...</info>");

                $results = $this->sitemapBuilder->generateForAllStores();

                foreach ($results as $id => $result) {
                    if ($result['success']) {
                        $this->outputResults($output, $id, $result['files']);
                    } else {
                        $output->writeln("<error>Store {$id}: Failed - {$result['error']}</error>");
                    }
                }
            }

            $output->writeln('');
            $output->writeln("<info>Sitemap generation complete!</info>");
            return Cli::RETURN_SUCCESS;

        } catch (\Exception $e) {
            $output->writeln("<error>Error: {$e->getMessage()}</error>");
            return Cli::RETURN_FAILURE;
        }
    }

    /**
     * Output generated files
     */
    private function outputResults(OutputInterface $output, int $storeId, array $files): void
    {
        try {
            $store = $this->storeManager->getStore($storeId);
            $storeName = $store->getName();
        } catch (\Exception $e) {
            $storeName = "Store {$storeId}";
        }

        $output->writeln('');
        $output->writeln("<comment>{$storeName} (ID: {$storeId})</comment>");

        if (empty($files)) {
            $output->writeln("  <comment>No files generated (sitemap may be disabled)</comment>");
            return;
        }

        foreach ($files as $file) {
            $output->writeln("  <info>✓</info> {$file}");
        }
    }
}
