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
     * @param null|string $language
     */
    public function __construct(? string $language)
    {
        $this->language = $language;
        $this->setNow();
    }

    /**
     * Payload to array.
     *
     * @return array
     */
    public function payloadToArray(): array
    {
        return [
            'language' => $this->language,
        ];
    }

    /**
     * To payload.
     *
     * @param string $data
     *
     * @return array
     */
    public static function stringToPayload(string $data): array
    {
        return [
            json_decode($data, true)['language'],
        ];
    }
}
