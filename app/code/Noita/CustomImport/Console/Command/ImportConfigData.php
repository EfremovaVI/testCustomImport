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
use Noita\CustomImport\Model\ImportConfigData as ConfigDataModel;

/**
 * Class ImportConfigData
 * @package Noita\CustomImport\Console\Command
 */
class ImportConfigData extends Command
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
     * @var ConfigDataModel
     */
    private $_configData;

    /**
     * @param State $appState
     * @param ObjectManagerInterface $objectManager
     * @param ConfigDataModel $importConfigData
     */
    public function __construct(
        State $appState,
        ObjectManagerInterface $objectManager,
        ConfigDataModel $importConfigData
    )
    {
        $this->appState = $appState;
        $this->objectManager = $objectManager;
        $this->_configData = $importConfigData;

        parent::__construct();
    }


    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('import:migration-core-config-data')
            ->setDescription('System configuration custom import')
            ->setDefinition([]);
        $this->addArgument('pathSection', InputArgument::REQUIRED, __('Import config path section (pathSection/*/*)'));
        $this->addArgument('scopeId', InputArgument::OPTIONAL, __('Scope Id'));

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
        } catch (Exception $ex) {
            $output->writeln("Areacode was already set");
        }

        $this->_configData->runImportData(
            $input->getArgument('pathSection'),
            $input->getArgument('scopeId')
        );
    }
}
