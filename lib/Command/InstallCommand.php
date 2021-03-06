<?php

namespace Phpactor\Extension\ExtensionManager\Command;

use Phpactor\Extension\ExtensionManager\Model\Exception\CouldNotInstallExtension;
use Phpactor\Extension\ExtensionManager\Service\InstallerService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class InstallCommand extends Command
{
    /**
     * @var InstallerService
     */
    private $installer;

    public function __construct(InstallerService $installer)
    {
        parent::__construct();
        $this->installer = $installer;
    }

    protected function configure()
    {
        $this->setDescription('Install extensions');
        $this->addArgument('extension', InputArgument::OPTIONAL|InputArgument::IS_ARRAY);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if (count((array) $input->getArgument('extension'))) {
            try {
                $this->installer->requireExtensions((array) $input->getArgument('extension'));
            } catch (CouldNotInstallExtension $couldNotInstall) {
                $output->writeln('<error>Could not install</>');
                return 1;
            }

            return 0;
        }

        $this->installer->install();
    }
}
