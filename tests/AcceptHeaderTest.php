<?php

require 'AcceptHeader.php';

use PHPUnit\Framework\TestCase;

class ContainerTest extends TestCase
{

    public function testHeader1() 
    {
        $acceptHeader = new AcceptHeader('audio/*; q=0.2, audio/basic');
        $this->assertEquals('audio/basic', $this->_getMedia($acceptHeader[0]));
        $this->assertEquals('audio/*; q=0.2', $this->_getMedia($acceptHeader[1]));
    }

    public function testHeader2()
    {
        $acceptHeader = new AcceptHeader('text/*;q=0.3, text/html;q=0.7, text/html;level=1, text/html;level=2;q=0.4, */*;q=0.5');
        $this->assertEquals('text/html; level=1', $this->_getMedia($acceptHeader[0]));
        $this->assertEquals('text/html; q=0.7', $this->_getMedia($acceptHeader[1]));
        $this->assertEquals('*/*; q=0.5', $this->_getMedia($acceptHeader[2]));
        $this->assertEquals('text/html; level=2; q=0.4', $this->_getMedia($acceptHeader[3]));
        $this->assertEquals('text/*; q=0.3', $this->_getMedia($acceptHeader[4]));
    }

    public function testHeader3()
    {
        $acceptHeader = new AcceptHeader('text/*, text/html, text/html;level=1, */*');
        $this->assertEquals('text/html; level=1', $this->_getMedia($acceptHeader[0]));
        $this->assertEquals('text/html', $this->_getMedia($acceptHeader[1]));
        $this->assertEquals('text/*', $this->_getMedia($acceptHeader[2]));
        $this->assertEquals('*/*', $this->_getMedia($acceptHeader[3]));
    }

    public function testHeader4()
    {
        $acceptHeader = new AcceptHeader('text, text/html');
        $this->assertEquals('text/html', $this->_getMedia($acceptHeader[0]));
    }

    private function _getMedia(array $mediaType) 
    {
        $str = $mediaType['type'] . '/' . $mediaType['subtype'];
        if (!empty($mediaType['params'])) {
            foreach ($mediaType['params'] as $k => $v) {
                $str .= '; ' . $k . '=' . $v;
            }
        }
        return $str;
    }
}

