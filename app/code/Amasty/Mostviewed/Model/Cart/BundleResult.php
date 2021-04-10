<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Mostviewed
 */


declare(strict_types=1);

namespace Amasty\Mostviewed\Model\Cart;

use Magento\Catalog\Api\Data\ProductInterface;

class BundleResult
{
    /**
     * @var bool
     */
    private $hasRequiredOptions;

    /**
     * @var array
     */
    private $skippedProducts = [];

    public function isHasRequiredOptions(): bool
    {
        return $this->hasRequiredOptions;
    }

    public function setHasRequiredOptions(bool $hasRequiredOptions): void
    {
        $this->hasRequiredOptions = $hasRequiredOptions;
    }

    public function getSkippedProducts(): array
    {
        return $this->skippedProducts;
    }

    public function setSkippedProducts(array $skippedProducts): void
    {
        $this->skippedProducts = $skippedProducts;
    }

    public function addSkippedProduct(ProductInterface $product, ?string $message = null): void
    {
        $this->skippedProducts[$product->getId()] = [
            'product' => $product,
            'message' => $message
        ];
    }
}
