<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_CronScheduler
 */


namespace Amasty\CronScheduler\Controller\Adminhtml\Timeline;

use Amasty\CronScheduleList\Controller\Adminhtml\Schedule\RunCron;

class RunJobs extends RunCron
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Amasty_CronScheduler::jobs_timeline';

    protected $redirectUrl = 'amasty_cronscheduler/timeline/index';
}
