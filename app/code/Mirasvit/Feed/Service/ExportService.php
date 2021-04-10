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



namespace Mirasvit\Feed\Service;

use Magento\Framework\Message\ManagerInterface as MessageManagerInterface;
use Mirasvit\Core\Service\YamlService;
use Mirasvit\Feed\Api\Service\ExportServiceInterface;
use Mirasvit\Feed\Model\Config;

class ExportService implements ExportServiceInterface
{
    /**
     * @var YamlService
     */
    protected $yaml;

    /**
     * @var Config
     */
    protected $config;

    /**
     * @var MessageManagerInterface
     */
    protected $messageManager;

    /**
     * {@inheritdoc}
     * @param YamlService             $yaml
     * @param Config                  $config
     * @param MessageManagerInterface $messageManager
     */
    public function __construct(
        YamlService $yaml,
        Config $config,
        MessageManagerInterface $messageManager
    ) {
        $this->yaml           = $yaml;
        $this->config         = $config;
        $this->messageManager = $messageManager;
    }

    /**
     * {@inheritdoc}
     */
    public function export($entityModel, $relativePath)
    {
        $absPath = $this->config->absolutePath($relativePath);

        $yaml = $this->yaml->dump(
            $entityModel->toArray($entityModel->getRowsToExport()),
            10
        );

        if (is_writeable(dirname($absPath))) {
            file_put_contents($absPath, $yaml);

            return true;
        } else {
            $this->messageManager->addWarningMessage(
                __(
                    'There is no permission to export files. Please set Write access to "%1"',
                    $this->config->printPath($relativePath)
                )
            );
        }

        return false;
    }
}