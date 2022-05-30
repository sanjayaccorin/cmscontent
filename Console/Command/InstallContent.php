<?php
/**
 * @author      Accorin <engineering@accorin.com>
 * @copyright   Copyright © 2022. All rights reserved.
 */
declare(strict_types=1);

namespace Accorin\CmsContent\Console\Command;

use Accorin\CmsContent\Model\ContentVersion\Action\ProcessContent;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class InstallContent extends Command
{
    /**
     * @var ProcessContent
     */
    private $processContent;

    /**
     * @param ProcessContent $processContent
     * @param string|null $name
     */
    public function __construct(
        ProcessContent $processContent,
        string $name = null
    ) {
        $this->processContent = $processContent;
        parent::__construct($name);
    }

    /**
     * Configure command name and description
     */
    protected function configure()
    {
        $this->setName('accorin:installContent')
            ->setDescription('Triggers content version install process');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|void|null
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        try {
            $this->processContent->execute();
        } catch (\Exception $exception) {
            $output->writeln($exception->getMessage());
        }
    }
}
