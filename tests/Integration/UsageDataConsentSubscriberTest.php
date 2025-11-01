<?php

declare(strict_types=1);

namespace Onlishop\Deployment\Tests\Integration;

use Onlishop\Deployment\Event\PostDeploy;
use Onlishop\Deployment\Integration\UsageDataConsentSubscriber;
use Onlishop\Deployment\Services\SystemConfigHelper;
use Onlishop\Deployment\Struct\RunConfiguration;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Output\NullOutput;
use Zalas\PHPUnit\Globals\Attribute\Env;

#[CoversClass(UsageDataConsentSubscriber::class)]
class UsageDataConsentSubscriberTest extends TestCase
{
    private SystemConfigHelper&MockObject $systemConfigHelper;
    private UsageDataConsentSubscriber $subscriber;

    protected function setUp(): void
    {
        $this->systemConfigHelper = $this->createMock(SystemConfigHelper::class);
        $this->subscriber = new UsageDataConsentSubscriber($this->systemConfigHelper);
    }

    #[DataProvider('provideConsent')]
    #[Env('ONLISHOP_USAGE_DATA_CONSENT', value: '')]
    public function testInvoke(string $consent, bool $shouldBeCalled): void
    {
        $_SERVER['ONLISHOP_USAGE_DATA_CONSENT'] = $consent;

        $this->systemConfigHelper
            ->expects($shouldBeCalled ? $this->once() : $this->never())
            ->method('set')
            ->with('core.usageData.consentState', $consent);

        $event = new PostDeploy(new RunConfiguration(), new NullOutput());
        $this->subscriber->__invoke($event);
    }

    public static function provideConsent(): \Generator
    {
        yield 'requested' => ['requested', true];
        yield 'accepted' => ['accepted', true];
        yield 'revoked' => ['revoked', true];
    }

    #[Env('ONLISHOP_USAGE_DATA_CONSENT', 'invalid')]
    public function testInvokeWithInvalidConsent(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid value for ONLISHOP_USAGE_DATA_CONSENT. Must be one of: requested, accepted, revoked');

        $this->systemConfigHelper
            ->expects($this->never())
            ->method('set');

        $event = new PostDeploy(new RunConfiguration(), new NullOutput());
        $this->subscriber->__invoke($event);
    }

    public function testInvokeWithNoConsent(): void
    {
        $this->systemConfigHelper
            ->expects($this->never())
            ->method('set');

        $event = new PostDeploy(new RunConfiguration(), new NullOutput());
        $this->subscriber->__invoke($event);
    }
}
