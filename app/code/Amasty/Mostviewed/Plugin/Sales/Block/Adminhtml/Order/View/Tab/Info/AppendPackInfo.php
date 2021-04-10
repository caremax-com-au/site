<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Mostviewed
 */


declare(strict_types=1);

namespace Amasty\Mostviewed\Plugin\Sales\Block\Adminhtml\Order\View\Tab\Info;

use Amasty\Mostviewed\Model\Pack\Order\GetPacks;
use Magento\Backend\Block\Template;
use Magento\Framework\View\LayoutInterface;
use Magento\Sales\Block\Adminhtml\Order\View\Tab\Info;

class AppendPackInfo
{
    const PACK_BLOCK_NAME = 'mostviewed_pack_info';

    /**
     * @var LayoutInterface
     */
    private $layout;

    /**
     * @var GetPacks
     */
    private $getPacks;

    public function __construct(
        LayoutInterface $layout,
        GetPacks $getPacks
    ) {
        $this->layout = $layout;
        $this->getPacks = $getPacks;
    }

    public function afterGetItemsHtml(Info $subject, string $result): string
    {
        if ($orderPacks = $this->getPacks->execute((int) $subject->getOrder()->getId())) {
            /** @var Template $packInfoBlock */
            $packInfoBlock = $this->layout->getBlock(self::PACK_BLOCK_NAME);
            if ($packInfoBlock) {
                $packInfoBlock->setData('packs', $orderPacks);
                $result .= $packInfoBlock->toHtml();
            }
        }

        return $result;
    }
}
