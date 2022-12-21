<?php

declare(strict_types=1);

namespace Hokodo\BnplCommerce\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class Company extends AbstractDb
{
    /**
     * @var string
     */
    protected $_eventPrefix = 'hokodo_company_resource_model';

    /**
     * Initialize resource model.
     */
    protected function _construct()
    {
        $this->_init('hokodo_company', 'id');
        $this->_useIsObjectNew = true;
    }
}
