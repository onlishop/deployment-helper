<?php

declare(strict_types=1);

namespace Onlishop\Deployment\Event;

use Onlishop\Deployment\Struct\RunConfiguration;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\Attribute\Exclude;

#[Exclude]
readonly class PostDeploy
{
    public function __construct(public readonly RunConfiguration $configuration, public readonly OutputInterface $output)
    {
    }
}
