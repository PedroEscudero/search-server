<?php

/*
 * This file is part of the Apisearch Server
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

namespace Apisearch\Server\Elastica\Repository;

use Apisearch\Config\Config;
use Apisearch\Config\Synonym;
use Apisearch\Exception\ResourceNotAvailableException;
use Apisearch\Server\Domain\Repository\Repository\ConfigRepository as ConfigRepositoryInterface;
use Apisearch\Server\Elastica\ElasticaWrapperWithRepositoryReference;

/**
 * Class ConfigRepository.
 */
class ConfigRepository extends ElasticaWrapperWithRepositoryReference implements ConfigRepositoryInterface
{
    /**
     * Config the index.
     *
     * @param Config $config
     *
     * @throws ResourceNotAvailableException
     */
    public function configureIndex(Config $config)
    {
        $this->writeCampaigns($config);
        $this->writeSynonyms($config);

        if ($this->elasticaWrapper instanceof ItemElasticaWrapper) {
            $this
                ->elasticaWrapper
                ->updateIndexSettings(
                    $this->getRepositoryReference(),
                    $this->getConfigPath(),
                    $config->getLanguage()
                );
        }
    }

    /**
     * Write synonyms.
     *
     * @param Config $config
     */
    private function writeSynonyms(Config $config)
    {
        $synonymsAsArray = array_map(function (Synonym $synonym) {
            return implode(', ', $synonym->getWords());
        }, $config->getSynonyms());

        if (empty($synonymsAsArray)) {
            @unlink($this->getConfigPath().'/synonyms.json');

            return;
        }

        $syonymsAsPlainText = implode("\n", $synonymsAsArray)."\n";
        $fileHandle = fopen($this->getConfigPath().'/synonyms.json', 'w');
        fwrite($fileHandle, $syonymsAsPlainText);
        fclose($fileHandle);
    }

    /**
     * Write campaigns.
     *
     * @param Config $config
     */
    private function writeCampaigns(Config $config)
    {
        $campaigns = $config
            ->getCampaigns()
            ->toArray();

        if (empty($campaigns)) {
            @unlink($this->getConfigPath().'/campaigns.json');

            return;
        }

        $fileHandle = fopen($this->getConfigPath().'/campaigns.json', 'w');
        fwrite($fileHandle, json_encode($config->getCampaigns()->toArray()));
        fclose($fileHandle);
    }
}
