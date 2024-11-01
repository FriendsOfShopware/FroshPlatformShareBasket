<?php
declare(strict_types=1);

namespace Frosh\ShareBasket\Storefront\Page\Checkout\Cart\Subscriber;

use Frosh\ShareBasket\Services\ShareBasketServiceInterface;
use Shopware\Storefront\Page\Checkout\Cart\CheckoutCartPageLoadedEvent;
use Shopware\Storefront\Page\Checkout\Offcanvas\OffcanvasCartPageLoadedEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class CheckoutPageSubscriber implements EventSubscriberInterface
{
    public function __construct(private readonly ShareBasketServiceInterface $shareBasketService)
    {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            CheckoutCartPageLoadedEvent::class => 'onCartLoaded',
            OffcanvasCartPageLoadedEvent::class => 'onCartLoaded',
        ];
    }

    public function onCartLoaded(CheckoutCartPageLoadedEvent|OffcanvasCartPageLoadedEvent $event): void
    {
        if (!$event->getRequest()->hasSession()) {
            return;
        }

        $page = $event->getPage();

        if ($event->getRequest()->get('froshShareBasketState')) {
            $page->assign(['froshShareBasketState' => $event->getRequest()->get('froshShareBasketState')]);

            return;
        }

        $session = $event->getRequest()->getSession();
        if ($hash = $session->get('froshShareBasketHash')) {
            $shareBasketData = $this->shareBasketService->prepareLineItems($event->getSalesChannelContext());
            if ($hash === $shareBasketData['hash']) {
                $page->assign([
                    'froshShareBasketState' => 'cartExists',
                    'froshShareBasketUrl' => $this->shareBasketService->saveCart(
                        $event->getRequest(),
                        $shareBasketData,
                        $event->getSalesChannelContext()
                    ),
                ]);
            }
        }
    }
}
