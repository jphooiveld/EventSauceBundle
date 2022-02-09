<?php
declare(strict_types=1);

namespace Jphooiveld\Bundle\EventSauceBundle\Tests\Command;

use Doctrine\DBAL\Configuration;
use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\Exception as DBALException;
use Jphooiveld\Bundle\EventSauceBundle\Command\CreateSchemaCommand;
use Jphooiveld\Bundle\EventSauceBundle\Tests\HasDoctrineMessageRepositoryTrait;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * @covers \Jphooiveld\Bundle\EventSauceBundle\Command\CreateSchemaCommand
 */
final class CreateSchemaCommandTest extends KernelTestCase
{
    use HasDoctrineMessageRepositoryTrait;

    /**
     * @return void
     * @throws DBALException
     */
    public function test_execute(): void
    {
        if (!extension_loaded('pdo_sqlite')) {
            self::markTestSkipped('Can only test under pdo_sqlite driver');
        }
        
        self::checkSkipForDoctrineMessageRepository();

        $con = DriverManager::getConnection(['driver' => 'pdo_sqlite', 'memory' => true], new Configuration());

        $command = new CreateSchemaCommand($con, 'event');
        $tester  = new CommandTester($command);

        $tester->execute(['--force' => true]);

        self::assertSame('Table event created' . PHP_EOL, $tester->getDisplay());

        $tester->execute([]);
        self::assertSame('You must use the --force option to execute this command.' . PHP_EOL, $tester->getDisplay());
    }
}
