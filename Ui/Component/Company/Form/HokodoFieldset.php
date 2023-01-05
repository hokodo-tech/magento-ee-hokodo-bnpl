<?php

declare(strict_types=1);

namespace Hokodo\BnplCommerce\Ui\Component\Company\Form;

use Hokodo\BNPL\Gateway\Config\Config;
use Hokodo\BnplCommerce\Model\Config\Source\EntityLevelForSave;
use Magento\Framework\View\Element\UiComponent\ContextInterface;

class HokodoFieldset extends \Magento\Ui\Component\Form\Fieldset
{
    /**
     * @var Config
     */
    private Config $paymentConfig;

    /**
     * HokodoFieldset constructor.
     *
     * @param Config           $paymentConfig
     * @param ContextInterface $context
     * @param array            $components
     * @param array            $data
     */
    public function __construct(
        Config $paymentConfig,
        ContextInterface $context,
        array $components = [],
        array $data = []
    ) {
        parent::__construct(
            $context,
            $components,
            $data
        );
        $this->paymentConfig = $paymentConfig;
    }

    /**
     * Prepare component for render.
     *
     * @return void
     */
    public function prepare()
    {
        $config = $this->getData('config');
        $config['visible'] = $this->paymentConfig->getEntityLevel() === EntityLevelForSave::COMPANY;
        $this->setData('config', $config);
        parent::prepare();
    }
}
