<?php
/**
 * @category Magento-2 Qordoba Connector Module
 * @package Qordoba_Connector
 * @copyright Copyright (c) 2018
 * @license https://www.qordoba.com/terms
 */

namespace Qordoba\Connector\Console\Command;

/**
 * Class CheckSubmissionsCommand
 * @package Qordoba\Connector\Console\Command
 */
class RepairProductsSubmissionsCommand extends \Symfony\Component\Console\Command\Command
{

    /**
     * @const int
     */
    const PRODUCT_SUBMISSIONS_COUNT = 2;

    /**
     * @var \Magento\Framework\App\State
     */
    protected $state;

    /**
     * CheckSubmissionsCommand constructor.
     *
     * @param \Magento\Framework\App\State $state
     * @param null $name
     * @throws \LogicException
     */
    public function __construct(\Magento\Framework\App\State $state, $name = null)
    {
        $this->state = $state;
        parent::__construct($name);
    }

    /**
     *
     * @throws \InvalidArgumentException
     */
    protected function configure()
    {
        $this->setName('qordoba:repair-product-submissions')
            ->setDescription('Repair Qordoba product submissions');
        parent::configure();
    }

    /**
     * @param \Symfony\Component\Console\Input\InputInterface $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     * @return int|null|void
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \InvalidArgumentException
     * @throws \RuntimeException
     * @throws \Exception
     */
    protected function execute(
        \Symfony\Component\Console\Input\InputInterface $input,
        \Symfony\Component\Console\Output\OutputInterface $output
    ) {
        $contentIdsResult = $this->getProductSubmissionTypeIdList();
        $this->state->setAreaCode(\Magento\Framework\App\Area::AREA_GLOBAL);
        foreach ($contentIdsResult as $submissionItem) {
            if (isset($submissionItem[\Qordoba\Connector\Api\Data\ContentInterface::CONTENT_ID_FIELD])) {
                $productSubmissions = $this->getSubmissionsByContentId(
                    $submissionItem[\Qordoba\Connector\Api\Data\ContentInterface::CONTENT_ID_FIELD]
                );
                if (self::PRODUCT_SUBMISSIONS_COUNT > count($productSubmissions) && isset($productSubmissions[0])) {
                    $activeSubmission = $productSubmissions[0];
                    $productModel = $this->getSubmissionProductModel(
                        $activeSubmission['content_id'],
                        $activeSubmission['store_id']
                    );
                    if ($productModel) {
                        if (!$this->isProductTitlesSubmissionExist($productSubmissions)) {
                            $this->createProductTitlesSubmission($productModel);
                            $this->printSuccessMessage($output, $productModel);
                        } elseif (!$this->isProductDescriptionSubmissionExist($productSubmissions)) {
                            $this->createProductDescriptionSubmission($productModel);
                            $this->printSuccessMessage($output, $productModel);
                        }
                    }
                    sleep(1);
                }
            }
        }
    }

    /**
     * @return array
     * @throws \RuntimeException
     */
    private function getProductSubmissionTypeIdList()
    {
        return $this->getObjectManager()->get(\Qordoba\Connector\Model\ResourceModel\Content::class)
            ->getSubmissionsContentIdsByTypes([
                \Qordoba\Connector\Api\Data\ContentInterface::TYPE_PRODUCT,
                \Qordoba\Connector\Api\Data\ContentInterface::TYPE_PRODUCT_DESCRIPTION
            ]);
    }

    /**
     * @return \Magento\Framework\App\ObjectManager
     * @throws \RuntimeException
     */
    private function getObjectManager()
    {
        return \Magento\Framework\App\ObjectManager::getInstance();
    }

    /**
     * @param null|int $contentId
     * @return array
     * @throws \RuntimeException
     */
    private function getSubmissionsByContentId($contentId = null)
    {
        $submissionList = [];
        if ((null !== $contentId) && (0 < (int)$contentId)) {
            $submissionList = $this->getObjectManager()->get(\Qordoba\Connector\Model\ResourceModel\Content::class)
                ->getByContentId($contentId);
        }
        return $submissionList;
    }

    /**
     * @param null|int $productId
     * @param null|int $storeId
     * @return mixed
     * @throws \RuntimeException
     */
    private function getSubmissionProductModel($productId = null, $storeId = null)
    {
        $productModel = null;
        $existingProductModel = null;
        if (isset($productId, $storeId)) {
            $existingProductModel = $this->getObjectManager()->get(\Magento\Catalog\Model\Product::class)->load($productId);
            if ($existingProductModel && $existingProductModel->getId()) {
                $productModel = $existingProductModel;
            }
        }
        return $productModel;
    }

    /**
     * @param array $submissions
     * @return bool
     */
    private function isProductTitlesSubmissionExist(array $submissions = [])
    {
        $isSubmissionExist = false;
        foreach ($submissions as $submission) {
            if (\Qordoba\Connector\Api\Data\ContentInterface::TYPE_PRODUCT === (int)$submission[\Qordoba\Connector\Api\Data\ContentInterface::TYPE_ID_FIELD]) {
                $isSubmissionExist = true;
                break;
            }
        }
        return $isSubmissionExist;
    }

    /**
     * @param \Magento\Framework\Model\AbstractModel $productModel
     * @throws \Exception
     */
    private function createProductTitlesSubmission(\Magento\Framework\Model\AbstractModel $productModel)
    {
        $this->getObjectManager()->get(\Qordoba\Connector\Model\ContentRepository::class)
            ->createProduct(
                $productModel,
                \Qordoba\Connector\Api\Data\ContentInterface::TYPE_PRODUCT
            );
    }

    /**
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     * @param $contentModel
     * @return mixed
     * @throws \InvalidArgumentException
     */
    private function printSuccessMessage(
        \Symfony\Component\Console\Output\OutputInterface $output,
        \Magento\Framework\Model\AbstractModel $contentModel
    ) {
        return $output->writeln(sprintf(
            'The new submission has been created with type [%s]! Active Content ID: %s',
            \Qordoba\Connector\Api\Data\ContentInterface::TYPE_PRODUCT,
            $contentModel->getId()
        ));
    }

    /**
     * @param array $submissions
     * @return bool
     */
    private function isProductDescriptionSubmissionExist(array $submissions = [])
    {

        $isSubmissionExist = false;
        foreach ($submissions as $submission) {
            if (\Qordoba\Connector\Api\Data\ContentInterface::TYPE_PRODUCT_DESCRIPTION === (int)$submission[\Qordoba\Connector\Api\Data\ContentInterface::TYPE_ID_FIELD]) {
                $isSubmissionExist = true;
                break;
            }
        }
        return $isSubmissionExist;
    }

    /**
     * @param \Magento\Framework\Model\AbstractModel $productModel
     * @throws \Exception
     */
    private function createProductDescriptionSubmission(\Magento\Framework\Model\AbstractModel $productModel)
    {
        $this->getObjectManager()->get(\Qordoba\Connector\Model\ContentRepository::class)->createProduct(
            $productModel,
            \Qordoba\Connector\Api\Data\ContentInterface::TYPE_PRODUCT_DESCRIPTION
        );
    }
}