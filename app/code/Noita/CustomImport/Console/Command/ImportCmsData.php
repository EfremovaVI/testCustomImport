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
use Noita\CustomImport\Model\ImportCmsData as ImportCmsModel;

/**
 * Class ImportCmsData
 * @package Noita\CustomImport\Console\Command
 */
class ImportCmsData extends Command
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
     * @var ImportCmsModel
     */
    private $_cmsData;

    /**
     * @param State $appState
     * @param ObjectManagerInterface $objectManager
     * @param ImportCmsModel $importCmsModel
     */
    public function __construct(
        State $appState,
        ObjectManagerInterface $objectManager,
        ImportCmsModel $importCmsModel
    )
    {
        $this->appState = $appState;
        $this->objectManager = $objectManager;
        $this->_cmsData = $importCmsModel;

        parent::__construct();
    }


    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('import:migration-cms-data')
            ->setDescription('CMS block/page custom import')
            ->setDefinition([]);
        $this->addArgument('type', InputArgument::REQUIRED, __('Migration entity type - "block" or "page"'));
        $this->addArgument('storeId', InputArgument::OPTIONAL, __('Store Id'));
        $this->addArgument('identifier', InputArgument::OPTIONAL, __('CMS block/page identifier'));

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

        $this->_cmsData->runImportCmsData(
            $input->getArgument('type'),
            $input->getArgument('storeId'),
            $input->getArgument('identifier')
        );
    }
}
