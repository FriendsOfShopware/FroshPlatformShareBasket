<?php declare(strict_types=1);

namespace Frosh\ShareBasket\Controller;

use Doctrine\DBAL\Connection;
use Shopware\Core\Checkout\Cart\LineItem\LineItem;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\Routing\Annotation\RouteScope;
use Shopware\Core\Framework\Uuid\Uuid;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @RouteScope(scopes={"api"})
 */
class ShareBasketController extends AbstractController
{
    /**
     * @var Connection
     */
    private $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    /**
     * @Route("/api/v{version}/frosh/sharebasket/statistics", name="api.action.frosh.share-basket.statistics", methods={"POST"})
     */
    public function statistics(Request $request, Context $context): Response
    {
        $languageId = $request->get('languageId', $context->getLanguageId());
        $page = $request->get('page');
        $limit = $request->get('limit');
        $offset = ($page - 1) * $limit;

        $query = $this->connection->createQueryBuilder();
        $query->select(
            [
                'SQL_CALC_FOUND_ROWS product.product_number as productNumber',
                'SUM(1 * save_count) as saveCount',
                'SUM(froshShareBasketLineItem.quantity * save_count) as totalQuantity',
                'IFNULL(translation.name, translationDefault.name) as productName',
            ]
        )
            ->from('frosh_share_basket', 'shareBasket')
            ->innerJoin(
                'shareBasket',
                'frosh_share_basket_line_item',
                'froshShareBasketLineItem',
                'shareBasket.id = froshShareBasketLineItem.cart_id'
            )
            ->leftJoin(
                'froshShareBasketLineItem',
                'product',
                'product',
                'froshShareBasketLineItem.identifier = product.id'
            )
            ->leftJoin(
                'product',
                'product_translation',
                'translation',
                'product.id = translation.product_id AND translation.language_id = :language'
            )
            ->leftJoin(
                'product',
                'product_translation',
                'translationDefault',
                'product.id = translationDefault.product_id AND translationDefault.language_id = :defaultLanguage'
            )
            ->where('froshShareBasketLineItem.type = :type')
            ->groupBy('froshShareBasketLineItem.identifier')
            ->setFirstResult($offset)
            ->setMaxResults($limit)
            ->setParameter('type', LineItem::PRODUCT_LINE_ITEM_TYPE)
            ->setParameter('language', Uuid::fromHexToBytes($languageId))
            ->setParameter('defaultLanguage', Uuid::fromHexToBytes($context->getLanguageId()));

        foreach ($request->get('sortings') as $condition) {
            $query->addOrderBy(
                $condition['field'],
                $condition['order']
            );
        }

        /*
         * TODO: fix statement
         */
        foreach ($request->get('filters') as $condition) {
            $query->andWhere($query->expr()->eq($condition['field'], ':' . $condition['value']));
            $query->setParameter(':' . $condition['value'], Uuid::fromHexToBytes($condition['value']));
        }

        $data = $query->execute()->fetchAll();
        $totalCount = (int) $this->connection->fetchColumn('SELECT FOUND_ROWS()');

        return new JsonResponse([
            'total' => $totalCount,
            'data' => $data,
        ]);
    }
}
