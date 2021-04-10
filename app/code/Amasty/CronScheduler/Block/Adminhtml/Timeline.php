<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_CronScheduler
 */


namespace Amasty\CronScheduler\Block\Adminhtml;

use Amasty\CronScheduleList\Model\ScheduleCollectionFactory as CollectionFactory;
use Magento\Backend\Block\Template;
use Magento\Framework\Json\Helper\Data;

class Timeline extends Template
{
    protected $_template = 'Amasty_CronScheduler::timeline.phtml';

    /**
     * @var CollectionFactory
     */
    private $scheduleCollectionFactory;

    /**
     * @var Data
     */
    private $jsonHelper;

    /**
     * @var TimezoneInterface
     */
    private $timezone;

    public function __construct(
        CollectionFactory $scheduleCollectionFactory,
        Data $jsonHelper,
        Template\Context $context,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->scheduleCollectionFactory = $scheduleCollectionFactory;
        $this->jsonHelper = $jsonHelper;
        $this->timezone = $context->getLocaleDate() ?: \Magento\Framework\App\ObjectManager::getInstance()
            ->get(\Magento\Framework\Stdlib\DateTime\TimezoneInterface::class);
    }

    public function getJobsJson()
    {
        $items = $this->scheduleCollectionFactory->create()->getData();

        return $this->jsonHelper->jsonEncode($items);
    }

    public function getServerTimeDifference()
    {
        return $this->timezone->date()->format('P');
    }

    protected function _prepareLayout()
    {
        $this->getToolbar()->addChild(
            'amasty_run_cron_timeline',
            'Magento\Backend\Block\Widget\Button',
            [
                'label'   => __('Run Cron'),
                'title'   => __('Run Cron'),
                'onclick' => 'setLocation(\'' . $this->getUrl(
                        'amasty_cronscheduler/timeline/runJobs'
                    ) . '\')',
                'class'   => 'action-default primary'
            ]
        );

        return parent::_prepareLayout();
    }
}
