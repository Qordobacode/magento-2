<?php
/**
 * @category Magento-2 Qordoba Connector Module
 * @package Qordoba_Connector
 * @copyright Copyright (c) 2017
 * @license https://www.qordoba.com/terms
 */

namespace Qordoba\Connector\Console;

/**
 * Class CheckSubmissionsCommand
 * @package Qordoba\Connector\Console
 */
class RepairProductsSubmissionsCommand extends \Symfony\Component\Console\Command\Command
{

    /**
     * @const int
     */
    const PRODUCT_SUBMISSIONS_COUNT = 2;

    /**
     * @var \Qordoba\Connector\Api\Helper\ObjectManagerHelperInterface
     */
    protected $managerHelper;
    /**
     * @var \Qordoba\Connector\Model\ContentRepository
     */
    protected $contentRepository;

    /**
     * CheckSubmissionsCommand constructor.
     * @param \Qordoba\Connector\Api\Helper\ObjectManagerHelperInterface $managerHelper
     * @param \Magento\Framework\App\State $state
     * @param \Qordoba\Connector\Model\ContentRepository $repository
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function __construct(
        \Qordoba\Connector\Api\Helper\ObjectManagerHelperInterface $managerHelper,
        \Magento\Framework\App\State $state,
        \Qordoba\Connector\Model\ContentRepository $repository
    ) {
        $this->managerHelper = $managerHelper;
        $this->contentRepository = $repository;
        $state->setAreaCode('adminhtml');
        parent::__construct();
    }

    /**
     *
     * @throws \InvalidArgumentException
     */
    protected function configure()
    {
        $this->setName('qordoba:repair-product-submissions');
        $this->setDescription('Repair qordoba product submissions');
        parent::configure();
    }

    /**
     * @param \Symfony\Component\Console\Input\InputInterface $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     * @return int|null|void
     * @throws \InvalidArgumentException
     * @throws \RuntimeException
     * @throws \Exception
     */
    protected function execute(
        \Symfony\Component\Console\Input\InputInterface $input,
        \Symfony\Component\Console\Output\OutputInterface $output
    ) {
        $contentIdsResult = $this->getProductSubmissionTypeIdList();
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
        return $this->managerHelper->get(\Qordoba\Connector\Model\ResourceModel\Content::class)
            ->getSubmissionsContentIdsByTypes([
                \Qordoba\Connector\Api\Data\ContentInterface::TYPE_PRODUCT,
                \Qordoba\Connector\Api\Data\ContentInterface::TYPE_PRODUCT_DESCRIPTION
            ]);
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
            $submissionList = $this->managerHelper->get(\Qordoba\Connector\Model\ResourceModel\Content::class)
                ->getByContentId($contentId);
        }
        return $submissionList;
    }

    /**
     * @param null|int $productId
     * @param null|int $storeId
     * @return mixed
     */
    private function getSubmissionProductModel($productId = null, $storeId = null)
    {
        $productModel = null;
        $existingProductModel = null;
        if (isset($productId, $storeId)) {
            $existingProductModel = $this->managerHelper->create(\Magento\Catalog\Model\ProductRepository::class)
                ->getById($productId, false, $storeId);
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
        $this->contentRepository->createProduct(
            $productModel,
            \Qordoba\Connector\Api\Data\ContentInterface::TYPE_PRODUCT
        );
    }

    /**
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     * @param $contentModel
     * @return mixed
     */
    private function printSuccessMessage(\Symfony\Component\Console\Output\OutputInterface $output, $contentModel)
    {
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
        $this->contentRepository->createProduct(
            $productModel,
            \Qordoba\Connector\Api\Data\ContentInterface::TYPE_PRODUCT_DESCRIPTION
        );
    }
}
