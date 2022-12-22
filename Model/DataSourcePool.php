<?php

declare(strict_types=1);

namespace Hokodo\BnplCommerce\Model;

class DataSourcePool implements DataSourcePoolInterface
{
    /**
     * @var array
     */
    private array $dataSourceTypes;

    public function get($type)
    {
        try {
            return $this->dataSourceTypes[$type];
        } catch (\Exception $e) {
            throw new LocalizedException(__('Data Source "%1" doesn\'t exist', $type), $e);
        }
    }
}