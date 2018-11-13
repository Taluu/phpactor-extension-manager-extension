<?php

namespace Phpactor\Extension\ExtensionManager\Tests\Unit\Command;

use PHPUnit\Framework\TestCase;
use Phpactor\Extension\ExtensionManager\Command\InstallCommand;
use Phpactor\Extension\ExtensionManager\Service\InstallerService;
use Symfony\Component\Console\Tester\CommandTester;

class InstallCommandTest extends TestCase
{
    /**
     * @var ObjectProphecy
     */
    private $installer;

    /**
     * @var CommandTester
     */
    private $tester;


    public function setUp()
    {
        $this->installer = $this->prophesize(InstallerService::class);
        $this->tester = new CommandTester(new InstallCommand($this->installer->reveal()));
    }

    public function testItCallsTheInstaller()
    {
        $this->installer->install()->shouldBeCalled();

        $this->tester->execute([]);
        $this->assertEquals(0, $this->tester->getStatusCode());
    }

    public function testItInstallsASingleExtension()
    {
        $this->tester->execute([
            'extension' => [ 'foobar' ]
        ]);

        $this->installer->addExtension('foobar')->shouldHaveBeenCalled();
        $this->installer->installForceUpdate()->shouldHaveBeenCalled();

        $this->assertEquals(0, $this->tester->getStatusCode());
    }

    public function testItInstallsManyExtensions()
    {
        $this->tester->execute([
            'extension' => [ 'foobar', 'barfoo' ]
        ]);

        $this->installer->addExtension('foobar')->shouldHaveBeenCalled();
        $this->installer->addExtension('barfoo')->shouldHaveBeenCalled();
        $this->installer->installForceUpdate()->shouldHaveBeenCalled();

        $this->assertEquals(0, $this->tester->getStatusCode());
    }
}