<?php

namespace cakebake\lesscss;

class TestCase extends \PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        $this->tmpDir = realpath(__DIR__ . '/../tmp');
        $this->cacheDir = realpath(__DIR__ . '/../tmp/cache');
    }

    protected function tearDown()
    {
    }
}