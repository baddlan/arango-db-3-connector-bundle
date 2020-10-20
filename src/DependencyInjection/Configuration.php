<?php
declare(strict_types=1);

namespace Eos\Bundle\ArangoDBConnector\DependencyInjection;

use Eos\ArangoDBConnector\Collection\CollectionHandlerInterface;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * @author Philipp Marien <marien@eosnewmedia.de>
 */
class Configuration implements ConfigurationInterface
{
    /**
     * @return TreeBuilder
     */
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('eos_arango_db_connector');
        $root = $treeBuilder->getRootNode()->children();

        $root->arrayNode('servers')->isRequired()->requiresAtLeastOneElement()->scalarPrototype();
        $root->scalarNode('user')->isRequired()->cannotBeEmpty();
        $root->scalarNode('password')->defaultNull();
        $root->scalarNode('database')->isRequired()->cannotBeEmpty();

        $collection = $root->arrayNode('collections')->useAttributeAsKey('name')->arrayPrototype()->children();
        $collection->enumNode('type')->values(['document', 'edge'])->defaultValue('document');
        $collection->booleanNode('wait_for_sync')->defaultFalse();

        $index = $collection->arrayNode('indices')->arrayPrototype()->children();
        $index->enumNode('type')->values([
            CollectionHandlerInterface::INDEX_FULLTEXT,
            CollectionHandlerInterface::INDEX_GEO,
            CollectionHandlerInterface::INDEX_HASH,
            CollectionHandlerInterface::INDEX_PERSISTENT,
            CollectionHandlerInterface::INDEX_SKIP_LIST,
        ]);
        $index->arrayNode('fields')->scalarPrototype();
        $index->arrayNode('options')->useAttributeAsKey('name')->scalarPrototype();

        return $treeBuilder;
    }
}
