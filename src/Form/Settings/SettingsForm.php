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

namespace Sendy\PrestaShop\Form\Settings;

use PrestaShop\PrestaShop\Core\Form\ChoiceProvider\OrderStateByIdChoiceProvider;
use PrestaShopBundle\Form\Admin\Type\SwitchType;
use PrestaShopBundle\Translation\TranslatorInterface;
use Sendy\Api\Exceptions\SendyException;
use Sendy\PrestaShop\Enum\ProcessingMethod;
use Sendy\PrestaShop\Exception\TokensMissingException;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;

if (!defined('_PS_VERSION_')) {
    exit;
}

/**
 * @see https://devdocs.prestashop-project.org/8/modules/creation/adding-configuration-page-modern/
 */
class SettingsForm extends AbstractType
{
    private TranslatorInterface $translator;
    private OrderStateByIdChoiceProvider $orderStateByIdChoiceProvider;
    private ShopChoiceProvider $shopChoiceProvider;

    public function __construct(
        TranslatorInterface $translator,
        OrderStateByIdChoiceProvider $orderStateByIdChoiceProvider,
        ShopChoiceProvider $shopChoiceProvider
    ) {
        $this->translator = $translator;
        $this->orderStateByIdChoiceProvider = $orderStateByIdChoiceProvider;
        $this->shopChoiceProvider = $shopChoiceProvider;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $orderStatuses = $this->orderStateByIdChoiceProvider->getChoices();
        $orderStatusAttributes = $this->orderStateByIdChoiceProvider->getChoicesAttributes();

        try {
            $shops = $this->shopChoiceProvider->getChoices();
        } catch (TokensMissingException|SendyException $e) {
            $shops = [];
        }

        $builder
            ->add('sendy_processing_method', ChoiceType::class, [
                'label' => $this->translator->trans('Processing method', [], 'Modules.Sendy.Admin'),
                'choices' => ProcessingMethod::choices(),
                'help' => $this->translator->trans(
                    'You can choose to push orders to Sendy and process them in the Sendy app, or process them directly in PrestaShop.',
                    [],
                    'Modules.Sendy.Admin'
                ),
            ])
            ->add('sendy_processable_status', ChoiceType::class, [
                'label' => $this->translator->trans('Processable Order Status', [], 'Modules.Sendy.Admin'),
                'choices' => $orderStatuses,
                'choice_attr' => $orderStatusAttributes,
                'attr' => ['data-conditional' => 'sendy_processing_method=' . ProcessingMethod::Sendy],
                'help' => $this->translator->trans(
                    'Create a shipment in Sendy when an order transitions to this status.',
                    [],
                    'Modules.Sendy.Admin'
                ),
            ])
            ->add('sendy_default_shop', ChoiceType::class, [
                'label' => $this->translator->trans('Default Sendy shop', [], 'Modules.Sendy.Admin'),
                'choices' => $shops,
                'required' => false,
            ])
            ->add('sendy_import_products', SwitchType::class, [
                'label' => $this->translator->trans('Import products', [], 'Modules.Sendy.Admin'),
                'required' => false,
            ])
            ->add('sendy_import_weight', SwitchType::class, [
                'label' => $this->translator->trans('Import weight', [], 'Modules.Sendy.Admin'),
                'required' => false,
            ])
            ->add('sendy_status_generated', ChoiceType::class, [
                'label' => $this->translator->trans('Status after shipment is generated', [], 'Modules.Sendy.Admin'),
                'choices' => $orderStatuses,
                'required' => false,
                'placeholder' => $this->translator->trans('None (do not update the order status)', [], 'Modules.Sendy.Admin'),
                'help' => $this->translator->trans(
                    'When a shipping label is generated in Sendy, the order will be updated to this status.',
                    [],
                    'Modules.Sendy.Admin'
                ),
                'attr' => ['data-conditional' => 'sendy_processing_method=' . ProcessingMethod::Sendy],
            ])
            ->add('sendy_status_printed', ChoiceType::class, [
                'label' => $this->translator->trans('Status after label is printed', [], 'Modules.Sendy.Admin'),
                'choices' => $orderStatuses,
                'required' => false,
                'placeholder' => $this->translator->trans('None (do not update the order status)', [], 'Modules.Sendy.Admin'),
                'help' => $this->translator->trans(
                    'When pressing the "Print label" button in your PrestaShop back office, the order will be updated to this status.',
                    [],
                    'Modules.Sendy.Admin'
                ),
            ])
            ->add('sendy_status_delivered', ChoiceType::class, [
                'label' => $this->translator->trans('Status after shipment is delivered', [], 'Modules.Sendy.Admin'),
                'choices' => $orderStatuses,
                'required' => false,
                'placeholder' => $this->translator->trans('None (do not update the order status)', [], 'Modules.Sendy.Admin'),
                'help' => $this->translator->trans(
                    'When a shipment is delivered, the order will be updated to this status.',
                    [],
                    'Modules.Sendy.Admin'
                ),
                'attr' => ['data-conditional' => 'sendy_processing_method=' . ProcessingMethod::Sendy],
            ])
        ;
    }
}
