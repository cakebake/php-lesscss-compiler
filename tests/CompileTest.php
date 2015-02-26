<?php

class CompileTest extends cakebake\lesscss\TestCase
{
    public function testGenerateCssFileWithCache()
    {
        $less = new \cakebake\lesscss\LessConverter();
        $less->init([
            [
                'input' => __DIR__ . '/example-1.less',
                'webFolder' => '../tests',
            ],
            [
                'input' => __DIR__ . '/example-2.less',
                'webFolder' => '../tests',
            ],
        ], $this->tmpDir . DIRECTORY_SEPARATOR . str_replace(array('::', ':'), '_', __METHOD__) . '.css');
    }
}