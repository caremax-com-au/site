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



namespace Mirasvit\Feed\Block\Adminhtml;

use Magento\Backend\Block\Template\Context;
use Mirasvit\Core\Block\Adminhtml\AbstractMenu;
use Mirasvit\Feed\Model\ResourceModel\Feed\CollectionFactory as FeedCollectionFactory;

class Menu extends AbstractMenu
{
    /**
     * @var FeedCollectionFactory
     */
    protected $feedCollectionFactory;

    /**
     * @param FeedCollectionFactory $feedCollectionFactory
     * @param Context               $context
     */
    public function __construct(
        FeedCollectionFactory $feedCollectionFactory,
        Context $context
    ) {
        $this->visibleAt(['mst_feed']);

        $this->feedCollectionFactory = $feedCollectionFactory;

        parent::__construct($context);
    }

    /**
     * {@inheritdoc}
     */
    protected function buildMenu()
    {
        $this->addItem([
            'id'       => 'feed',
            'resource' => 'Mirasvit_Feed::feed_feed',
            'title'    => __('Feeds'),
            'url'      => $this->urlBuilder->getUrl('mst_feed/feed'),
        ])->addItem([
            'resource' => 'Mirasvit_Feed::feed_template',
            'title'    => __('Templates'),
            'url'      => $this->urlBuilder->getUrl('mst_feed/template'),
        ])->addItem([
            'resource' => 'Mirasvit_Feed::feed_rule',
            'title'    => __('Filters'),
            'url'      => $this->urlBuilder->getUrl('mst_feed/rule'),
        ])->addItem([
            'resource' => 'Mirasvit_Feed::feed_report',
            'title'    => __('Reports'),
            'url'      => $this->urlBuilder->getUrl('mst_feed/report/view'),
        ]);

        $this->addSeparator();

        $this->addItem([
            'resource' => 'Mirasvit_Feed::feed_dynamic_category',
            'title'    => __('Category Mapping'),
            'url'      => $this->urlBuilder->getUrl('mst_feed/dynamic_category'),
        ])->addItem([
            'resource' => 'Mirasvit_Feed::feed_dynamic_attribute',
            'title'    => __('Dynamic Attributes'),
            'url'      => $this->urlBuilder->getUrl('mst_feed/dynamic_attribute'),
            /** mp comment start **/
        ])->addItem([
            'resource' => 'Mirasvit_Feed::feed_dynamic_variable',
            'title'    => __('Dynamic Variables'),
            'url'      => $this->urlBuilder->getUrl('mst_feed/dynamic_variable'),
            /** mp comment end **/
        ])->addItem([
            'resource' => 'Mirasvit_Feed::feed_import',
            'title'    => __('Import/Export Data'),
            'url'      => $this->urlBuilder->getUrl('mst_feed/import'),
        ]);

        /** @var \Mirasvit\Feed\Model\Feed $feed */
        foreach ($this->feedCollectionFactory->create() as $feed) {
            if ($feed->getName()) {
                $this->addItem([
                    'resource' => 'Mirasvit_Feed::feed_feed',
                    'title'    => $feed->getName(),
                    'url'      => $this->urlBuilder->getUrl('mst_feed/feed/edit', ['id' => $feed->getId()]),
                ], 'feed');
            }
        }

        return $this;
    }
}