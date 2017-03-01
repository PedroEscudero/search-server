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

namespace Mmoreram\SearchBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

use Mmoreram\SearchBundle\Model\Category;
use Mmoreram\SearchBundle\Model\Product;

/**
 * Class GenerateProductsCommand.
 */
class GenerateProductsCommand extends ContainerAwareCommand
{
    /**
     * Configures the current command.
     */
    protected function configure()
    {
        $this->setName('load-catalog');
    }

    /**
     * Executes the current command.
     *
     * This method is not abstract because you can use this class
     * as a concrete class. In this case, instead of defining the
     * execute() method, you set the code to execute by passing
     * a Closure to the setCode() method.
     *
     * @param InputInterface  $input  An InputInterface instance
     * @param OutputInterface $output An OutputInterface instance
     *
     * @return null|int null or 0 if everything went fine, or an error code
     *
     * @see setCode()
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this
            ->getContainer()
            ->get('search_bundle.elastica_wrapper')
            ->createIndexMapping();

        for ($id = 1; $id < 1000; ++$id) {
            $family = array_keys($this->categories())[rand(0, 1)];
            $mainCategory = $this->categories()[$family][rand(0, 2)];
            $lastCategory = $mainCategory['categories'][rand(0, 2)];
            $price = rand(100, 20000);
            $manufacturerId = rand(0, 3);
            $brandId = rand(0, 3);
            $product = Product::createFromArray([
                'id' => $id,
                'family' => $family,
                'ean' => rand(1, 9999999999),
                'name' => "$family #$id",
                'description' => "This is the $family number #$id, with categories {$mainCategory['name']} and {$lastCategory['name']}",
                'price' => $price,
                'reduced_price' => max(min($price, rand($price - 5000, $price + 500)), 0),
                'manufacturer' => [
                    'id' => $manufacturerId,
                    'name' => $this->manufacturers()[$manufacturerId],
                ],
                'brand' => [
                    'id' => $brandId,
                    'name' => $this->brands()[$brandId],
                ],
            ]);

            $product->addCategory(
                Category::createFromArray([
                    'id' => $mainCategory['id'],
                    'name' => $mainCategory['name'],
                    'level' => 1,
                ])
            );
            $product->addCategory(
                Category::createFromArray([
                    'id' => $lastCategory['id'],
                    'name' => $lastCategory['name'],
                    'level' => 2,
                ])
            );

            $tags = array_rand(
                $this->tags(),
                rand(2, 4)
            );
            foreach ($tags as $tagIndex) {
                $product->addTag(
                    $this->tags()[$tagIndex]
                );
            }

            $this
                ->getContainer()
                ->get('search_bundle.repository')
                ->index('000', $product);
        }
    }

    /**
     * Categories.
     */
    private function categories()
    {
        return [
            'products' => [
                [
                    'id' => '1',
                    'name' => 'Wear',
                    'categories' => [
                        [
                            'id' => '11',
                            'name' => 'Man\'s Wear',
                        ],
                        [
                            'id' => '12',
                            'name' => 'Woman\'s Wear',
                        ],
                        [
                            'id' => '13',
                            'name' => 'Kid\'s Wear',
                        ],
                    ],
                ],
                [
                    'id' => '2',
                    'name' => 'Shoes',
                    'categories' => [
                        [
                            'id' => '21',
                            'name' => 'Man\'s Shoes',
                        ],
                        [
                            'id' => '22',
                            'name' => 'Woman\'s Shoes',
                        ],
                        [
                            'id' => '23',
                            'name' => 'Kid\'s Shoes',
                        ],
                    ],
                ],
                [
                    'id' => '3',
                    'name' => 'Hats',
                    'categories' => [
                        [
                            'id' => '31',
                            'name' => 'Man\'s Hats',
                        ],
                        [
                            'id' => '32',
                            'name' => 'Woman\'s Hats',
                        ],
                        [
                            'id' => '33',
                            'name' => 'Kid\'s Hats',
                        ],
                    ],
                ],
            ],
            'food' => [
                [
                    'id' => '4',
                    'name' => 'Meat',
                    'categories' => [
                        [
                            'id' => '41',
                            'name' => 'Chicken',
                        ],
                        [
                            'id' => '42',
                            'name' => 'Lamb',
                        ],
                        [
                            'id' => '13',
                            'name' => 'Cow',
                        ],
                    ],
                ],
                [
                    'id' => '5',
                    'name' => 'Drinks',
                    'categories' => [
                        [
                            'id' => '51',
                            'name' => 'Milk',
                        ],
                        [
                            'id' => '52',
                            'name' => 'Water',
                        ],
                        [
                            'id' => '53',
                            'name' => 'Juice',
                        ],
                    ],
                ],
                [
                    'id' => '6',
                    'name' => 'Fruits',
                    'categories' => [
                        [
                            'id' => '61',
                            'name' => 'Apples',
                        ],
                        [
                            'id' => '62',
                            'name' => 'Bananas',
                        ],
                        [
                            'id' => '63',
                            'name' => 'Oranges',
                        ],
                    ],
                ],
            ],
        ];
    }

    /**
     * Manufacturers.
     */
    private function manufacturers()
    {
        return  [
            'Adidas',
            'Nike',
            'Rebook',
            'Quetchua',
        ];
    }

    /**
     * Brands.
     */
    private function brands()
    {
        return [
            'Decathlon',
            'Amazon',
            'Nike',
            'Nestle',
        ];
    }

    /**
     * Tags.
     */
    private function tags()
    {
        return [
            // quality
            'amazing',
            'new',
            'next generation',
            'healthy',

            // stock
            'last units',
            'infinite stock',

            // shipping
            'express',
            'two-day delivery',
            'one-week delivery',
        ];
    }
}
