<?php
/**
 * Mirasvit
 *
 * This source file is subject to the Mirasvit Software License, which is available at https://mirasvit.com/license/.
 * Do not edit or add to this file if you wish to upgrade the to newer versions in the future.
 * If you wish to customize this module for your needs.
 * Please refer to http://www.magentocommerce.com for more information.
 *
 * @category  Mirasvit
 * @package   mirasvit/module-feed
 * @version   1.1.30
 * @copyright Copyright (C) 2021 Mirasvit (https://mirasvit.com/)
 */



namespace Mirasvit\Feed\Factory;

use Mirasvit\Feed\Api\Factory\EntityFactoryInterface;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Mirasvit\Feed\Model\TemplateFactory;
use Mirasvit\Feed\Model\RuleFactory;
use Mirasvit\Feed\Model\Dynamic\AttributeFactory;
use Mirasvit\Feed\Model\Dynamic\CategoryFactory;
/** mp comment start **/
use Mirasvit\Feed\Model\Dynamic\VariableFactory;

/** mp comment end **/


class EntityFactory implements EntityFactoryInterface
{
    /**
     * @var VariableFactory
     */
    private $variable;
    /**
     * @var CategoryFactory
     */
    private $category;
    /**
     * @var AttributeFactory
     */
    private $attribute;
    /**
     * @var RuleFactory
     */
    private $rule;
    /**
     * @var TemplateFactory
     */
    private $template;
    /**
     * @var Context
     */
    private $context;

    /**
     * EntityFactory constructor.
     * @param Context $context
     * @param TemplateFactory $template
     * @param RuleFactory $rule
     * @param AttributeFactory $attribute
     * @param VariableFactory $variable
     * @param CategoryFactory $category
     */
    public function __construct(
        Context $context,
        TemplateFactory $template,
        RuleFactory $rule,
        AttributeFactory $attribute,
        /** mp comment start **/
        VariableFactory $variable,
        /** mp comment end **/
        CategoryFactory $category
    ) {
        $this->context = $context;
        $this->template = $template;
        $this->rule = $rule;
        $this->attribute = $attribute;
        $this->category = $category;
        /** mp comment start **/
        $this->variable = $variable;
        /** mp comment end **/
    }

    /**
     * {@inheritdoc}
     */
    public function getEntityModelFactory($entityName)
    {
        switch ($entityName) {
            case 'template':
                $entityModel = $this->template->create();
                break;

            case 'rule':
                $entityModel = $this->rule->create();
                break;

            case 'dynamic_attribute':
                $entityModel = $this->attribute->create();
                break;

            case 'dynamic_category':
                $entityModel = $this->category->create();
                break;
            /** mp comment start **/
            case 'dynamic_variable':
                $entityModel = $this->variable->create();
                break;
            /** mp comment end **/
            default:
                $entityModel = '';
                break;
        }

        return $entityModel;
    }
}
