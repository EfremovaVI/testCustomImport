<?php
/**
 * Copyright (c) 2018. Noita. All rights reserved.
 */
namespace Noita\CustomImport\Console\Command;

use Magento\Framework\App\State;
use Magento\Framework\ObjectManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Noita\CustomImport\Model\NewDatabaseConnection as NewDatabaseConnection;

/**
 * Class ImportConfigData
 * @package Noita\CustomImport\Console\Command
 */
class ImportCreateNewConnection extends Command
{
    /**
     * @var \Magento\Framework\App\State
     */
    private $appState;

    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var NewDatabaseConnection
     */
    private $_newDatabaseConnection;

    /**
     * @param State $appState
     * @param ObjectManagerInterface $objectManager
     * @param NewDatabaseConnection $newDatabaseConnection
     */
    public function __construct(
        State $appState,
        ObjectManagerInterface $objectManager,
        NewDatabaseConnection $newDatabaseConnection
    )
    {
        $this->appState = $appState;
        $this->objectManager = $objectManager;
        $this->_newDatabaseConnection = $newDatabaseConnection;

        parent::__construct();
    }


    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('import:create-new-connection')
            ->setDescription('Add new connection with Magento 1 database')
            ->setDefinition([]);
        $this->addArgument('dbName', InputArgument::REQUIRED, __('Magento 1 database name'));
        $this->addArgument('dbUser', InputArgument::REQUIRED, __('Magento 1 database user'));
        $this->addArgument('dbPassword', InputArgument::REQUIRED, __('Magento 1 database password'));
        $this->addArgument('dbHost', InputArgument::REQUIRED, __('Magento 1 database host'));

        parent::configure();
    }

    /**
     * {@inheritdoc}
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        try {
            $this->appState->setAreaCode('adminhtml');
        } catch (\Exception $ex) {
            $output->writeln("Areacode was already set");
        }

        $this->_newDatabaseConnection->createNewConnection(
            $input->getArgument('dbName'),
            $input->getArgument('dbUser'),
            $input->getArgument('dbPassword'),
            $input->getArgument('dbHost')
        );
    }
}
