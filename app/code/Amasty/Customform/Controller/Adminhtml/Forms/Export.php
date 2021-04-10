<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Customform
 */


declare(strict_types=1);

namespace Amasty\Customform\Controller\Adminhtml\Forms;

use Amasty\Customform\Api\Data\AnswerInterface;
use Magento\Backend\App\Action;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\Response\Http\FileFactory;
use Magento\Framework\Exception\NoSuchEntityException;

class Export extends \Magento\Backend\App\Action
{
    const AMASTY_CUSTOM_FORMS_EXPORT_PATH = 'amasty/custom_forms';

    /**
     * @var \Magento\Framework\Serialize\Serializer\Json
     */
    private $json;

    /**
     * @var \Magento\Framework\Filesystem
     */
    private $filesystem;

    /**
     * @var DirectoryList
     */
    protected $directory;

    /**
     * @var \Amasty\Customform\Model\AnswerRepository
     */
    protected $answerRepository;

    /**
     * @var SearchCriteriaBuilder
     */
    protected $searchCriteriaBuilder;

    /**
     * @var FileFactory
     */
    private $fileFactory;

    public function __construct(
        Action\Context $context,
        \Amasty\Customform\Model\AnswerRepository $answerRepository,
        \Magento\Framework\Serialize\Serializer\Json $json,
        \Magento\Framework\Filesystem $filesystem,
        FileFactory $fileFactory,
        SearchCriteriaBuilder $searchCriteriaBuilder
    ) {
        parent::__construct($context);
        $this->json = $json;
        $this->filesystem = $filesystem;
        $this->answerRepository = $answerRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->fileFactory = $fileFactory;
        $this->directory = $filesystem->getDirectoryWrite(DirectoryList::VAR_DIR);
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface|void
     */
    public function execute()
    {
        $formId = (int)$this->getRequest()->getParam('form_id');
        try {
            if (!$formId) {
                throw new NoSuchEntityException(__('Response was not found.'));
            }
            $this->searchCriteriaBuilder->addFilter('form_id', $formId);
            $answers = $this->answerRepository->getListFilter($this->searchCriteriaBuilder->create());
        } catch (NoSuchEntityException $exception) {
            $this->messageManager->addErrorMessage(__('This Response no longer exists.'));
            $this->_redirect('amasty_customform/forms/index');
            return;
        }
        $fileData = [];
        if ($answers) {
            $fileData = $this->exportProcess($answers, $formId);
        } else {
            $this->messageManager->addErrorMessage(__('Submitted data was not found.'));
        }

        return $fileData
            ? $this->fileFactory->create('export_' . $formId . '.csv', $fileData, 'var')
            : $this->_redirect('amasty_customform/forms/index');
    }

    /**
     * @param array $answers
     * @param int $formId
     * @return array
     */
    private function exportProcess(array $answers, $formId)
    {
        try {
            $data = $this->prepareData($answers);
            $this->directory->create(self::AMASTY_CUSTOM_FORMS_EXPORT_PATH);
            $file = sprintf('export_%s.csv', $formId);
            $stream = $this->directory->openFile($file, 'w+');
            $stream->lock();
            foreach ($data as $row) {
                $stream->writeCsv($row);
            }
            $stream->unlock();
            $stream->close();
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
            return [];
        }

        return ['type' => 'filename', 'value' => $file, 'rm' => true];
    }

    /**
     * @param array $answers
     * @return array
     */
    private function prepareData(array $answers): array
    {
        return array_merge([$this->getRow(array_shift($answers), true)], $this->getAnswersData($answers));
    }

    /**
     * @param \Amasty\Customform\Api\Data\AnswerInterface $answer
     * @param bool $isHeader
     * @return array
     */
    private function getRow(\Amasty\Customform\Api\Data\AnswerInterface $answer, $isHeader = false)
    {
        $result = [];
        foreach ($answer->getData() as $name => $value) {
            if (is_object($value)) {
                continue;
            }
            if ($name == AnswerInterface::RESPONSE_JSON) {
                $fields = $this->json->unserialize($value);
                foreach ($fields as $field) {
                    $result[] = $isHeader ? $field['label'] : $field['value'];
                }
                continue;
            }
            $result[] = $isHeader ? ucwords(str_replace('_', ' ', $name)) : $value;
        }

        return $result;
    }

    /**
     * @param array $answers
     * @return array
     */
    private function getAnswersData(array $answers): array
    {
        $resultValues = [];
        foreach ($answers as $answer) {
            $resultValues[$answer->getAnswerId()] = $this->getRow($answer);
        }

        return $resultValues;
    }
}
