<?php
/**
 * This file is part of the Sendy PrestaShop module - https://sendy.nl
 *
 * @author Sendy B.V.
 * @copyright Sendy B.V.
 * @license https://github.com/sendynl/prestashop-module/blob/master/LICENSE MIT
 *
 * @see https://github.com/sendynl/prestashop-module
 */
declare(strict_types=1);

namespace Sendy\PrestaShop\Form\CreateShipment;

use PrestaShopBundle\Translation\TranslatorInterface;
use Sendy\Api\Exceptions\SendyException;
use Sendy\PrestaShop\Exception\TokensMissingException;
use Sendy\PrestaShop\Factory\ApiConnectionFactory;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\FormBuilderInterface;

if (!defined('_PS_VERSION_')) {
    exit;
}

class CreateShipmentForm extends AbstractType
{
    private TranslatorInterface $translator;
    private ApiConnectionFactory $apiConnectionFactory;

    public function __construct(
        TranslatorInterface $translator,
        ApiConnectionFactory $apiConnectionFactory
    ) {
        $this->translator = $translator;
        $this->apiConnectionFactory = $apiConnectionFactory;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        try {
            $sendy = $this->apiConnectionFactory->buildConnectionUsingTokens();
            $shops = $sendy->shop->list();
            $preferences = $sendy->shippingPreference->list();
        } catch (TokensMissingException|SendyException $e) {
            $shops = [];
            $preferences = [];
        }

        $builder
            ->add('shop_id', ChoiceType::class, [
                'label' => $this->translator->trans('Shop', [], 'Modules.Sendy.Admin'),
                'choices' => array_column($shops, 'uuid', 'name'),
            ])
            ->add('preference_id', ChoiceType::class, [
                'label' => $this->translator->trans('Shipping profile', [], 'Modules.Sendy.Admin'),
                'choices' => array_column($preferences, 'uuid', 'name'),
            ])
            ->add('amount', NumberType::class, [
                'label' => $this->translator->trans('Amount of packages', [], 'Modules.Sendy.Admin'),
            ])
            ->add('order_ids', CollectionType::class, [
                'allow_add' => true,
                'entry_type' => HiddenType::class,
                'label' => false,
            ])
        ;
    }
}
