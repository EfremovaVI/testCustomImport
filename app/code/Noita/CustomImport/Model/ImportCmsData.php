<?php
/**
 * Copyright (c) 2018. Noita. All rights reserved.
 */
namespace Noita\CustomImport\Model;

/**
 * Class ImportCmsData
 * @package Noita\CustomImport\Model
 */
class ImportCmsData extends \Noita\CustomImport\Model\AbstractImportCustomData
{
    /**
     * @var \Magento\Cms\Model\BlockFactory
     */
    private $blockFactory;

    /**
     * @var \Magento\Cms\Model\PageFactory
     */
    private $pageFactory;

    /**
     * @var string
     */
    private $_type;

    /**
     * ImportCmsData constructor.
     *
     * @param \Magento\Cms\Model\PageFactory $pageFactory
     * @param \Magento\Cms\Model\BlockFactory $blockFactory
     * @param \Magento\Framework\App\ResourceConnection $resourceConnection
     * @param \Magento\Framework\App\Config\Storage\WriterInterface $configWriter
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource|null $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb|null $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Cms\Model\PageFactory $pageFactory,
        \Magento\Cms\Model\BlockFactory $blockFactory,
        \Magento\Framework\App\ResourceConnection $resourceConnection,
        \Magento\Framework\App\Config\Storage\WriterInterface $configWriter,
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    )
    {
        parent::__construct($resourceConnection, $configWriter, $context, $registry, $resource, $resourceCollection, $data);
        $this->pageFactory = $pageFactory;
        $this->blockFactory = $blockFactory;
    }

    /**
     * @param string $type
     * @param null $storeId
     * @param null $identifier
     */
    public function runImportCmsData($type, $storeId = null, $identifier = null)
    {
        echo __('Start migrated.') . PHP_EOL;
        $this->_type = $type;
        $data = $this->getCmsData($type, $storeId, $identifier);
        if ($data['data']) {
            $storesData = [];
            foreach ($data['data'] as $item) {
                foreach ($data['stores'] as $stores) {
                    if ($stores[$type . '_id'] == $item[$type . '_id']) {
                        $storesData[] = $stores['store_id'];
                    }
                }
                $item['stores'] = count($storesData) < 1 ? [0] : $storesData;
                if (!is_null($storeId)) {
                    $item['stores'] = [$storeId];
                }
                $this->deleteUrlRewrite();
                $this->migrationData($item);
            }
            echo __('The data was migrated.') . PHP_EOL;

        }
    }

    /**
     * @param string $type
     * @param null $storeId
     * @param null $identifier
     *
     * @return array|null
     */
    protected function getCmsData($type, $storeId, $identifier = null)
    {
        try {
            $bind = [];
            $where = '';
            if (!is_null($storeId)) {
                $bind['store_id'] = $storeId;
                $where .= 'cms_store.store_id = :store_id';
            }
            if (!is_null($storeId) && !is_null($identifier)) {
                $where .= ' AND ';
            }
            if (!is_null($identifier)) {
                $bind['identifier'] = $identifier;
                $where .= 'cms.identifier = :identifier';
            }

            $selectData = $this->connection->select()
                ->from(
                    ['cms' => 'cms_' . $type]
                )
                ->joinLeft(
                    ['cms_store' => 'cms_' . $type . '_store'],
                    'cms.' . $type . '_id = cms_store.' . $type . '_id'
                )
                ->where($where);

            $data = $this->connection->fetchAll($selectData, $bind);

            $selectStores = $this->connection->select()->from(['cms_' . $type . '_store']);
            $stores = $this->connection->fetchAll($selectStores);

            return ['data' => $data, 'stores' => $stores];
        } catch (\Exception $ex) {
            echo __($ex->getMessage()) . PHP_EOL;
        }
        return null;
    }

    /**
     * @param array $data
     *
     * @return void
     */
    protected function migrationData(array $data)
    {
        try {
            switch ($this->_type) {
                case 'page':
                    $pageData = [
                        'title' => $data['title'],
                        'identifier' => $data['identifier'],
                        'stores' => $data['stores'],
                        'is_active' => $data['is_active'],
                        'content_heading' => $data['content_heading'],
                        'content' => $data['content'],
                        'page_layout' => $this->_getPageLayout($data['root_template'])
                    ];

                    $page = $this->pageFactory->create();
                    if (count($data['stores']) == 1) {
                        $page->setStoreId($data['stores']);
                    }
                    $page->load(
                        $data['identifier'],
                        'identifier'
                    );
                    if ($page->getId()) {
                        $page->setData($pageData)->save();
                        echo __($page->getId() . ' - update block') . PHP_EOL;
                    } else {
                        $page = $this->pageFactory->create()->setData($pageData)->save();
                        echo __($page->getId() . ' - create block') . PHP_EOL;
                    }
                    break;

                case 'block':
                    $blockData = [
                        'title' => $data['title'],
                        'identifier' => $data['identifier'],
                        'content' => $data['content'],
                        'stores' => $data['stores'],
                        'is_active' => $data['is_active'],
                    ];
                    $block = $this->blockFactory->create();
                    if (count($data['stores']) == 1) {
                        $block->setStoreId($data['stores']);
                    }
                    $block->load(
                        $data['identifier'],
                        'identifier'
                    );
                    if ($block->getId()) {
                        $block->setData($blockData)->save();
                        echo __($block->getId() . ' - update block') . PHP_EOL;
                    } else {
                        $block = $this->blockFactory->create()->setData($blockData)->save();
                        echo __($block->getId() . ' - create block') . PHP_EOL;
                    }
                    break;
            }
        } catch (\Exception $ex) {
            echo __($ex->getMessage()) . PHP_EOL;
        }
    }

    /**
     * @param $template
     *
     * @return string
     */
    protected function _getPageLayout($template)
    {
        switch ($template) {
            case 'one_column':
                return '1column';
            case 'one_column_frontpage':
                return '1column-full-width';
            case 'two_columns_left':
                return '2columns-left';
            case 'two_columns_right':
                return '2columns-right';
            case 'three_columns':
                return '3columns';
        }
        return 'empty';
    }

    /**
     * @return bool
     */
    protected function deleteUrlRewrite()
    {
        try {
            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            $resource = $objectManager->get('Magento\Framework\App\ResourceConnection');
            $connection = $resource->getConnection();
            $where = ['entity_type LIKE ?' => '%cms%'];
            if ($connection->delete('url_rewrite', $where)) {
                return true;
            }
        } catch (\Exception $ex) {
            echo __($ex->getMessage()) . PHP_EOL;
        }
        return false;
    }
}