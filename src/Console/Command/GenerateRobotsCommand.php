<?php
/**
 * Copyright (c) 2025 Flipdev. All rights reserved.
 */
declare(strict_types=1);

namespace FlipDev\Seo\Console\Command;

use FlipDev\Seo\Model\RobotsTxt\Generator;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class GenerateRobotsCommand extends Command
{
    public function __construct(
        private Generator $generator
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setName('flipdev:robots:generate')
            ->setDescription('Generate robots.txt file from configuration');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln('<info>Generating robots.txt...</info>');

        if (!$this->generator->isEnabled()) {
            $output->writeln('<comment>robots.txt management is disabled in configuration.</comment>');
            return Command::SUCCESS;
        }

        try {
            if ($this->generator->generate()) {
                $output->writeln('<info>✓ robots.txt generated successfully!</info>');
                $output->writeln('');
                $output->writeln('<comment>Content:</comment>');
                $output->writeln($this->generator->getCurrentContent());
                return Command::SUCCESS;
            } else {
                $output->writeln('<error>Failed to generate robots.txt</error>');
                return Command::FAILURE;
            }
        } catch (\Exception $e) {
            $output->writeln('<error>Error: ' . $e->getMessage() . '</error>');
            return Command::FAILURE;
        }
    }
}
