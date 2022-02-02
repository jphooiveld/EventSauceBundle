<?php

declare(strict_types=1);

namespace Jphooiveld\Bundle\EventSauceBundle\Command;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception as DBALException;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Schema\SchemaException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

final class CreateSchemaCommand extends Command
{
    /**
     * @var string
     */
    protected static $defaultName = 'eventsauce:create-schema';

    public function __construct(
        private Connection $connection,
        private string $table,
    ) {
        parent::__construct();
    }

    /**
     * @inheritDoc
     */
    protected function configure(): void
    {
        $this->setDefinition([
            new InputOption('force', null, InputOption::VALUE_NONE),
        ]);
    }

    /**
     * @inheritDoc
     * @throws DBALException
     * @throws SchemaException
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        if (!$input->getOption('force')) {
            $output->writeln('You must use the --force option to execute this command.');
            return 1;
        }

        $schema = new Schema();
        $table  = $schema->createTable($this->table);
        $table->addColumn('event_id', 'guid');
        $table->addColumn('event_type', 'string', ['length' => 255]);
        $table->addColumn('aggregate_root_id', 'guid');
        $table->addColumn('aggregate_root_version', 'integer');
        $table->addColumn('time_of_recording', 'datetime_immutable');
        $table->addColumn('payload', 'json', ['PlatformOptions' => ['jsonb' => true]]);
        $table->setPrimaryKey(['event_id']);
        $table->addIndex(['aggregate_root_id']);
        $table->addIndex(['time_of_recording']);
        $table->addIndex(['aggregate_root_id', 'aggregate_root_version']);
        $table->addUniqueIndex(['aggregate_root_id', 'aggregate_root_version']);

        $platform = $this->connection->getDatabasePlatform();

        if (!($platform instanceof AbstractPlatform)) {
            $output->writeln('<error>No database platform was found</error>');
            return 1;
        }

        $queries  = $schema->toSql($platform);

        foreach ($queries as $query) {
            $this->connection->executeStatement($query);
        }

        $output->writeln(sprintf('Table %s created', $this->table));

        return 0;
    }
}
