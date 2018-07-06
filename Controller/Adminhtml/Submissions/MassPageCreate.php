<?php
/**
 * @category Magento-2 Qordoba Connector Module
 * @package Qordoba_Connector
 * @copyright Copyright (c) 2018
 * @license https://www.qordoba.com/terms
 */

namespace Qordoba\Connector\Controller\Adminhtml\Submissions;

/**
 * Class MassDelete
 * @package Qordoba\Connector\Controller\Adminhtml\Events
 */
class MassPageCreate extends \Magento\Backend\App\Action implements \Qordoba\Connector\Api\Controller\ControllerInterface
{
    /**
     * @const string
     */
    const ADMIN_RESOURCE = 'Qordoba_Connector::submissions';
    /**
     * @var \Magento\Ui\Component\MassAction\Filter
     */
    protected $filter;
    /**
     * @var \Qordoba\Connector\Model\ContentRepository
     */
    protected $contentRepository;
    /**
     * @var \Qordoba\Connector\Model\ResourceModel\Event\CollectionFactory
     */
    protected $collectionFactory;

    /**
     * MassPageCreate constructor.
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Ui\Component\MassAction\Filter $filter
     * @param \Qordoba\Connector\Model\ContentRepository $repository
     * @param \Magento\Cms\Model\ResourceModel\Page\CollectionFactory $collectionFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Ui\Component\MassAction\Filter $filter,
        \Qordoba\Connector\Model\ContentRepository $repository,
        \Magento\Cms\Model\ResourceModel\Page\CollectionFactory $collectionFactory
    ) {
        $this->filter = $filter;
        $this->collectionFactory = $collectionFactory;
        $this->contentRepository = $repository;
        parent::__construct($context);
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface|void
     * @throws \InvalidArgumentException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function execute()
    {
        $collection = $this->filter->getCollection($this->collectionFactory->create());
        $pageIds = $collection->getAllIds();
        $resultRedirect = $this->resultFactory->create(\Magento\Framework\Controller\ResultFactory::TYPE_REDIRECT);
        try {
            if (!$this->contentRepository->isDefaultPreferenceExist()) {
                $this->messageManager->addErrorMessage(
                    __('Submission has not been created. Please, check your confection preferences')
                );
            } else {
                foreach ($pageIds as $id) {
                    $pageModel = $this->_objectManager->create(\Magento\Cms\Model\Page::class)->load($id);
                    if ($pageModel && $pageModel->getId()) {
                        $this->contentRepository->createPage($pageModel, \Qordoba\Connector\Model\Content::TYPE_PAGE_CONTENT);
                        $this->contentRepository->createPage($pageModel, \Qordoba\Connector\Model\Content::TYPE_PAGE);
                    }
                }
                $this->messageManager->addSuccessMessage(__('A total of %1 record(s) have been submitted.', $collection->getSize() * 2));
            }
        } catch (\Exception $e) {
            $this->messageManager->addExceptionMessage($e, __('Something went wrong while saving the data.'));
        }
        return $resultRedirect->setPath('*/*/');
    }

    /**
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed(self::ADMIN_RESOURCE);
    }
}
