<?php
/**
 * @category Magento-2 Qordoba Connector Module
 * @package Qordoba_Connector
 * @copyright Copyright (c) 2019
 * @license https://www.qordoba.com/terms
 */

namespace Qordoba\Connector\Observer;

/**
 * Class AfterDeleteContent
 * @package Qordoba\Connector\Observer
 */
class AfterDeleteContent implements \Magento\Framework\Event\ObserverInterface
{

    /**
     * @var \Qordoba\Connector\Model\ContentRepository
     */
    private $contentRepository;

    /**
     * AfterDeletePage constructor.
     * @param \Qordoba\Connector\Model\ContentRepository $contentRepository
     */
    public function __construct(\Qordoba\Connector\Model\ContentRepository $contentRepository)
    {
        $this->contentRepository = $contentRepository;
    }

    /**
     * @param \Magento\Framework\Event $event
     * @return \Magento\Framework\Model\AbstractModel|null
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function getObjectModel(\Magento\Framework\Event $event)
    {
        $object = $event->getObject();
        if (($object instanceof \Magento\Framework\Model\AbstractModel) && $object->getId()) {
            return $object;
        }
        $object = $event->getProduct();
        if (($object instanceof \Magento\Framework\Model\AbstractModel) && $object->getId()) {
            return $object;
        }
        $object = $event->getCategory();
        if (($object instanceof \Magento\Framework\Model\AbstractModel) && $object->getId()) {
            return $object;
        }
        $object = $event->getAttribute();
        if (($object instanceof \Magento\Framework\Model\AbstractModel) && $object->getId()) {
            return $object;
        }
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     * @return AfterDeleteContent
     * @throws \Magento\Framework\Exception\CouldNotDeleteException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $object = $this->getObjectModel($observer->getEvent());
        if ($object) {
            $this->contentRepository->deleteByContent($object);
        }
        return $this;
    }
}
