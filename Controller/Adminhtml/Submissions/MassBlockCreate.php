<?php
/**
 * @category Magento-2 Qordoba Connector Module
 * @package Qordoba_Connector
 * @copyright Copyright (c) 2019
 * @license https://www.qordoba.com/terms
 */

namespace Qordoba\Connector\Controller\Adminhtml\Submissions;

/**
 * Class MassBlockCreate
 * @package Qordoba\Connector\Controller\Adminhtml\Events
 */
class MassBlockCreate extends \Magento\Backend\App\Action implements \Qordoba\Connector\Api\Controller\ControllerInterface
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
     * MassBlockCreate constructor.
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Ui\Component\MassAction\Filter $filter
     * @param \Qordoba\Connector\Model\ContentRepository $repository
     * @param \Magento\Cms\Model\ResourceModel\Block\CollectionFactory $collectionFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Ui\Component\MassAction\Filter $filter,
        \Qordoba\Connector\Model\ContentRepository $repository,
        \Magento\Cms\Model\ResourceModel\Block\CollectionFactory $collectionFactory
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
        $collection = $this->applySelection($this->collectionFactory->create());
        $blockIds = $collection->getAllIds();
        $resultRedirect = $this->resultFactory->create(\Magento\Framework\Controller\ResultFactory::TYPE_REDIRECT);
        try {
            if (!$this->contentRepository->isDefaultPreferenceExist()) {
                $this->messageManager->addErrorMessage(
                    __('Submission has not been created. Please, check your connection preferences')
                );
            } else {
                foreach ($blockIds as $id) {
                    $blockModel = $this->_objectManager->create(\Magento\Cms\Model\Block::class)->load($id);
                    if ($blockModel && $blockModel->getId()) {
                        $this->contentRepository->createBlock($blockModel);
                    }
                }
                $this->messageManager->addSuccessMessage(
                    __('A total of %1 record(s) have been submitted.',
                        $collection->getSize())
                );
            }
        } catch (\Exception $e) {
            $this->messageManager->addExceptionMessage($e, __('Something went wrong while saving the data.'));
        }
        return $resultRedirect->setPath('*/*/');
    }

    /**
     * @param \Magento\Framework\Data\Collection\AbstractDb $collection
     * @return \Magento\Framework\Data\Collection\AbstractDb
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function applySelection(\Magento\Framework\Data\Collection\AbstractDb $collection)
    {
        $selected = $this->_request->getParam(\Magento\Ui\Component\MassAction\Filter::SELECTED_PARAM);
        $excluded = $this->_request->getParam(\Magento\Ui\Component\MassAction\Filter::EXCLUDED_PARAM);

        if ('false' === $excluded) {
            return $collection;
        }
        try {
            if (is_array($excluded) && !empty($excluded)) {
                $collection->addFieldToFilter($collection->getIdFieldName(), ['nin' => $excluded]);
            } elseif (is_array($selected) && !empty($selected)) {
                $collection->addFieldToFilter($collection->getIdFieldName(), ['in' => $selected]);
            } else {
                throw new \Magento\Framework\Exception\LocalizedException(__('Please select item(s).'));
            }
        } catch (\Exception $e) {
            throw new \Magento\Framework\Exception\LocalizedException(__($e->getMessage()));
        }
        return $collection;
    }

    /**
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed(self::ADMIN_RESOURCE);
    }
}
