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
use Mmoreram\SearchBundle\Query\Query;
use Mmoreram\SearchBundle\Query\Range;
use Mmoreram\SearchBundle\Query\SortBy;
use Mmoreram\SearchBundle\Result\Result;

/**
 * Class ShopController.
 */
class ShopController extends Controller
{
    /**
     * @var string
     *
     * Used api key
     */
    protected static $key = 'test_000';

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
        $priceRanges = $requestQuery->get('price', []);
        $ratingRanges = $requestQuery->get('rating', []);
        $q = $requestQuery->get('q', '');
        $page = (int) $requestQuery->get('page', 1);
        $sortBy = $requestQuery->get('sort_by', SortBy::SCORE);

        $searchQuery = Query::create($q, $page, 100)
            ->filterByRange('price', 'real_price', Range::createRanges(10, 200, 10), $priceRanges)
            ->filterByRange('rating', 'rating', Range::createRanges(0, 10, 1), $ratingRanges)
            ->filterByCategories($categories, Filter::MUST_ALL_WITH_LEVELS)
            ->filterByBrands($brands, Filter::AT_LEAST_ONE)
            ->filterByManufacturers($manufacturers, Filter::AT_LEAST_ONE)
            ->sortBy($sortBy);

        /**
         * @var Result $result
         */
        $result = $this
            ->get('search_bundle.query_repository')
            ->query(self::$key, $searchQuery);

        return $this->render('SearchBundle:Shop:content.html.twig', [
            'search_query' => $searchQuery,
            'result' => $result,
        ]);
    }
}
