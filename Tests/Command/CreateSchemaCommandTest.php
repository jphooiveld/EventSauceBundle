<?php
declare(strict_types=1);

namespace Jphooiveld\Bundle\EventSauceBundle\Tests\Command;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Configuration;
use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\Exception as DBALException;
use EventSauce\MessageRepository\DoctrineMessageRepository\DoctrineUuidV4MessageRepository as DoctrineUuidV4MessageRepositoryV3;
use EventSauce\MessageRepository\DoctrineV2MessageRepository\DoctrineUuidV4MessageRepository as DoctrineUuidV4MessageRepositoryV2;
use Jphooiveld\Bundle\EventSauceBundle\Command\CreateSchemaCommand;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * @covers \Jphooiveld\Bundle\EventSauceBundle\Command\CreateSchemaCommand
 */
final class CreateSchemaCommandTest extends KernelTestCase
{
    /**
     * @return void
     * @throws DBALException
     */
    public function test_execute(): void
    {
        if (!extension_loaded('pdo_sqlite')) {
            $this->markTestSkipped('Can only test under pdo_sqlite driver');
        }

        if (!class_exists(DoctrineUuidV4MessageRepositoryV3::class) && !class_exists(DoctrineUuidV4MessageRepositoryV2::class)) {
            $this->markTestSkipped('Can only test with Doctrine Message Repository enabled');
        }

        $con = DriverManager::getConnection(['driver' => 'pdo_sqlite', 'memory' => true], new Configuration());

        $command = new CreateSchemaCommand($con, 'event');
        $tester  = new CommandTester($command);

        $tester->execute(['--force' => true]);

        self::assertSame('Table event created' . PHP_EOL, $tester->getDisplay());

        $tester->execute([]);
        self::assertSame('You must use the --force option to execute this command.' . PHP_EOL, $tester->getDisplay());
    }
}
