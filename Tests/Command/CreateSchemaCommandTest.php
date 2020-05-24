<?php
declare(strict_types=1);

namespace Jphooiveld\Bundle\EventSauceBundle\Tests\Command;

use Doctrine\DBAL\Configuration;
use Doctrine\DBAL\DBALException;
use Doctrine\DBAL\DriverManager;
use Jphooiveld\Bundle\EventSauceBundle\Command\CreateSchemaCommand;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBag;

/**
 * Class CreateSchemaCommandTest
 * @package Jphooiveld\Bundle\EventSauceBundle\Tests\Command
 * @covers \Jphooiveld\Bundle\EventSauceBundle\Command\CreateSchemaCommand
 */
final class CreateSchemaCommandTest extends KernelTestCase
{
    /**
     * @throws DBALException
     */
    public function testExecute(): void
    {
        if (!extension_loaded('pdo_sqlite')) {
            $this->markTestSkipped('Can only test under pdo_sqlite driver');
            return;
        }

        $con = DriverManager::getConnection(['driver' => 'pdo_sqlite', 'memory' => true], new Configuration());
        $bag = new ParameterBag();
        $bag->set('jphooiveld_eventsauce.repository.doctrine.table', 'event');

        $command = new CreateSchemaCommand($bag, $con);
        $tester  = new CommandTester($command);

        $tester->execute(['--force' => true]);

        self::assertSame('Table event created' . PHP_EOL, $tester->getDisplay());

        $tester->execute([]);
        self::assertSame('You must use the --force option to execute this command.' . PHP_EOL, $tester->getDisplay());
    }
}
