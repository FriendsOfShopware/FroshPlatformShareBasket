<?php declare(strict_types=1);

namespace Frosh\ShareBasket\Storefront\Controller;

use Frosh\ShareBasket\Services\ShareBasketServiceInterface;
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
     * @var ShareBasketServiceInterface
     */
    private $shareBasketService;

    public function __construct(ShareBasketServiceInterface $shareBasketService)
    {
        $this->shareBasketService = $shareBasketService;
    }

    /**
     * @Route("/sharebasket/save", name="frontend.frosh.share-basket.save", options={"seo"="false"}, methods={"POST"}, defaults={"XmlHttpRequest"=true}))
     */
    public function save(SalesChannelContext $context): Response
    {
        try {
            $data = $this->shareBasketService->prepareLineItems($context);
            $froshShareBasketUrl = $this->shareBasketService->saveCart($data, $context);
            $froshShareBasketState = 'cartSaved';
        } catch (\Exception $exception) {
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

    /**
     * @Route("/loadBasket/{basketId}", name="frontend.frosh.share-basket.load", methods={"GET"})
     */
    public function load(Request $request, SalesChannelContext $context): Response
    {
        try {
            $this->shareBasketService->loadCart($request, $context);
            $froshShareBasketState = 'cartLoaded';
            $this->addFlash('success', $this->trans('frosh-share-basket.cartLoaded'));
        } catch (\Exception $exception) {
            $froshShareBasketState = 'cartNotFound';
            $this->addFlash('danger', $this->trans('frosh-share-basket.cartNotFound'));
        }

        return $this->forwardToRoute(
            'frontend.checkout.cart.page',
            ['froshShareBasketState' => $froshShareBasketState]
        );
    }
}
