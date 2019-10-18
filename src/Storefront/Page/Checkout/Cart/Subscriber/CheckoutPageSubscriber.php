<?php declare(strict_types=1);

namespace Frosh\ShareBasket\Storefront\Page\Checkout\Cart\Subscriber;

use Frosh\ShareBasket\Services\ShareBasketService;
use Shopware\Storefront\Page\Checkout\Cart\CheckoutCartPageLoadedEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class CheckoutPageSubscriber implements EventSubscriberInterface
{
    /**
     * @var ShareBasketService
     */
    private $shareBasketService;

    public function __construct(ShareBasketService $shareBasketService)
    {
        $this->shareBasketService = $shareBasketService;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            CheckoutCartPageLoadedEvent::class => 'onCartLoaded',
        ];
    }

    public function onCartLoaded(CheckoutCartPageLoadedEvent $event): void
    {
        $session = $event->getRequest()->getSession();
        if (!$session) {
            return;
        }

        $page = $event->getPage();

        if ($event->getRequest()->get('froshShareBasketState')) {
            $page->assign(['froshShareBasketState' => $event->getRequest()->get('froshShareBasketState')]);

            return;
        }

        if ($hash = $session->get('froshShareBasketHash')) {
            $shareBasketData = $this->shareBasketService->prepareLineItems($event->getSalesChannelContext());
            if ($hash === $shareBasketData['hash']) {
                $page->assign([
                    'froshShareBasketState' => 'cartExists',
                    'froshShareBasketUrl' => $this->shareBasketService->saveCart($event->getSalesChannelContext()),
                ]);
            }
        }
    }
}
