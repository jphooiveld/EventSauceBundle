<?php

declare(strict_types=1);

namespace Jphooiveld\Bundle\EventSauceBundle\Command;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception as DBALException;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Schema\Schema;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Attribute\Option;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand('eventsauce:create-schema')]
final readonly class CreateSchemaCommand
{
    public function __construct(
        private Connection $connection,
        private string $table,
    ) {
    }

    /**
     * @throws DBALException
     */
    public function __invoke(
        SymfonyStyle $io,
        #[Option]
        bool $legacy = false,
        #[Option]
        bool $force = false,
    ): int
    {
        if (!$force) {
            $io->writeln('You must use the --force option to execute this command.');
            return 1;
        }

        $schema = new Schema();
        $table  = $schema->createTable($this->table);

        if ($legacy) {
            $table->addColumn('event_id', 'guid');
            $table->addColumn('event_type', 'string', ['length' => 255]);
            $table->addColumn('aggregate_root_id', 'guid');
            $table->addColumn('aggregate_root_version', 'integer');
            $table->addColumn('payload', 'json', ['PlatformOptions' => ['jsonb' => true]]);
            $table->addColumn('time_of_recording', 'datetimetz_immutable');
            $table->setPrimaryKey(['event_id']);
            $table->addIndex(['time_of_recording']);
            $table->addIndex(['aggregate_root_id']);
            $table->addIndex(['aggregate_root_id', 'version']);
            $table->addUniqueIndex(['aggregate_root_id', 'version']);
        } else {
            $table->addColumn('event_id', 'guid');
            $table->addColumn('aggregate_root_id', 'guid');
            $table->addColumn('version', 'integer');
            $table->addColumn('payload', 'json', ['PlatformOptions' => ['jsonb' => true]]);
            $table->setPrimaryKey(['event_id']);
            $table->addIndex(['aggregate_root_id']);
            $table->addIndex(['aggregate_root_id', 'version']);
            $table->addUniqueIndex(['aggregate_root_id', 'version']);
        }

        /** @var AbstractPlatform $platform */
        $platform = $this->connection->getDatabasePlatform();
        $queries  = $schema->toSql($platform);

        foreach ($queries as $query) {
            $this->connection->executeStatement($query);
        }

        $io->writeln(sprintf('Table %s created', $this->table));

        return Command::SUCCESS;
    }
}
