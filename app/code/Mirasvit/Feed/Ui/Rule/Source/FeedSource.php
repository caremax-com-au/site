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


declare(strict_types=1);

namespace Mirasvit\Feed\Ui\Rule\Source;

use Magento\Framework\Data\OptionSourceInterface;
use Mirasvit\Feed\Model\ResourceModel\Feed\CollectionFactory;

class FeedSource implements OptionSourceInterface
{
    private $collectionFactory;

    public function __construct(
        CollectionFactory $collectionFactory
    ) {
        $this->collectionFactory = $collectionFactory;
    }

    public function toOptionArray(): array
    {
        $options = [];

        foreach ($this->collectionFactory->create() as $feed) {
            $options[] = [
                'value' => $feed->getId(),
                'label' => $feed->getName(),
            ];
        }

        return $options;
    }
}
