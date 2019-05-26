<?php

namespace Jphooiveld\Bundle\EventSauceBundle\Tests\Aggregate;

use EventSauce\EventSourcing\AggregateRoot;
use EventSauce\EventSourcing\AggregateRootBehaviour;

class Order implements AggregateRoot
{
    use AggregateRootBehaviour;
}