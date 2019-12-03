<?php declare(strict_types=1);

namespace Frosh\ShareBasket\Storefront\Controller;

use Frosh\ShareBasket\Services\ShareBasketService;
use Shopware\Core\Checkout\Cart\Cart;
use Shopware\Core\Framework\Routing\Annotation\RouteScope;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Shopware\Storefront\Controller\StorefrontController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @RouteScope(scopes={"storefront"})
 */
class ShareBasketController extends StorefrontController
{
    /**
     * @var ShareBasketService
     */
    private $shareBasketService;

    public function __construct(ShareBasketService $shareBasketService)
    {
        $this->shareBasketService = $shareBasketService;
    }

    /**
     * @Route("/sharebasket/save", name="frontend.frosh.share-basket.save", options={"seo"="false"}, methods={"POST"}, defaults={"XmlHttpRequest"=true}))
     */
    public function save(Request $request, SalesChannelContext $context): Response
    {
        if ($froshShareBasketUrl = $this->shareBasketService->saveCart($context)) {
            $froshShareBasketState = 'cartSaved';
        } else {
            $froshShareBasketState = 'cartError';
        }

        return $this->renderStorefront(
            '@Storefront/storefront/utilities/frosh-share-basket.html.twig',
            [
                'froshShareBasketState' => $froshShareBasketState,
                'froshShareBasketUrl' => $froshShareBasketUrl,
            ]
        );
    }

    /**
     * @Route("/loadBasket/{basketId}", name="frontend.frosh.share-basket.load", methods={"GET"})
     */
    public function load(Request $request, SalesChannelContext $context): Response
    {
        if ($this->shareBasketService->loadCart($context) instanceof Cart) {
            $froshShareBasketState = 'cartLoaded';
            $this->addFlash('success', $this->trans('frosh-share-basket.cartLoaded'));
        } else {
            $froshShareBasketState = 'cartNotFound';
            $this->addFlash('info', $this->trans('frosh-share-basket.cartNotFound'));
        }

        return $this->forwardToRoute(
            'frontend.checkout.cart.page',
            ['froshShareBasketState' => $froshShareBasketState]
        );
    }
}
