<?php
declare(strict_types=1);

namespace Frosh\ShareBasket\Storefront\Controller;

use Frosh\ShareBasket\Services\CustomerShareBasketServiceInterface;
use Frosh\ShareBasket\Services\ShareBasketServiceInterface;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Shopware\Core\System\SystemConfig\SystemConfigService;
use Shopware\Storefront\Controller\StorefrontController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route(defaults: ['_routeScope' => ['storefront']])]
class ShareBasketController extends StorefrontController
{
    public function __construct(
        private readonly ShareBasketServiceInterface $shareBasketService,
        private readonly CustomerShareBasketServiceInterface $customerShareBasketService,
        private readonly SystemConfigService $systemConfigService
    ) {
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

    #[Route(path: 'account/saved-carts', name: 'frontend.frosh.share-basket.account.saved-carts', methods: ['GET'], defaults: ['_loginRequired' => true])]
    public function accountLoadBaskets(SalesChannelContext $context): Response
    {
        $showSavedCarts = $this->systemConfigService->getBool(
            'FroshPlatformShareBasket.config.showSavedCartsInCustomerAccount',
            $context->getSalesChannelId()
        );

        if ($showSavedCarts === false) {
            $this->addFlash('info', $this->trans('frosh-share-basket.savedCartsOverviewDisabled'));

            return $this->redirectToRoute('frontend.account.home.page');
        }

        return $this->renderStorefront(
            '@Storefront/storefront/page/account/saved-carts/index.html.twig',
            [
                'froshSavedBaskets' => $this->customerShareBasketService->loadCustomerCarts($context),
            ]
        );
    }

    #[Route(path: 'account/saved-carts/delete/{id}', name: 'frontend.frosh.share-basket.account.saved-carts-delete', methods: ['GET'], defaults: ['_loginRequired' => true])]
    public function accountDeleteBasket(string $id, Request $request, SalesChannelContext $context): Response
    {
        $showSavedCarts = $this->systemConfigService->getBool(
            'FroshPlatformShareBasket.config.showSavedCartsInCustomerAccount',
            $context->getSalesChannelId()
        );

        if ($showSavedCarts === false) {
            $this->addFlash('info', $this->trans('frosh-share-basket.savedCartsOverviewDisabled'));

            return $this->redirectToRoute('frontend.account.home.page');
        }

        $this->customerShareBasketService->removeCustomerCart($id, $context);
        $request->getSession()->remove('froshShareBasketHash');

        $this->addFlash('success', $this->trans('frosh-share-basket.cartRemoved'));

        return $this->redirectToRoute('frontend.frosh.share-basket.account.saved-carts');
    }
}
