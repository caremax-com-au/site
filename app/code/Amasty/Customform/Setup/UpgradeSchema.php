<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Customform
 */


namespace Amasty\Customform\Setup;

use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

class UpgradeSchema implements UpgradeSchemaInterface
{
    /**
     * @var Operation\AddEmailResponse
     */
    private $addEmailResponse;

    /**
     * @var Operation\AddResponseStatus
     */
    private $addResponseStatus;

    /**
     * @var Operation\AddPopupColumns
     */
    private $addPopupColumns;

    /**
     * @var Operation\AddTitlesField
     */
    private $addTitlesField;

    /**
     * @var Operation\AddRefererUrlColumn
     */
    private $addRefererUrl;

    /**
     * @var Operation\UpdateAutoResponder
     */
    private $updateAutoResponder;

    /**
     * @var Operation\AddSurveyMode
     */
    private $addSurveyMode;

    /**
     * @var Operation\AddDesignColumn
     */
    private $addDesignColumn;

    public function __construct(
        \Amasty\Customform\Setup\Operation\AddEmailResponse $addEmailResponse,
        Operation\AddResponseStatus $addResponseStatus,
        Operation\AddPopupColumns $addPopupColumns,
        Operation\AddTitlesField $addTitlesField,
        Operation\AddRefererUrlColumn $addRefererUrlColumn,
        Operation\UpdateAutoResponder $updateAutoResponder,
        Operation\AddSurveyMode $addSurveyMode,
        Operation\AddDesignColumn $addDesignColumn
    ) {
        $this->addEmailResponse = $addEmailResponse;
        $this->addResponseStatus = $addResponseStatus;
        $this->addPopupColumns = $addPopupColumns;
        $this->addTitlesField = $addTitlesField;
        $this->addRefererUrl = $addRefererUrlColumn;
        $this->addSurveyMode = $addSurveyMode;
        $this->updateAutoResponder = $updateAutoResponder;
        $this->addDesignColumn = $addDesignColumn;
    }

    /**
     * @param SchemaSetupInterface $setup
     * @param ModuleContextInterface $context
     * @throws \Zend_Db_Exception
     */
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        if (version_compare($context->getVersion(), '1.5.0', '<')) {
            $this->addEmailResponse->execute($setup);
        }

        if (version_compare($context->getVersion(), '1.7.0', '<')) {
            $this->addResponseStatus->execute($setup);
        }

        if (version_compare($context->getVersion(), '1.8.0', '<')) {
            $this->addPopupColumns->execute($setup);
        }

        if (version_compare($context->getVersion(), '1.9.0', '<')) {
            $this->addTitlesField->execute($setup);
        }

        if (version_compare($context->getVersion(), '1.10.0', '<')) {
            $this->addRefererUrl->execute($setup);
        }

        if (version_compare($context->getVersion(), '1.11.1', '<')) {
            $this->updateAutoResponder->execute($setup);
        }

        if (version_compare($context->getVersion(), '1.13.0', '<')) {
            $this->addSurveyMode->execute($setup);
        }

        if (version_compare($context->getVersion(), '1.14.1', '<')) {
            $this->addDesignColumn->execute($setup);
        }

        $setup->endSetup();
    }
}
