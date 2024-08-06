<?php
namespace Vdcstore\TickTerms\Helper;

use Magento\Catalog\Api\ProductRepositoryInterface;
use \Magento\Framework\App\Helper\AbstractHelper;


class Data extends AbstractHelper
{

      protected $productRepository; 
      protected $_storeManager; 

      public function __construct(
          ProductRepositoryInterface $productRepository
      ) {
              $this->productRepository = $productRepository;
        }

      public function getProduct($productId)
      {
        return $this->productRepository->getById($productId);
      }
}