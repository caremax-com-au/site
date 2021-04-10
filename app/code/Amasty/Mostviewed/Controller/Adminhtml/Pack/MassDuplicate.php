<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Mostviewed
 */


namespace Amasty\Mostviewed\Controller\Adminhtml\Pack;

use Amasty\Mostviewed\Api\Data\PackInterface;

class MassDuplicate extends AbstractMassAction
{
    /**
     * {@inheritdoc}
     */
    protected function itemAction(PackInterface $pack)
    {
        $this->repository->duplicate($pack);
    }

    /**
     * {@inheritdoc}
     */
    protected function getSuccessMessage($collectionSize = 0)
    {
        return __('A total of %1 record(s) have been duplicated.', $collectionSize);
    }
}
