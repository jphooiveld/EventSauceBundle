<?php

declare(strict_types=1);

namespace Jphooiveld\Bundle\EventSauceBundle\Command;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DBALException;
use Doctrine\DBAL\Schema\Schema;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBag;

final class CreateSchemaCommand extends Command
{
    /**
     * @var string
     */
    protected static $defaultName = 'eventsauce:create-schema';

    /**
     * @var ParameterBag
     */
    private $bag;

    /**
     * @var Connection
     */
    private $connection;

    /**
     * Constructor
     *
     * @param ParameterBag $bag
     * @param Connection $connection
     */
    public function __construct(ParameterBag $bag, Connection $connection)
    {
        parent::__construct();

        $this->bag        = $bag;
        $this->connection = $connection;
    }

    /**
     * {@inheritDoc}
     */
    protected function configure(): void
    {
        $this->setDefinition([
            new InputOption('force', null, InputOption::VALUE_NONE),
        ]);
    }

    /**
     * {@inheritDoc}
     * @throws DBALException
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        if (!$input->getOption('force')) {
            $output->writeln('You must use the --force option to execute this command.');
            return 1;
        }

        $schema = new Schema();
        $table  = $schema->createTable($this->bag->get('jphooiveld_eventsauce.repository.doctrine.table'));
        $table->addColumn('event_id', 'guid');
        $table->addColumn('event_type', 'string', ['length' => 255]);
        $table->addColumn('aggregate_root_id', 'guid');
        $table->addColumn('aggregate_root_version', 'integer');
        $table->addColumn('time_of_recording', 'datetime_immutable');
        $table->addColumn('payload', 'json_array', ['PlatformOptions' => ['jsonb' => true]]);
        $table->setPrimaryKey(['event_id']);
        $table->addIndex(['aggregate_root_id']);
        $table->addIndex(['time_of_recording']);
        $table->addIndex(['aggregate_root_id', 'aggregate_root_version']);
        $table->addUniqueIndex(['aggregate_root_id', 'aggregate_root_version']);

        $platform = $this->connection->getDatabasePlatform();

        $queries  = $schema->toSql($platform);

        foreach ($queries as $query) {
            $this->connection->exec($query);
        }

        $output->writeln(sprintf('Table %s created', $this->bag->get('jphooiveld_eventsauce.repository.doctrine.table')));

        return 0;
    }
}
