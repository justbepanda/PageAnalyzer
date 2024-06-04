<?php

namespace Hexlet\Code\Tests;

use PHPUnit\Framework\TestCase;
use Hexlet\Code\UrlValidator;

class UrlValidatorTest extends TestCase
{
    private $urlValidator;

    protected function setUp(): void
    {
        $this->urlValidator = new UrlValidator();
    }


    public function testNormalize()
    {
        $received = "https://hexlet.io/asdsd";
        $expected = "https://hexlet.io";

        $result = $this->urlValidator->normalize($received);

        $this->assertEquals($expected, $result);
    }

    public function testNormalizeFalse()
    {
        $received = "hexlet.io";

        $result = $this->urlValidator->normalize($received);

        $this->assertFalse($result);
    }

    public function testValidate()
    {
        $receivedData1 = '';
        $expectedData1 = array('URL не должен быть пустым', 'Некорректный URL');
        $result1 = $this->urlValidator->validate($receivedData1);

        $receivedData2 = 'https://hexlethexlethexlethexlethexlethexlethexlethexlethexlethex
        lethexlethexlethexlethexlethexlethexlethexlethexlethexlethexlethexlethexlethexlethe
        xlethexlethexlethexlethexlethexlethexlethexlethexlethexlethexlethexlethexlethexleth
        exlethexlethexlethexlethexlethexlethexlethexlethexlethexlethexlethexlethexlethexlet
        hexlethexlethexlethexlethexlethexlet.io';
        $expectedData2 = array('Слишком длинный URL');
        $result2 = $this->urlValidator->validate($receivedData2);

        $this->assertEquals($expectedData1, $result1);
        $this->assertEquals($expectedData2, $result2);
    }
}
