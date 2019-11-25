<?php
declare(strict_types=1);

namespace Jphooiveld\Bundle\EventSauceBundle\Tests\Aggregate;

use EventSauce\EventSourcing\AggregateRoot;
use EventSauce\EventSourcing\AggregateRootBehaviour;

final class Order implements AggregateRoot
{
    use AggregateRootBehaviour;
}