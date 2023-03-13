<?php
/**
 * Copyright © 2018-2023 Hokodo. All Rights Reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Hokodo\BnplCommerce\Service\Customer\Grid;

use Hokodo\BNPL\Api\HokodoEntityTypeResolverInterface;
use Hokodo\BNPL\Service\Customer\Grid\QueryConfigInterface;
use Hokodo\BnplCommerce\Model\Config\Source\EntityLevelForSave;

class QueryConfig implements QueryConfigInterface
{
    /**
     * @var HokodoEntityTypeResolverInterface
     */
    private HokodoEntityTypeResolverInterface $hokodoEntityTypeResolver;

    /**
     * @param HokodoEntityTypeResolverInterface $hokodoEntityTypeResolver
     */
    public function __construct(
        HokodoEntityTypeResolverInterface $hokodoEntityTypeResolver
    ) {
        $this->hokodoEntityTypeResolver = $hokodoEntityTypeResolver;
    }

    /**
     * Get query.
     *
     * @return string
     */
    public function getIsNullQuery(): string
    {
        $query = '!ISNULL(`hokodo_customer_table`.`company_id`)';
        $entityType = $this->hokodoEntityTypeResolver->resolve();
        if ($entityType == EntityLevelForSave::COMPANY) {
            $query = '!ISNULL(`hokodo_company_table`.`company_id`)';
        }

        return $query;
    }

    /**
     * Get additional tables.
     *
     * @return array
     */
    public function getAdditionalTables(): array
    {
        $additionalTables = [
            'hokodo_customer' => [
                'alias' => 'hokodo_customer_table',
                'condition' => 'main_table.entity_id = hokodo_customer_table.customer_id',
                'columns' => [
                    'hokodo_company_id' => 'hokodo_customer_table.company_id',
                    'is_hokodo_company_assigned' => new \Zend_Db_Expr($this->getIsNullQuery()),
                ],
            ],
        ];

        $entityType = $this->hokodoEntityTypeResolver->resolve();
        if ($entityType == EntityLevelForSave::COMPANY) {
            $additionalTables = [
                'company_advanced_customer_entity' => [
                    'alias' => 'company_customer',
                    'condition' => 'company_customer.customer_id = main_table.entity_id',
                    'columns' => [
                        'company_customer.company_id',
                    ],
                ],
                'hokodo_company' => [
                    'alias' => 'hokodo_company_table',
                    'condition' => 'company_customer.company_id = hokodo_company_table.entity_id',
                    'columns' => [
                        'hokodo_company_id' => 'hokodo_company_table.company_id',
                        'is_hokodo_company_assigned' => new \Zend_Db_Expr($this->getIsNullQuery()),
                    ],
                ],
            ];
        }

        return $additionalTables;
    }
}
