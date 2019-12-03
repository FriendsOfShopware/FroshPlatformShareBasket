<?php declare(strict_types=1);

namespace Frosh\ShareBasket\Controller;

use Shopware\Core\Framework\Api\Response\ResponseFactoryInterface;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Aggregation\Metric\SumAggregation;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Grouping\FieldGrouping;
use Shopware\Core\Framework\Routing\Annotation\RouteScope;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @RouteScope(scopes={"api"})
 */
class ShareBasketController extends AbstractController
{
    /**
     * @Route("/api/v{version}/frosh/sharebasket/statistics", name="api.action.frosh.share-basket.statistics", methods={"POST"})
     */
    public function statistics(Request $request, Context $context, ResponseFactoryInterface $responseFactory): Response
    {
        $repository = $this->container->get('frosh_share_basket_line_item.repository');

        $criteria = new Criteria();

        $criteria->setTotalCountMode(Criteria::TOTAL_COUNT_MODE_EXACT);

        $criteria->addAggregation(
            new SumAggregation('sum-quantity', 'cart.saveCount')
        );

        $criteria->addGroupField(new FieldGrouping('identifier'));

        $result = $repository->search($criteria, $context);

        return $responseFactory->createListingResponse($result, $repository->getDefinition(), $request, $context);
    }
}
