<?php
/**
 * @category Magento-2 Qordoba Connector Module
 * @package Qordoba_Connector
 * @copyright Copyright (c) 2017
 * @license https://www.qordoba.com/terms
 */

namespace Qordoba\Connector\Setup;

use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

/**
 * Class InstallSchema
 * @package Qordoba\Connector\Setup
 */
class InstallSchema implements InstallSchemaInterface
{
    /**
     * @const string
     */
    const QORDOBA_PREFERENCE_TABLE = 'qordoba_preference';
    /**
     * @const string
     */
    const QORDOBA_SUBMISSIONS_TABLE = 'qordoba_submissions';
    /**
     * @const string
     */
    const QORDOBA_EVENTS_TABLE = 'qordoba_event';
    /**
     * @const string
     */
    const QORDOBA_TRANSLATED_CONTENT_TABLE = 'qordoba_translated_content';

    /**
     * @param SchemaSetupInterface $setup
     * @param ModuleContextInterface $context
     * @throws \Zend_Db_Exception
     */
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;
        $installer->startSetup();

        $this->createPreferencesTable($installer);
        $this->createTranslateContentTable($installer);
        $this->createEventsTable($installer);
        $this->createTranslatedContentTable($installer);

        $installer->endSetup();
    }

    /**
     * @param SchemaSetupInterface $setup
     * @throws \Zend_Db_Exception
     */
    private function createTranslateContentTable(SchemaSetupInterface $setup)
    {
        $connection = $setup->getConnection();

        $contentTable = $connection
            ->newTable($setup->getTable(self::QORDOBA_SUBMISSIONS_TABLE))
            ->addColumn(
                'id',
                Table::TYPE_INTEGER,
                null,
                [
                    'identity' => true,
                    'nullable' => false,
                    'primary' => true,
                    'unsigned' => true
                ]
            )->addColumn(
                'preference_id',
                Table::TYPE_INTEGER,
                null,
                [
                    'identity' => false,
                    'nullable' => false,
                    'primary' => false,
                    'unsigned' => true
                ]
            )->addColumn(
                'store_id',
                Table::TYPE_SMALLINT,
                null,
                [
                    'identity' => false,
                    'nullable' => false,
                    'primary' => false,
                    'unsigned' => true
                ]
            )->addColumn(
                'content_id',
                Table::TYPE_INTEGER,
                null,
                [
                    'identity' => false,
                    'nullable' => false,
                    'primary' => false,
                    'unsigned' => true
                ]
            )->addColumn(
                'title',
                Table::TYPE_TEXT,
                255,
                ['nullable' => false]
            )->addColumn(
                'file_name',
                Table::TYPE_TEXT,
                255,
                ['nullable' => true]
            )->addColumn(
                'version',
                Table::TYPE_INTEGER,
                null,
                [
                    'identity' => false,
                    'nullable' => false,
                    'primary' => false,
                    'unsigned' => true
                ]
            )->addColumn(
                'type_id',
                Table::TYPE_INTEGER,
                null,
                [
                    'identity' => false,
                    'nullable' => false,
                    'primary' => false,
                    'unsigned' => true
                ]
            )
            ->addColumn(
                'state',
                Table::TYPE_SMALLINT,
                null,
                [
                    'identity' => false,
                    'nullable' => false,
                    'primary' => false,
                    'unsigned' => true
                ]
            )->addColumn(
                'created_time',
                Table::TYPE_TIMESTAMP
            )->addColumn(
                'updated_time',
                Table::TYPE_TIMESTAMP
            )->addForeignKey(
                $setup->getFkName(
                    self::QORDOBA_SUBMISSIONS_TABLE,
                    'store_id',
                    'store',
                    'store_id'
                ),
                'store_id',
                $setup->getTable('store'),
                'store_id',
                Table::ACTION_CASCADE
            )->addForeignKey(
                $setup->getFkName(
                    self::QORDOBA_SUBMISSIONS_TABLE,
                    'preference_id',
                    self::QORDOBA_PREFERENCE_TABLE,
                    'id'
                ),
                'preference_id',
                $setup->getTable(self::QORDOBA_PREFERENCE_TABLE),
                'id',
                Table::ACTION_CASCADE
            )
            ->setComment('Customer qordoba project translation');
        $setup->getConnection()->createTable($contentTable);
    }

    /**
     * @param SchemaSetupInterface $setup
     * @throws \Zend_Db_Exception
     */
    private function createPreferencesTable(SchemaSetupInterface $setup)
    {
        $connection = $setup->getConnection();
        $preferenceTable = $connection
            ->newTable($setup->getTable(self::QORDOBA_PREFERENCE_TABLE))
            ->addColumn(
                'id',
                Table::TYPE_INTEGER,
                null,
                [
                    'identity' => true,
                    'nullable' => false,
                    'primary' => true,
                    'unsigned' => true
                ]
            )->addColumn(
                'organization_id',
                Table::TYPE_SMALLINT,
                null,
                [
                    'identity' => false,
                    'nullable' => false,
                    'primary' => false,
                    'unsigned' => true
                ]
            )->addColumn(
                'store_id',
                Table::TYPE_SMALLINT,
                null,
                [
                    'identity' => false,
                    'nullable' => false,
                    'primary' => false,
                    'unsigned' => true
                ]
            )->addColumn(
                'project_id',
                Table::TYPE_SMALLINT,
                null,
                [
                    'identity' => false,
                    'nullable' => false,
                    'primary' => false,
                    'unsigned' => true,
                ]
            )->addColumn(
                'state',
                Table::TYPE_SMALLINT,
                null,
                [
                    'identity' => false,
                    'nullable' => false,
                    'primary' => false,
                    'unsigned' => true
                ]
            )->addColumn(
                'account_token',
                Table::TYPE_TEXT,
                255,
                ['nullable' => false]
            )->addColumn(
                'email',
                Table::TYPE_TEXT,
                255
            )->addColumn(
                'password',
                Table::TYPE_TEXT,
                255,
                ['nullable' => false]
            )->addColumn(
                'created_time',
                Table::TYPE_TIMESTAMP
            )->addColumn(
                'updated_time',
                Table::TYPE_TIMESTAMP
            )->addForeignKey(
                $setup->getFkName(
                    self::QORDOBA_PREFERENCE_TABLE,
                    'store_id',
                    'store',
                    'store_id'
                ),
                'store_id',
                $setup->getTable('store'),
                'store_id',
                Table::ACTION_CASCADE
            )
            ->setComment('Customer qordoba project preferences');

        $setup->getConnection()->createTable($preferenceTable);
    }

    /**
     * @param SchemaSetupInterface $setup
     * @throws \Zend_Db_Exception
     */
    public function createEventsTable(SchemaSetupInterface $setup)
    {
        $connection = $setup->getConnection();
        $eventsTable = $connection
            ->newTable($setup->getTable(self::QORDOBA_EVENTS_TABLE))
            ->addColumn(
                'event_id',
                Table::TYPE_INTEGER,
                null,
                [
                    'identity' => true,
                    'nullable' => false,
                    'primary' => true,
                    'unsigned' => true
                ]
            )->addColumn(
                'message',
                Table::TYPE_TEXT,
                255,
                ['nullable' => false]
            )->addColumn(
                'store_id',
                Table::TYPE_SMALLINT,
                null,
                [
                    'identity' => false,
                    'nullable' => false,
                    'primary' => false,
                    'unsigned' => true
                ]
            )->addColumn(
                'content_id',
                Table::TYPE_INTEGER,
                null,
                [
                    'identity' => false,
                    'nullable' => false,
                    'primary' => false,
                    'unsigned' => true
                ]
            )->addColumn(
                'state',
                Table::TYPE_SMALLINT,
                null,
                [
                    'identity' => false,
                    'nullable' => false,
                    'primary' => false,
                    'unsigned' => true
                ]
            )->addColumn(
                'created_time',
                Table::TYPE_TIMESTAMP
            )->addColumn(
                'updated_time',
                Table::TYPE_TIMESTAMP
            )->addForeignKey(
                $setup->getFkName(
                    self::QORDOBA_EVENTS_TABLE,
                    'store_id',
                    'store',
                    'store_id'
                ),
                'store_id',
                $setup->getTable('store'),
                'store_id',
                Table::ACTION_CASCADE
            )->addForeignKey(
                $setup->getFkName(
                    self::QORDOBA_EVENTS_TABLE,
                    'content_id',
                    self::QORDOBA_SUBMISSIONS_TABLE,
                    'id'
                ),
                'content_id',
                $setup->getTable(self::QORDOBA_SUBMISSIONS_TABLE),
                'id',
                Table::ACTION_CASCADE
            );
        $setup->getConnection()->createTable($eventsTable);
    }

    /**
     * @param SchemaSetupInterface $setup
     * @throws \Zend_Db_Exception
     */
    private function createTranslatedContentTable(SchemaSetupInterface $setup)
    {
        $connection = $setup->getConnection();
        $translatedContentTable = $connection
            ->newTable($setup->getTable(self::QORDOBA_TRANSLATED_CONTENT_TABLE))
            ->addColumn(
                'id',
                Table::TYPE_INTEGER,
                null,
                [
                    'identity' => true,
                    'nullable' => false,
                    'primary' => true,
                    'unsigned' => true
                ]
            )->addColumn(
                'created_time',
                Table::TYPE_TIMESTAMP
            )->addColumn(
                'updated_time',
                Table::TYPE_TIMESTAMP
            )->addColumn(
                'content_id',
                Table::TYPE_INTEGER,
                null,
                [
                    'identity' => false,
                    'nullable' => false,
                    'primary' => false,
                    'unsigned' => true
                ]
            )->addColumn(
                'type_id',
                Table::TYPE_INTEGER,
                null,
                [
                    'identity' => false,
                    'nullable' => false,
                    'primary' => false,
                    'unsigned' => true
                ]
            )->addColumn(
                'translated_content_id',
                Table::TYPE_INTEGER,
                null,
                [
                    'identity' => false,
                    'nullable' => true,
                    'primary' => false,
                    'unsigned' => true
                ]
            )->addForeignKey(
                $setup->getFkName(
                    self::QORDOBA_TRANSLATED_CONTENT_TABLE,
                    'content_id',
                    self::QORDOBA_SUBMISSIONS_TABLE,
                    'id'
                ),
                'content_id',
                $setup->getTable(self::QORDOBA_SUBMISSIONS_TABLE),
                'id',
                Table::ACTION_CASCADE
            );
        $setup->getConnection()->createTable($translatedContentTable);
    }
}
