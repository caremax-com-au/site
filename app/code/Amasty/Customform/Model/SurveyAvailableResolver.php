<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Customform
 */


declare(strict_types=1);

namespace Amasty\Customform\Model;

use Amasty\Customform\Api\Data\AnswerInterface;
use Magento\Customer\Model\SessionFactory;
use Magento\Framework\Api\FilterFactory;
use Magento\Framework\Api\Search\FilterGroupFactory;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\App\Http\Context as HttpContext;
use Magento\Framework\Api\SearchCriteria;

class SurveyAvailableResolver
{
    /**
     * @var AnswerRepository
     */
    private $answerRepository;

    /**
     * @var FilterFactory
     */
    private $filterFactory;

    /**
     * @var FilterGroupFactory
     */
    private $filterGroupFactory;

    /**
     * @var SessionFactory
     */
    private $customerSessionFactory;

    /**
     * @var HttpContext
     */
    private $httpContext;

    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    private $messageManager;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @var \Amasty\Customform\Helper\Data
     */
    private $helper;

    public function __construct(
        AnswerRepository $answerRepository,
        FilterFactory $filterFactory,
        FilterGroupFactory $filterGroupFactory,
        SessionFactory $customerSessionFactory,
        HttpContext $httpContext,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Amasty\Customform\Helper\Data $helper,
        SearchCriteriaBuilder $searchCriteriaBuilder
    ) {

        $this->answerRepository = $answerRepository;
        $this->filterFactory = $filterFactory;
        $this->filterGroupFactory = $filterGroupFactory;
        $this->customerSessionFactory = $customerSessionFactory;
        $this->httpContext = $httpContext;
        $this->messageManager = $messageManager;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->helper = $helper;
    }

    public function isSurveyAvailable(int $formId): bool
    {
        try {
            $list = $this->answerRepository->getListFilter($this->prepareSearchCriteria($formId));
        } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
            $list = [];
        }

        return !count($list);
    }

    private function prepareSearchCriteria(int $formId): SearchCriteria
    {
        $filter = $this->filterFactory->create()->setField(AnswerInterface::FORM_ID)
            ->setValue($formId)
            ->setConditionType('eq');
        $filterGroup1 = $this->filterGroupFactory->create()->setFilters([$filter]);

        if ($this->httpContext->getValue(\Magento\Customer\Model\Context::CONTEXT_AUTH)) {
            $filters[] = $this->filterFactory->create()->setField(AnswerInterface::CUSTOMER_ID)
                ->setValue($this->customerSessionFactory->create()->getId())
                ->setConditionType('eq');
        }
        $filters[] = $this->filterFactory->create()->setField(AnswerInterface::IP)
            ->setValue($this->helper->getCurrentIp())
            ->setConditionType('eq');
        $filterGroup2 = $this->filterGroupFactory->create()->setFilters($filters);
        $this->searchCriteriaBuilder->setFilterGroups([$filterGroup1, $filterGroup2]);

        return $this->searchCriteriaBuilder->create();
    }
}
