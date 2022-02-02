<?php
declare(strict_types = 1);

namespace Jphooiveld\Bundle\EventSauceBundle\Tests\DependencyInjection;

use Jphooiveld\Bundle\EventSauceBundle\DependencyInjection\Configuration;
use Jphooiveld\Bundle\EventSauceBundle\DependencyInjection\JphooiveldEventSauceExtension;
use PHPUnit\Framework\TestCase;

final class ConfigurationTest extends TestCase
{
    /**
     * @covers \Jphooiveld\Bundle\EventSauceBundle\DependencyInjection\Configuration
     */
    public function test_tree_root_name_matches_extension_alias(): void
    {
        $configuration = new Configuration();
        $extension     = new JphooiveldEventSauceExtension();

        self::assertSame(
            $extension->getAlias(),
            $configuration->getConfigTreeBuilder()->buildTree()->getName()
        );

    }

}
