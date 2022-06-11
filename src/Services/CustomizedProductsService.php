<?php declare(strict_types=1);

namespace Frosh\ShareBasket\Services;

use Frosh\ShareBasket\Core\Content\ShareBasket\Aggregate\ShareBasketLineItem\ShareBasketLineItemEntity;
use Shopware\Core\Checkout\Cart\Cart;
use Shopware\Core\Checkout\Cart\LineItem\LineItem;
use Shopware\Core\Framework\Validation\DataBag\RequestDataBag;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Swag\CustomizedProducts\Core\Checkout\Cart\Route\AbstractAddCustomizedProductsToCartRoute;
use Swag\CustomizedProducts\Core\Checkout\CustomizedProductsCartDataCollector;
use Swag\CustomizedProducts\Storefront\Controller\CustomizedProductsCartController;
use Swag\CustomizedProducts\Template\Aggregate\TemplateConfiguration\TemplateConfigurationEntity;
use Symfony\Component\HttpFoundation\Request;

class CustomizedProductsService implements CustomizedProductsServiceInterface
{
    private ?AbstractAddCustomizedProductsToCartRoute $addCustomizedProductsToCartRoute;

    public function __construct(
        ?AbstractAddCustomizedProductsToCartRoute $addCustomizedProductsToCartRoute
    ) {
        $this->addCustomizedProductsToCartRoute = $addCustomizedProductsToCartRoute;
    }

    public function prepareCustomizedProductsLineItem(LineItem $lineItem): ?array
    {
        if (
            $this->addCustomizedProductsToCartRoute instanceof AbstractAddCustomizedProductsToCartRoute
            && $lineItem->getType() === CustomizedProductsCartDataCollector::CUSTOMIZED_PRODUCTS_TEMPLATE_LINE_ITEM_TYPE
        ) {
            if (($product = $this->getProduct($lineItem)) === null) {
                return null;
            }

            /** @var TemplateConfigurationEntity $customizedProductsConfigurationEntity */
            $customizedProductsConfigurationEntity = $lineItem->getExtension(
                CustomizedProductsCartDataCollector::CUSTOMIZED_PRODUCT_CONFIGURATION_KEY
            );

            if (!$customizedProductsConfigurationEntity instanceof TemplateConfigurationEntity) {
                return null;
            }

            return [
                'product' => $product,
                'customizedProductsConfiguration' => [
                    'templateId' => $customizedProductsConfigurationEntity->getTemplateId(),
                    'options' => $customizedProductsConfigurationEntity->getConfiguration(),
                ],
            ];
        }

        return null;
    }

    public function addCustomProduct(
        Cart $cart,
        SalesChannelContext $salesChannelContext,
        ShareBasketLineItemEntity $shareBasketLineItemEntity,
        Request $request
    ): void {
        if (!$this->addCustomizedProductsToCartRoute instanceof AbstractAddCustomizedProductsToCartRoute) {
            return;
        }

        $customFields = $shareBasketLineItemEntity->getCustomFields();

        if (!\is_array($customFields) || !\array_key_exists('customizedProductsConfiguration', $customFields)) {
            return;
        }

        $customizedProductsConfiguration = $customFields['customizedProductsConfiguration'];

        $lineItems[$shareBasketLineItemEntity->getIdentifier()] = [
            'id' => $shareBasketLineItemEntity->getIdentifier(),
            'type' => LineItem::PRODUCT_LINE_ITEM_TYPE,
            'quantity' => $shareBasketLineItemEntity->getQuantity(),
            'referencedId' => $shareBasketLineItemEntity->getIdentifier(),
            'stackable' => $shareBasketLineItemEntity->isStackable(),
            'removable' => $shareBasketLineItemEntity->isRemovable(),
        ];

        $parameters = [
            'lineItems' => $lineItems,
            CustomizedProductsCartController::CUSTOMIZED_PRODUCTS_TEMPLATE_REQUEST_PARAMETER => [
                'id' => $customizedProductsConfiguration['templateId'],
                'options' => $this->transformOptions($customizedProductsConfiguration['options']),
            ],
        ];

        $requestDataBag = new RequestDataBag($parameters);

        $request->request->add($parameters);

        $this->addCustomizedProductsToCartRoute->add(
            $requestDataBag,
            $request,
            $salesChannelContext,
            $cart
        );
    }

    private function getProduct(LineItem $lineItem): ?LineItem
    {
        foreach ($lineItem->getChildren() as $child) {
            if ($child->getType() === LineItem::PRODUCT_LINE_ITEM_TYPE) {
                return $child;
            }
        }

        return null;
    }

    private function transformOptions(array $options): array
    {
        foreach ($options as &$option) {
            if (\is_array($option) && \is_array($option['value'])) {
                if ($option['type'] === 'imageupload' || $option['type'] === 'fileupload') {
                    foreach ($option['value'] as $media) {
                        $option['media'][$media['filename']]['id'] = $media['mediaId'];
                        $option['media'][$media['filename']]['filename'] = $media['filename'];
                    }
                    unset($option['type'], $option['value']);
                } elseif (\count($option['value']) > 1) {
                    $option['values'] = [];
                    foreach ($option['value'] as $value) {
                        $option['values'][$value] = ['value' => $value];
                    }
                    unset($option['value']);
                } else {
                    $option['value'] = $option['value'][0];
                }
            }
        }
        unset($option);

        return $options;
    }
}
