<?php

namespace Enqueue\AmqpTools\Tests;

use Enqueue\AmqpTools\SignalSocketHelper;
use PHPUnit\Framework\TestCase;

class SignalSocketHelperTest extends TestCase
{
    /**
     * @var SignalSocketHelper
     */
    private $signalHelper;

    private $backupSigTermHandler;

    private $backupSigIntHandler;

    protected function setUp(): void
    {
        parent::setUp();

        // PHP 7.1 and pcntl ext installed higher
        if (false == function_exists('pcntl_signal_get_handler')) {
            $this->markTestSkipped('PHP 7.1+ needed');
        }

        $this->backupSigTermHandler = pcntl_signal_get_handler(\SIGTERM);
        $this->backupSigIntHandler = pcntl_signal_get_handler(\SIGINT);

        pcntl_signal(\SIGTERM, \SIG_DFL);
        pcntl_signal(\SIGINT, \SIG_DFL);

        $this->signalHelper = new SignalSocketHelper();
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        if ($this->signalHelper) {
            $this->signalHelper->afterSocket();
        }

        if ($this->backupSigTermHandler) {
            pcntl_signal(\SIGTERM, $this->backupSigTermHandler);
        }

        if ($this->backupSigIntHandler) {
            pcntl_signal(\SIGINT, $this->backupSigIntHandler);
        }
    }

    public function testShouldReturnFalseByDefault()
    {
        $this->assertFalse($this->signalHelper->wasThereSignal());
    }

    public function testShouldRegisterHandlerOnBeforeSocket()
    {
        $this->signalHelper->beforeSocket();

        self::assertFalse($this->signalHelper->wasThereSignal());
    }

    public function testShouldRegisterHandlerOnBeforeSocketAndBackupCurrentOne()
    {
        $handler = function () {};

        pcntl_signal(\SIGTERM, $handler);

        $this->signalHelper->beforeSocket();

        self::assertFalse($this->signalHelper->wasThereSignal());
    }

    public function testRestoreDefaultPropertiesOnAfterSocket()
    {
        $this->signalHelper->beforeSocket();
        $this->signalHelper->afterSocket();

        self::assertFalse($this->signalHelper->wasThereSignal());
    }

    public function testRestorePreviousHandlerOnAfterSocket()
    {
        $handler = function () {};

        pcntl_signal(\SIGTERM, $handler);

        $this->signalHelper->beforeSocket();
        $this->signalHelper->afterSocket();

        $this->assertSame($handler, pcntl_signal_get_handler(\SIGTERM));
    }

    public function testThrowsIfBeforeSocketCalledSecondTime()
    {
        $this->signalHelper->beforeSocket();

        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('The wasThereSignal property should be null but it is not. The afterSocket method might not have been called.');
        $this->signalHelper->beforeSocket();
    }

    public function testShouldReturnTrueOnWasThereSignal()
    {
        $this->signalHelper->beforeSocket();

        posix_kill(getmypid(), \SIGINT);
        pcntl_signal_dispatch();

        $this->assertTrue($this->signalHelper->wasThereSignal());

        $this->signalHelper->afterSocket();
    }
}
