<?php

declare(strict_types=1);

namespace PimcoreDatabaseExplorer\Bundle\DatabaseExplorerBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * Validates and merges configuration for the Database Explorer bundle.
 */
class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('database_explorer');

        return $treeBuilder;
    }
}
