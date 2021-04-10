<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Mostviewed
 */


declare(strict_types=1);

namespace Amasty\Mostviewed\Model\Cart;

use Amasty\Mostviewed\Model\ConfigProvider;
use Amasty\Mostviewed\Model\Pack\Cart\ProductRegistry;
use Exception;
use Magento\Bundle\Model\Product\Type as Bundle;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product\Type;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\Downloadable\Model\Product\Type as Downloadable;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Message\ManagerInterface as MessageManagerInterface;
use Magento\GroupedProduct\Model\Product\Type\Grouped;

class AddProductsByIds
{
    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var BundleResultFactory
     */
    private $bundleResultFactory;

    /**
     * @var ConfigProvider
     */
    private $configProvider;

    /**
     * @var CheckoutSession
     */
    private $checkoutSession;

    /**
     * @var MessageManagerInterface
     */
    private $messageManager;

    /**
     * @var ProductRegistry
     */
    private $productRegistry;

    public function __construct(
        ProductRepositoryInterface $productRepository,
        CheckoutSession $checkoutSession,
        ConfigProvider $configProvider,
        BundleResultFactory $bundleResultFactory,
        ProductRegistry $productRegistry,
        MessageManagerInterface $messageManager
    ) {
        $this->productRepository = $productRepository;
        $this->bundleResultFactory = $bundleResultFactory;
        $this->configProvider = $configProvider;
        $this->checkoutSession = $checkoutSession;
        $this->messageManager = $messageManager;
        $this->productRegistry = $productRegistry;
    }

    public function execute(array $productIds): BundleResult
    {
        /** @var BundleResult $bundleResult */
        $bundleResult = $this->bundleResultFactory->create();

        $hasRequiredOptions = false;
        foreach ($productIds as $productId => $qty) {
            $productId = (int) $productId;
            if (!$productId) {
                continue;
            }

            try {
                $product = $this->productRepository->getById($productId);
                if (!$product->isSalable() || !$product->isVisibleInCatalog()) {
                    $this->messageManager->addErrorMessage(__(
                        'We can\'t add %1 to your shopping cart right now.',
                        $product->getName()
                    ));
                    continue;
                }
            } catch (NoSuchEntityException $e) {
                continue;
            }

            $productHasRequiredOptions = $this->isProductHasRequiredOptions($product);
            $hasRequiredOptions = $hasRequiredOptions || $productHasRequiredOptions;
            $shopPopupOptions = $productHasRequiredOptions
                || ($this->configProvider->isShowAllOptions() && $product->getOptions());

            if ($shopPopupOptions) {
                $bundleResult->addSkippedProduct($product);
            } else {
                try {
                    $result = $this->checkoutSession->getQuote()->addProduct($product, $qty);
                    if (is_string($result)) {
                        $bundleResult->addSkippedProduct($product, $result);
                    } else {
                        $this->productRegistry->addProduct((int) $product->getId(), [
                            'qty' => (float) $qty
                        ]);
                    }
                } catch (Exception $e) {
                    $bundleResult->addSkippedProduct($product, $e->getMessage());
                }
            }
        }
        $bundleResult->setHasRequiredOptions($hasRequiredOptions);

        return $bundleResult;
    }

    private function isProductHasRequiredOptions(ProductInterface $product): bool
    {
        switch ($product->getTypeId()) {
            case Type::TYPE_SIMPLE:
            case Type::TYPE_VIRTUAL:
                $result = $product->getTypeInstance()->hasRequiredOptions($product);
                break;
            case Configurable::TYPE_CODE:
            case Grouped::TYPE_CODE:
            case Bundle::TYPE_CODE:
            case Downloadable::TYPE_DOWNLOADABLE:
            case 'amgiftcard':
            default:
                $result = true;
        }

        return $result;
    }
}
