<?php

/*
 * This file is part of the Search Server Bundle.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * Feel free to edit as you please, and have fun.
 *
 * @author Marc Morera <yuhu@mmoreram.com>
 * @author PuntMig Technologies
 */

declare(strict_types=1);

namespace Puntmig\Search\Server\Domain\Event;

/**
 * Class IndexWasReset.
 */
class IndexWasReset extends DomainEvent
{
    /**
     * @var null|string
     *
     * Language
     */
    private $language;

    /**
     * ItemsWasIndexed constructor.
     *
     * @param string      $key
     * @param null|string $language
     */
    public function __construct(
        string $key,
        ? string $language
    ) {
        $this->key = $key;
        $this->language = $language;
        $this->setNow();
    }

    /**
     * To payload.
     *
     * @return string
     */
    public function toPayload() : string
    {
        return json_encode([
            'language' => $this->language,
        ]);
    }

    /**
     * To payload.
     *
     * @param string $payload
     *
     * @return array
     */
    public static function fromPayload(string $payload) : array
    {
        return [json_decode($payload, true)['language']];
    }
}
