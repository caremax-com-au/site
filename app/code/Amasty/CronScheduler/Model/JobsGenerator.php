<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_CronScheduler
 */


namespace Amasty\CronScheduler\Model;

use Amasty\CronScheduler\Model\JobsFactory;
use Amasty\CronScheduler\Model\Repository\JobsRepository;
use Magento\Cron\Model\Config\Data as ConfigData;

class JobsGenerator
{
    /*
     * @see Magento_Cron:etc/crontab.xsd jobDeclaration
     */
    const REQUIRED_JOB_ATTRIBUTES = ['name', 'instance', 'method'];

    /**
     * @var ConfigData
     */
    private $cronConfig;

    /**
     * @var JobsRepository
     */
    private $jobsRepository;

    /**
     * @var JobsFactory
     */
    private $jobsFactory;

    public function __construct(
        ConfigData $cronConfig,
        JobsRepository $jobsRepository,
        JobsFactory $jobsFactory
    ) {
        $this->cronConfig = $cronConfig;
        $this->jobsRepository = $jobsRepository;
        $this->jobsFactory = $jobsFactory;
    }

    public function execute()
    {
        $allJobs = $this->cronConfig->getJobs();
        $jobsFromDb = $this->jobsRepository->getAll();
        $savedJobs = [];
        $jobsToAdd = [];
        $jobsToDelete = [];

        /** @var Jobs $savedJob */
        foreach ($jobsFromDb as $savedJob) {
            $savedJobs[$savedJob->getJobCode()] = $savedJob;
        }

        foreach ($allJobs as $groupId => $jobs) {
            foreach ($jobs as $jobCode => $job) {
                if ($jobCode === "amasty_cron_activity") {
                    continue;
                }

                if (!array_key_exists($jobCode, $savedJobs)) {
                    if ($this->isValidJobData($job)) {
                        /** @var Jobs $jobToSave */
                        $jobToSave = $this->jobsFactory->create();
                        $jobToSave->setGroup($groupId);
                        $jobToSave->setInstance($job['instance']);
                        $jobToSave->setMethod($job['method']);
                        $jobToSave->setJobCode($job['name']);
                        $jobToSave->setSchedule(isset($job['schedule']) ? $job['schedule'] : '');
                        $jobToSave->setStatus(true);
                        array_push($jobsToAdd, $jobToSave);
                    }
                } elseif (isset($job['schedule']) && $job['schedule'] !== $savedJobs[$jobCode]->getSchedule()) {
                    $savedJobs[$jobCode]->setSchedule($job['schedule']);
                    array_push($jobsToAdd, $savedJobs[$jobCode]);
                }
            }

            foreach ($jobsFromDb as $job) {
                if ($job->getGroup() === $groupId && !array_key_exists($job->getJobCode(), $jobs)) {
                    array_push($jobsToDelete, $job);
                }
            }
        }

        foreach ($jobsToAdd as $newJob) {
            $this->jobsRepository->save($newJob);
        }

        foreach ($jobsToDelete as $deletedJob) {
            $this->jobsRepository->delete($deletedJob);
        }
    }

    /**
     * @param array $job
     *
     * @return bool
     */
    private function isValidJobData(array $job)
    {
        $isValid = true;

        foreach (self::REQUIRED_JOB_ATTRIBUTES as $attribute) {
            if (!array_key_exists($attribute, $job)
                || '' === $job[$attribute]
            ) {
                $isValid = false;
                break;
            }
        }

        return $isValid;
    }
}
