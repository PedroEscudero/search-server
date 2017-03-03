<?php

/*
 * This file is part of the SearchBundle for Symfony2.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * Feel free to edit as you please, and have fun.
 *
 * @author Marc Morera <yuhu@mmoreram.com>
 */

declare(strict_types=1);

namespace Mmoreram\SearchBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use Mmoreram\SearchBundle\Query\Filter;
use Mmoreram\SearchBundle\Query\PriceRange;
use Mmoreram\SearchBundle\Query\Query;
use Mmoreram\SearchBundle\Query\SortBy;
use Mmoreram\SearchBundle\Result\Result;

/**
 * Class ShopController.
 */
class ShopController extends Controller
{
    /**
     * Index action.
     *
     * @param Request $request
     *
     * @return Response
     */
    public function indexAction(Request $request)
    {
        return $this->render('SearchBundle:Shop:index.html.twig', [
            'query' => $request->query->get('q', ''),
        ]);
    }

    /**
     * Load products and facets page.
     *
     * @param Request $request
     *
     * @return Response
     */
    public function contentAction(Request $request)
    {
        $requestQuery = $request->query;
        $manufacturers = $requestQuery->get('manufacturer', []);
        $brands = $requestQuery->get('brand', []);
        $categories = $requestQuery->get('categories', []);
        $qualityTags = $requestQuery->get('quality', []);
        $stockTags = $requestQuery->get('stock', []);
        $shippingTags = $requestQuery->get('shipping', []);
        $from = (int) $requestQuery->get('from', PriceRange::FREE);
        $to = (int) $requestQuery->get('to', PriceRange::INFINITE);
        $q = $requestQuery->get('q', '');
        $page = (int) $requestQuery->get('page', 1);
        $sortBy = $requestQuery->get('sort_by', SortBy::SCORE);

        $searchQuery = Query::create($q, $page, 100)
            ->filterByPriceRange($from * 100, $to === PriceRange::INFINITE ? $to : ($to * 100))
            ->filterByCategories($categories, Filter::MUST_ALL_WITH_LEVELS)
            ->filterByBrands($brands, Filter::AT_LEAST_ONE)
            ->filterByManufacturers($manufacturers, Filter::AT_LEAST_ONE)
            ->sortBy($sortBy)
            ->filterByTags(
                'quality',
                [
                    'amazing',
                    'new',
                    'next generation',
                    'healthy',
                ],
                $qualityTags,
                Filter::AT_LEAST_ONE
            )
            ->filterByTags(
                'stock',
                [
                    'last units',
                    'infinite stock',
                ],
                $stockTags,
                Filter::AT_LEAST_ONE
            )
            ->filterByTags(
                'shipping',
                [
                    'express',
                    'two-day delivery',
                    'one-week delivery',
                ],
                $shippingTags,
                Filter::AT_LEAST_ONE
            );

        /**
         * @var Result $result
         */
        $result = $this
            ->get('search_bundle.repository')
            ->search('000', $searchQuery);

        return $this->render('SearchBundle:Shop:content.html.twig', [
            'search_query' => $searchQuery,
            'result' => $result,
            'from' => $from,
            'to' => $to === PriceRange::INFINITE
                ? ceil($result->getMaxPrice() / 100)
                : $to,
        ]);
    }
}
