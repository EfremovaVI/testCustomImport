<?php
/**
 * Copyright (c) 2018. Noita. All rights reserved.
 */
namespace Noita\CustomImport\Model;

/**
 * Class ImportConfigData
 * @package Noita\CustomImport\Model
 */
class ImportConfigData extends \Noita\CustomImport\Model\AbstractImportCustomData
{
    /**
     * @param $pathSection
     * @param null $scopeId
     */
    public function runImportData($pathSection, $scopeId = null)
    {
        $dataExceptions = [
            'web/unsecure/base_url',
            'web/secure/base_url',
            'web/unsecure/base_link_url',
            'web/unsecure/base_skin_url',
            'web/unsecure/base_media_url',
            'web/unsecure/base_js_url',
            'web/secure/base_link_url',
            'web/secure/base_skin_url',
            'web/secure/base_media_url',
            'web/secure/base_js_url',
            'web/secure/use_in_frontend',
            'web/secure/use_in_adminhtml'
        ];
        $data = $this->getConfigData($pathSection, $scopeId);
        if ($data) {
            foreach ($data as $item) {
                if (in_array($item['path'], $dataExceptions)) {
                    continue;
                }
                $this->migrationData($item);
            }
            echo __('The data was migrated successfully.')  . PHP_EOL;
            return;
        }
        echo __('No data to migrate.') . PHP_EOL;
        return;
    }

    /**
     * @param $pathSection
     * @param null $scopeId
     *
     * @return array|null
     */
    protected function getConfigData($pathSection,  $scopeId = null)
    {
        try {
            $bind = is_null($scopeId) ? ['path'=>$pathSection . '%'] : ['path'=>$pathSection . '%', 'scope_id' => $scopeId];
            $where = is_null($scopeId) ? 'path LIKE :path' : 'path LIKE :path AND scope_id = :scope_id';
            $select = $this->connection->select()
                ->from(['core_config_data'])
                ->where($where);
            $data = $this->connection->fetchAll($select, $bind);

            return $data;
        } catch (\Exception $ex) {
            echo __($ex->getMessage()) . PHP_EOL;
        }
        return null;
    }

    /**
     * @param $data
     */
    protected function migrationData(array $data)
    {
        try {
            $this->configWriter->save($data['path'], $data['value'], $data['scope'], $data['scope_id']);
        } catch (\Exception $ex) {
            echo __($ex->getMessage()) . PHP_EOL;
        }
    }
}