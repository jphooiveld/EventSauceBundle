<?php

declare(strict_types=1);

namespace Jphooiveld\Bundle\EventSauceBundle\Tests;

use EventSauce\MessageRepository\DoctrineMessageRepository\DoctrineUuidV4MessageRepository as DoctrineUuidV4MessageRepositoryV3;
use EventSauce\MessageRepository\DoctrineV2MessageRepository\DoctrineUuidV4MessageRepository as DoctrineUuidV4MessageRepositoryV2;

trait HasDoctrineMessageRepositoryTrait
{
    private static function checkSkipForDoctrineMessageRepository(): void
    {
        if (!class_exists(DoctrineUuidV4MessageRepositoryV3::class) && !class_exists(DoctrineUuidV4MessageRepositoryV2::class)) {
            self::markTestSkipped('Can only test with Doctrine Message Repository enabled');
        }
    }

    abstract public static function markTestSkipped(string $message = ''): void;
}