<?php
/**
 * Zend Framework
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://framework.zend.com/license/new-bsd
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@zend.com so we can send you a copy immediately.
 *
 * @category   Zend
 * @package    Zend_Xml_Security
 * @subpackage UnitTests
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id$
 */


/**
 * @category   Zend
 * @package    Zend_Xml_Security
 * @subpackage UnitTests
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @group      Zend_Xml
 */
class Zend_Xml_SecurityTest extends PHPUnit\Framework\TestCase
{
    public function testScanForXEE()
    {
        $xml = <<<XML
<?xml version="1.0"?>
<!DOCTYPE results [<!ENTITY harmless "completely harmless">]>
<results>
    <result>This result is &harmless;</result>
</results>
XML;

        $this->expectException('Zend_Xml_Exception');
        $result = Zend_Xml_Security::scan($xml);
    }

    public function testScanForXXE()
    {
        $file = tempnam(sys_get_temp_dir(), 'Zend_XML_Security');
        file_put_contents($file, 'This is a remote content!');
        $xml = <<<XML
<?xml version="1.0"?>
<!DOCTYPE root
[
<!ENTITY foo SYSTEM "file://$file">
]>
<results>
    <result>&foo;</result>
</results>
XML;
        $this->expectException('Zend_Xml_Exception');
        $result = Zend_Xml_Security::scan($xml);

        unlink($file);
    }

    public function testScanSimpleXmlResult()
    {
        $result = Zend_Xml_Security::scan($this->_getXml());
        $this->assertInstanceOf(SimpleXMLElement::class, $result);
        $this->assertEquals((string) $result->result, 'test');
    }

    public function testScanDom()
    {
        $dom    = new DOMDocument('1.0');
        $result = Zend_Xml_Security::scan($this->_getXml(), $dom);
        $this->assertInstanceOf(DOMDocument::class, $result);
        $node = $result->getElementsByTagName('result')->item(0);
        $this->assertEquals($node->nodeValue, 'test');
    }

    public function testScanInvalidXml()
    {
        $xml = <<<XML
<foo>test</bar>
XML;

        $result = Zend_Xml_Security::scan($xml);
        $this->assertFalse($result);
    }

    public function testScanInvalidXmlDom()
    {
        $xml = <<<XML
<foo>test</bar>
XML;

        $dom    = new DOMDocument('1.0');
        $result = Zend_Xml_Security::scan($xml, $dom);
        $this->assertFalse($result);
    }

    public function testScanFile()
    {
        $file = tempnam(sys_get_temp_dir(), 'Zend_XML_Security');
        file_put_contents($file, $this->_getXml());

        $result = Zend_Xml_Security::scanFile($file);
        $this->assertInstanceOf(SimpleXMLElement::class, $result);
        $this->assertEquals((string) $result->result, 'test');
        unlink($file);
    }

    public function testScanXmlWithDTD()
    {
        $xml = <<<XML
<?xml version="1.0"?>
<!DOCTYPE results [
<!ELEMENT results (result+)>
<!ELEMENT result (#PCDATA)>
]>
<results>
    <result>test</result>
</results>
XML;

        $dom    = new DOMDocument('1.0');
        $result = Zend_Xml_Security::scan($xml, $dom);
        $this->assertInstanceOf(DOMDocument::class, $result);
        $this->assertTrue($result->validate());
    }

    protected function _getXml()
    {
        return <<<XML
<?xml version="1.0"?>
<results>
    <result>test</result>
</results>
XML;
    }
}
