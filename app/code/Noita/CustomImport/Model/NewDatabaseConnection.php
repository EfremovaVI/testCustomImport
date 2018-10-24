<?php
/**
 * Copyright (c) 2018. Noita. All rights reserved.
 */
namespace Noita\CustomImport\Model;

use Magento\Framework\Config\File\ConfigFilePool;

/**
 * Class NewDatabaseConnection
 * @package Noita\CustomImport\Model
 */
class NewDatabaseConnection extends \Magento\Framework\Model\AbstractModel
{
    /**
     * @var \Magento\Framework\App\DeploymentConfig\Writer
     */
    protected $deploymentConfig;

    /**
     * NewDatabaseConnection constructor.
     *
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource|null $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb|null $resourceCollection
     * @param \Magento\Framework\App\DeploymentConfig\Writer $deploymentConfig
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\App\DeploymentConfig\Writer $deploymentConfig,
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    )
    {
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
        $this->deploymentConfig = $deploymentConfig;
    }

    /**
     * @param $dbName
     * @param $dbUser
     * @param $dbPassword
     * @param $dbHost
     */
    public function createNewConnection($dbName, $dbUser, $dbPassword, $dbHost)
    {
        try {
            $dataConnection = [
                'connection' => [
                    'magento1' => [
                        'host' => $dbHost,
                        'dbname' => $dbName,
                        'username' => $dbUser,
                        'password' => $dbPassword,
                        'active' => '1'
                    ]
                ]
            ];
            $dataResource = [
                'magento1' => [
                    'connection' => 'magento1'
                ]
            ];
            $this->deploymentConfig->saveConfig([ConfigFilePool::APP_ENV => ['db' => $dataConnection]]);
            $this->deploymentConfig->saveConfig([ConfigFilePool::APP_ENV => ['resource' => $dataResource]]);
        } catch (\Exception $ex) {
            echo $ex->getMessage();
        }
    }
}