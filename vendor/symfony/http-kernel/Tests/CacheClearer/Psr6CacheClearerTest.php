<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\HttpKernel\Tests\CacheClearer;

use PHPUnit\Framework\TestCase;
use Psr\Cache\CacheItemPoolInterface;
use Symfony\Component\HttpKernel\CacheClearer\Psr6CacheClearer;

class Psr6CacheClearerTest extends TestCase
{
    public function testClearPoolsInjectedInConstructor()
    {
        $pool = $this->getMockBuilder(CacheItemPoolInterface::class)->getMock();
        $pool
            ->expects($this->once())
            ->method('clear');

        (new Psr6CacheClearer(['pool' => $pool]))->clear('');
    }

    public function testClearPool()
    {
        $pool = $this->getMockBuilder(CacheItemPoolInterface::class)->getMock();
        $pool
            ->expects($this->once())
            ->method('clear');

        (new Psr6CacheClearer(['pool' => $pool]))->clearPool('pool');
    }

    public function testClearPoolThrowsExceptionOnUnreferencedPool()
    {
        $this->expectException('InvalidArgumentException');
        $this->expectExceptionMessage('Cache pool not found: "unknown"');
        (new Psr6CacheClearer())->clearPool('unknown');
    }

    /**
     * @group legacy
     * @expectedDeprecation The Symfony\Component\HttpKernel\CacheClearer\Psr6CacheClearer::addPool() method is deprecated since Symfony 3.3 and will be removed in 4.0. Pass an array of pools indexed by name to the constructor instead.
     */
    public function testClearPoolsInjectedByAdder()
    {
        $pool1 = $this->getMockBuilder(CacheItemPoolInterface::class)->getMock();
        $pool1
            ->expects($this->once())
            ->method('clear');

        $pool2 = $this->getMockBuilder(CacheItemPoolInterface::class)->getMock();
        $pool2
            ->expects($this->once())
            ->method('clear');

        $clearer = new Psr6CacheClearer(['pool1' => $pool1]);
        $clearer->addPool($pool2);
        $clearer->clear('');
    }
}
