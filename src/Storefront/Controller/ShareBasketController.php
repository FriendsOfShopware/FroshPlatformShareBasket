<?php
declare(strict_types=1);

namespace Frosh\ShareBasket\Storefront\Controller;

use Frosh\ShareBasket\Services\ShareBasketServiceInterface;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Shopware\Storefront\Controller\StorefrontController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route(defaults: ['_routeScope' => ['storefront']])]
class ShareBasketController extends StorefrontController
{
    public function __construct(private readonly ShareBasketServiceInterface $shareBasketService)
    {
    }

    #[Route(path: '/sharebasket/save', name: 'frontend.frosh.share-basket.save', options: ['seo' => false], defaults: ['XmlHttpRequest' => true], methods: ['POST'])]
    public function save(Request $request, SalesChannelContext $context): Response
    {
        try {
            $data = $this->shareBasketService->prepareLineItems($context);
            $froshShareBasketUrl = $this->shareBasketService->saveCart($request, $data, $context);
            $froshShareBasketState = 'cartSaved';
        } catch (\Exception) {
            $froshShareBasketState = 'cartError';
            $froshShareBasketUrl = null;
        }

        return $this->renderStorefront(
            '@Storefront/storefront/utilities/frosh-share-basket.html.twig',
            [
                'froshShareBasketState' => $froshShareBasketState,
                'froshShareBasketUrl' => $froshShareBasketUrl,
            ]
        );
    }

    #[Route(path: '/loadBasket/{basketId}', name: 'frontend.frosh.share-basket.load', methods: ['GET'])]
    public function load(Request $request, SalesChannelContext $context): Response
    {
        try {
            $this->shareBasketService->loadCart($request, $context);
            $froshShareBasketState = 'cartLoaded';
            $this->addFlash('success', $this->trans('frosh-share-basket.cartLoaded'));
        } catch (\Exception) {
            $froshShareBasketState = 'cartNotFound';
            $this->addFlash('danger', $this->trans('frosh-share-basket.cartNotFound'));
        }

        return $this->forwardToRoute(
            'frontend.checkout.cart.page',
            ['froshShareBasketState' => $froshShareBasketState]
        );
    }
}
