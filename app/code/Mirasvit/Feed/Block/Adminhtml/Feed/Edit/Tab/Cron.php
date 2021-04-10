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



namespace Mirasvit\Feed\Block\Adminhtml\Feed\Edit\Tab;

use Magento\Backend\Block\Widget\Context;
use Magento\Backend\Block\Widget\Form;
use Magento\Framework\Data\FormFactory;
use Magento\Framework\Registry;
use Mirasvit\Core\Api\CronHelperInterface;
use Mirasvit\Feed\Model\Config\Source\Day as SourceDay;
use Mirasvit\Feed\Model\Config\Source\Time as SourceTime;
use Mirasvit\Core\Api\Service\CronServiceInterface;
use Magento\Framework\Stdlib\DateTime\TimezoneInterfaceFactory;
use Magento\Cron\Model\ResourceModel\Schedule\CollectionFactory as ScheduleCollectionFactory;

class Cron extends Form
{
    /**
     * @var SourceDay
     */
    protected $sourceDay;

    /**
     * @var SourceTime
     */
    protected $sourceTime;

    /**
     * @var FormFactory
     */
    protected $formFactory;

    /**
     * @var Registry
     */
    protected $registry;

    /**
     * @var CronHelperInterface
     */
    protected $cronHelper;

    /**
     * @var ScheduleCollectionFactory
     */
    private $scheduleCollectionFactory;

    /**
     * @var CronServiceInterface
     */
    protected $cronService;
    
    /**
     * @var TimezoneInterfaceFactory
     */
    protected $timezoneFactory;

    /**
     * {@inheritdoc}
     *
     * @param SourceDay   $sourceDay
     * @param SourceTime  $sourceTime
     * @param FormFactory $formFactory
     * @param Registry    $registry
     * @param CronHelperInterface  $cronHelper
     * @param Context     $context
     */
    public function __construct(
        SourceDay $sourceDay,
        SourceTime $sourceTime,
        FormFactory $formFactory,
        Registry $registry,
        CronHelperInterface $cronHelper,
        ScheduleCollectionFactory $scheduleCollectionFactory,
        CronServiceInterface $cronService,
        TimezoneInterfaceFactory $timezoneFactory,
        Context $context
    ) {
        $this->sourceDay = $sourceDay;
        $this->sourceTime = $sourceTime;
        $this->formFactory = $formFactory;
        $this->registry = $registry;
        $this->cronHelper = $cronHelper;
        $this->scheduleCollectionFactory = $scheduleCollectionFactory;
        $this->cronService = $cronService;
        $this->timezoneFactory = $timezoneFactory;

        parent::__construct($context);
    }

    /**
     * {@inheritdoc}
     */
    protected function _prepareForm()
    {
        $model = $this->registry->registry('current_model');
        $form = $this->formFactory->create();
        $form->setFieldNameSuffix('feed');
        $this->setForm($form);

        $general = $form->addFieldset('general', ['legend' => __('Scheduled Task')]);
        $general->addField('cron', 'select', [
            'name'   => 'cron',
            'label'  => __('Enabled'),
            'values' => [0 => __('No'), 1 => __('Yes')],
            'value' => $model->getCron(),
            'note'   => __(
                'If enabled, the extension will generate a feed by schedule. To generate feed by schedule, magento cron must be configured.'
            )
        ]);

        $general->addField('cron_day', 'multiselect', [
            'label'    => __('Days of the week'),
            'required' => false,
            'name'     => 'cron_day',
            'values'   => $this->sourceDay->toOptionArray(),
            'value'    => $model->getCronDay(),
        ]);

        $timeNow          = $this->timezoneFactory->create()->date()->format('h:i A');
        $cronstatus       = $this->cronService->isCronRunning(['feed_export']) ? 'OK' : 'Cron is not running';

        $lastFeedCron = '-';
        $cron = $this->scheduleCollectionFactory->create();
        $cron->addFieldToFilter('job_code', 'feed_export')
            ->addFieldToFilter('status', 'success')
            ->setOrder('executed_at', 'desc')
            ->getFirstItem()
            ->setPageSize(1);

        if ($cron->getSize()) {
            $timezone     = $this->timezoneFactory->create();
            $lastFeedCron = $timezone->date($cron->fetchItem()->getExecutedAt())->format('d.m.Y h:i A');
        }

        $message = '
            <table>
                <tr>
                    <th align="left">Current Time </th>
                    <td>'. $timeNow.'</td>
                </tr>
                <tr>
                    <th align="left">Cron Status</th>
                    <td>'. $cronstatus.'</td>
                </tr>
                <tr>
                    <th>Last Feed Cron Run&nbsp;</th>
                    <td>'. $lastFeedCron.'</td>
                </tr>
            </table>';

        $general->addField('cron_time', 'multiselect', [
            'label'    => __('Time of the day'),
            'required' => false,
            'name'     => 'cron_time',
            'values'   => $this->sourceTime->toOptionArray(),
            'value'    => $model->getCronTime(),
            'note'     => $message,
        ]);

        return parent::_prepareForm();
    }
}
