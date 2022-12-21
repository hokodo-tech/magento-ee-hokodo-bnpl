<?php

declare(strict_types=1);

namespace Hokodo\BnplCommerce\Model;

use Hokodo\BnplCommerce\Model\ResourceModel\Company as ResourceModel;
use Magento\Framework\Model\AbstractModel;

class Company extends AbstractModel
{
    /**
     * @var string
     */
    protected $_eventPrefix = 'hokodo_company_model';

    /**
     * Initialize magento model.
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(ResourceModel::class);
    }
}
