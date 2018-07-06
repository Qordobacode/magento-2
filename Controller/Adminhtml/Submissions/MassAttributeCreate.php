<?php
/**
 * @category Magento-2 Qordoba Connector Module
 * @package Qordoba_Connector
 * @copyright Copyright (c) 2018
 * @license https://www.qordoba.com/terms
 */

namespace Qordoba\Connector\Controller\Adminhtml\Submissions;

/**
 * Class MassAttributeCreate
 * @package Qordoba\Connector\Controller\Adminhtml\Events
 */
class MassAttributeCreate extends \Magento\Backend\App\Action implements \Qordoba\Connector\Api\Controller\ControllerInterface
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
     * @var \Magento\Catalog\Model\ResourceModel\Eav\Attribute
     */
    protected $attributesCollection;

    /**
     * MassAttributeCreate constructor.
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Qordoba\Connector\Model\ContentRepository $contentRepository
     * @param \Magento\Catalog\Model\ResourceModel\Product\Attribute\Collection $collection
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Qordoba\Connector\Model\ContentRepository $contentRepository,
        \Magento\Catalog\Model\ResourceModel\Product\Attribute\Collection $collection
    ) {
        $this->contentRepository = $contentRepository;
        $this->attributesCollection = $collection;
        parent::__construct($context);
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface|\Magento\Framework\View\Result\Page
     * @throws \Exception
     */
    public function execute()
    {
        $collectionSize = $this->attributesCollection->getSize();
        $resultRedirect = $this->resultFactory->create(\Magento\Framework\Controller\ResultFactory::TYPE_REDIRECT);
        $collectionItems = $this->attributesCollection->getItems();
        try {
            if (!$this->contentRepository->isDefaultPreferenceExist()) {
                $this->messageManager->addErrorMessage(
                    __('Submission has not been created. Please, check your confection preferences')
                );
            } else {
                foreach ($collectionItems as $item) {
                    if ($item instanceof \Magento\Eav\Api\Data\AttributeInterface
                        && ('' !== trim($item->getDefaultFrontendLabel()))) {
                        $this->contentRepository->createProductAttribute($item);
                    } elseif (0 < $collectionSize) {
                        --$collectionSize;
                    }
                }
                $this->messageManager->addSuccessMessage(__('A total of %1 record(s) have been submitted.', $collectionSize));
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
