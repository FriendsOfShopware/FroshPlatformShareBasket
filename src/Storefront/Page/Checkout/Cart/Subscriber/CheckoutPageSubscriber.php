<?php declare(strict_types=1);

namespace Frosh\ShareBasket\Storefront\Page\Checkout\Cart\Subscriber;

use Frosh\ShareBasket\Services\ShareBasketServiceInterface;
use Shopware\Core\Checkout\Cart\Exception\PayloadKeyNotFoundException;
use Shopware\Storefront\Page\Checkout\Cart\CheckoutCartPageLoadedEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class CheckoutPageSubscriber implements EventSubscriberInterface
{
    /**
     * @var ShareBasketServiceInterface
     */
    private $shareBasketService;

    public function __construct(ShareBasketServiceInterface $shareBasketService)
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
            try {
                $shareBasketData = $this->shareBasketService->prepareLineItems($event->getSalesChannelContext());
                if ($hash === $shareBasketData['hash']) {
                    $page->assign([
                        'froshShareBasketState' => 'cartExists',
                        'froshShareBasketUrl' => $this->shareBasketService->saveCart($shareBasketData, $event->getSalesChannelContext()),
                    ]);
                }
            } catch (PayloadKeyNotFoundException $e) {
            }
        }
    }
}
