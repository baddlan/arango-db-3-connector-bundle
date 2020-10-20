<?php
declare(strict_types=1);

namespace Eos\Bundle\ArangoDBConnector\Command;

use Eos\ArangoDBConnector\ArangoDBInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * @author Philipp Marien <marien@eosnewmedia.de>
 */
class CollectionCreateCommand extends AbstractDatabaseCommand
{
    /**
     * @var array
     */
    private $collections;

    /**
     * @param array $collections
     * @param ArangoDBInterface $databaseClient
     */
    public function __construct(array $collections, ArangoDBInterface $databaseClient)
    {
        parent::__construct('collections:create', $databaseClient);
        $this->collections = $collections;

        $this->addArgument(
            'collections',
            InputArgument::OPTIONAL | InputArgument::IS_ARRAY,
            'Which collections should be created?',
            array_keys($collections)
        );

        $this->addOption('force', 'f', InputOption::VALUE_NONE, 'Overwrite existing collection?');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     */
    protected function execute(InputInterface $input, OutputInterface $output): void
    {
        $io = new SymfonyStyle($input, $output);
        foreach ($input->getArgument('collections') as $collectionName) {
            if (!array_key_exists($collectionName, $this->collections)) {
                $io->warning('Collection ' . $collectionName . ' is not configured.');
                continue;
            }

            try {
                $collection = $this->collections[$collectionName];

                if ($collection['type'] === 'document') {
                    $this->getDatabaseClient()->collections()->createDocumentCollection(
                        $collectionName,
                        $input->getOption('force'),
                        (bool)$collection['wait_for_sync']
                    );
                } else {
                    $this->getDatabaseClient()->collections()->createEdgeCollection(
                        $collectionName,
                        $input->getOption('force'),
                        (bool)$collection['wait_for_sync']
                    );
                }

                foreach ($collection['indices'] as $index) {
                    $this->getDatabaseClient()->collections()->createIndex(
                        $collectionName,
                        $index['options']
                    );
                }

                $io->success('Created collection ' . $collectionName . '.');
            } catch (\Throwable $e) {
                $io->error($e->getMessage());
            }
        }

        $io->success('Created all collections.');
    }
}
