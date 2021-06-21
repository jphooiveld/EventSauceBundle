<?php
declare(strict_types = 1);

namespace Jphooiveld\Bundle\EventSauceBundle\Tests\DependencyInjection;

use Jphooiveld\Bundle\EventSauceBundle\DependencyInjection\Configuration;
use Jphooiveld\Bundle\EventSauceBundle\DependencyInjection\JphooiveldEventSauceExtension;
use PHPUnit\Framework\TestCase;

final class ConfigurationTest extends TestCase
{

    public function testTreeRootNameMatchesExtensionAlias()
    {
        $configuration = new Configuration();
        $extension     = new JphooiveldEventSauceExtension();

        self::assertSame(
            $extension->getAlias(),
            $configuration->getConfigTreeBuilder()->buildTree()->getName()
        );

    }

}
