<?php
require_once 'Services/Libravatar.php';

class Services_LibravatarTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->sl = new Services_Libravatar();
    }

    public function testUrl()
    {
        $this->loadSLMock();
        $this->assertEquals(
            'http://example.org/avatar/9e263681488308e5e5d5e548b2f9bc99',
            $this->sl->url('cweiske@cweiske.de')
        );
    }

    public function testGetUrl()
    {
        $this->loadSLMock();
        $this->assertEquals(
            'http://example.org/avatar/9e263681488308e5e5d5e548b2f9bc99',
            $this->sl->getUrl('cweiske@cweiske.de')
        );
    }

    public function testGetUrlCasing()
    {
        $this->loadSLMock();
        $this->assertEquals(
            'http://example.org/avatar/9e263681488308e5e5d5e548b2f9bc99',
            $this->sl->getUrl('CWeiske@cWeiske.de')
        );
    }

    public function testGetUrlHttpsDefault()
    {
        $this->loadSLMock();
        $this->sl->setHttps(true);
        $this->assertEquals(
            'https://example.org/avatar/9e263681488308e5e5d5e548b2f9bc99',
            $this->sl->getUrl('cweiske@cweiske.de')
        );
    }

    public function testGetUrlHttpsOptionOff()
    {
        $this->loadSLMock();
        $this->sl->setHttps(true);
        $this->assertEquals(
            'http://example.org/avatar/9e263681488308e5e5d5e548b2f9bc99',
            $this->sl->getUrl('cweiske@cweiske.de', array('https' => false))
        );
    }

    public function testGetUrlAlgorithmDefault()
    {
        $this->loadSLMock();
        $this->sl->setAlgorithm('sha256');
        $this->assertEquals(
            'http://example.org/avatar/baa4e986ac6bb3f3715de5b08727be61d33afb4b03e792eb6db2f184a61626d6',
            $this->sl->getUrl('cweiske@cweiske.de')
        );
    }

    public function testGetUrlAlgorithmOption()
    {
        $this->loadSLMock();
        $this->assertEquals(
            'http://example.org/avatar/baa4e986ac6bb3f3715de5b08727be61d33afb4b03e792eb6db2f184a61626d6',
            $this->sl->getUrl('cweiske@cweiske.de', array('algorithm' => 'sha256'))
        );
    }

    public function testGetUrlSizeDefault()
    {
        $this->loadSLMock();
        $this->sl->setSize(128);
        $this->assertEquals(
            'http://example.org/avatar/9e263681488308e5e5d5e548b2f9bc99?size=128',
            $this->sl->getUrl('cweiske@cweiske.de')
        );
    }

    public function testGetUrlSizeOption()
    {
        $this->loadSLMock();
        $this->sl->setSize(128);
        $this->assertEquals(
            'http://example.org/avatar/9e263681488308e5e5d5e548b2f9bc99?size=256',
            $this->sl->getUrl('cweiske@cweiske.de', array('size' => 256))
        );
    }

    /**
     * Note that this short option is deprecated
     */
    public function testGetUrlSizeOptionShort()
    {
        $this->loadSLMock();
        $this->sl->setSize(128);
        $this->assertEquals(
            'http://example.org/avatar/9e263681488308e5e5d5e548b2f9bc99?size=256',
            $this->sl->getUrl('cweiske@cweiske.de', array('s' => 256))
        );
    }

    public function testGetUrlDefaultDefault()
    {
        $this->loadSLMock();
        $this->sl->setDefault('identicon');
        $this->assertEquals(
            'http://example.org/avatar/9e263681488308e5e5d5e548b2f9bc99?default=identicon',
            $this->sl->getUrl('cweiske@cweiske.de')
        );
    }

    public function testGetUrlDefaultOption()
    {
        $this->loadSLMock();
        $this->assertEquals(
            'http://example.org/avatar/9e263681488308e5e5d5e548b2f9bc99?default=404',
            $this->sl->getUrl('cweiske@cweiske.de', array('default' => 404))
        );
    }

    /**
     * Note that this short option is deprecated
     */
    public function testGetUrlDefaultOptionShort()
    {
        $this->loadSLMock();
        $this->assertEquals(
            'http://example.org/avatar/9e263681488308e5e5d5e548b2f9bc99?default=404',
            $this->sl->getUrl('cweiske@cweiske.de', array('d' => 404))
        );
    }

    /**
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage Invalid option in array: foo
     */
    public function testGetUrlOptionInvalid()
    {
        $this->sl->getUrl('cweiske@cweiske.de', array('foo' => 123));
    }

    public function testGetUrlNoIdentifier()
    {
        $this->loadSLMock();
        $this->assertEquals(
            'http://example.org/avatar/d41d8cd98f00b204e9800998ecf8427e',
            $this->sl->getUrl(false)
        );
    }

    public function testGetUrlOpenId()
    {
        $this->loadSLMock();
        $this->assertEquals(
            'http://example.org/avatar/b5bbeb6202fa01ca1deb8809716a1492f013a8896abf6e11b651fdf1cde23380',
            $this->sl->getUrl('cweiske.de')
        );
    }

    public function testGetUrlInvalid()
    {
        $this->assertEquals(
            'http://cdn.libravatar.org/avatar/e3b0c44298fc1c149afbf4c8996fb92427ae41e4649b934ca495991b7852b855',
            $this->sl->getUrl('adam@adam-laptop.(none)')
        );
    }

    public function testNormalizeOpenId()
    {
        $this->assertEquals(
            'https://example.org/',
            Services_Libravatar::normalizeOpenId('https://example.org/')
        );
    }

    public function testNormalizeOpenIdCasing()
    {
        $this->assertEquals(
            'https://example.org/BaR?Foo',
            Services_Libravatar::normalizeOpenId('Https://examPLe.Org/BaR?Foo#mE')
        );
    }

    public function testNormalizeOpenIdPortDefault()
    {
        $this->assertEquals(
            'http://example.org/',
            Services_Libravatar::normalizeOpenId('http://example.org:80/')
        );
        $this->assertEquals(
            'https://example.org/',
            Services_Libravatar::normalizeOpenId('https://example.org:443/')
        );
    }

    public function testNormalizeOpenIdPortNonDefault()
    {
        $this->assertEquals(
            'http://example.org:123/',
            Services_Libravatar::normalizeOpenId('http://example.org:123/')
        );
        $this->assertEquals(
            'https://example.org:234/',
            Services_Libravatar::normalizeOpenId('https://example.org:234/')
        );
    }

    public function testNormalizeOpenIdUsername()
    {
        $this->assertEquals(
            'https://User@example.org/',
            Services_Libravatar::normalizeOpenId('Https://User@examPLe.Org/')
        );
    }

    public function testNormalizeOpenIdPassword()
    {
        $this->assertEquals(
            'https://:pAss@example.org/',
            Services_Libravatar::normalizeOpenId('Https://:pAss@examPLe.Org/')
        );
    }

    public function testNormalizeOpenIdUserAndPass()
    {
        $this->assertEquals(
            'https://User:Pass@example.org/',
            Services_Libravatar::normalizeOpenId('Https://User:Pass@examPLe.Org/')
        );
    }

    public function testNormalizeOpenIdXRI()
    {
        $this->assertEquals(
            'foo',
            Services_Libravatar::normalizeOpenId('xri://foo')
        );
    }

    public function testNormalizeOpenIdXRIGlobalSymbol()
    {
        $this->assertEquals(
            '=bar',
            Services_Libravatar::normalizeOpenId('=bar')
        );
    }

    public function testNormalizeOpenIdMissingProtocol()
    {
        $this->assertEquals(
            'http://example.org/',
            Services_Libravatar::normalizeOpenId('example.org/')
        );
    }

    public function testNormalizeOpenIdMissingSlash()
    {
        $this->assertEquals(
            'http://example.org/',
            Services_Libravatar::normalizeOpenId('http://example.org')
        );
    }

    public function testNormalizeOpenIdInvalid()
    {
        $this->assertEquals(
            '',
            Services_Libravatar::normalizeOpenId('http://e=g/')
        );
    }

    public function testDomainGetEmail()
    {
        $this->assertEquals(
            'example.org',
            $this->callProtected('domainGet', 'user@example.org')
        );
    }

    public function testDomainGetNull()
    {
        $this->assertEquals(
            null,
            $this->callProtected('domainGet', null)
        );
    }

    public function testDomainGetOpenId()
    {
        $this->assertEquals(
            'example.org',
            $this->callProtected('domainGet', 'http://example.org/')
        );
    }

    public function testDomainGetOpenIdNonDefaultPort()
    {
        $this->assertEquals(
            'example.org:123',
            $this->callProtected('domainGet', 'http://example.org:123/')
        );
        $this->assertEquals(
            'example.org:234',
            $this->callProtected('domainGet', 'https://example.org:234/')
        );
    }



    public function testSetAlgorithmValid()
    {
        $this->sl->setAlgorithm('md5');
        $this->assertEquals('md5', $this->getProtected('algorithm', $this->sl));

        $this->sl->setAlgorithm('sha256');
        $this->assertEquals('sha256', $this->getProtected('algorithm', $this->sl));
    }

    /**
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage Only md5 and sha256 hashing supported
     */
    public function testSetAlgorithmInvalid()
    {
        $this->sl->setAlgorithm('foo');
    }

    public function testSetDefaultIdenticon()
    {
        $this->sl->setDefault('identicon');
        $this->assertEquals('identicon', $this->getProtected('default', $this->sl));
    }

    public function testSetDefaultUrl()
    {
        $this->sl->setDefault('http://example.org/default.png');
        $this->assertEquals(
            'http://example.org/default.png',
            $this->getProtected('default', $this->sl)
        );
    }

    public function testSetDefaultNull()
    {
        $this->sl->setDefault(null);
        $this->assertNull(
            $this->getProtected('default', $this->sl)
        );
    }

    /**
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage Invalid default avatar URL
     */
    public function testSetDefaultInvalidShortcut()
    {
        $this->sl->setDefault('foo');
    }

    /**
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage Invalid default avatar URL
     */
    public function testSetDefaultInvalidUrl()
    {
        //missing protocol
        $this->sl->setDefault('example.org/default.png');
    }

    public function testSetHttps()
    {
        $this->sl->setHttps(true);
        $this->assertEquals(true, $this->getProtected('https', $this->sl));
    }

    public function testSetSize()
    {
        $this->sl->setSize(32);
        $this->assertEquals(32, $this->getProtected('size', $this->sl));
    }

    public function testSetSizeNull()
    {
        $this->sl->setSize(null);
        $this->assertNull($this->getProtected('size', $this->sl));
    }

    /**
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage Size has to be larger than 0
     */
    public function testSetSizeInvalid()
    {
        $this->sl->setSize(-21);
    }

    public function testDetectHttpsOn()
    {
        $_SERVER['HTTPS'] = 'on';
        $this->sl->detectHttps();
        $this->assertEquals(true, $this->getProtected('https', $this->sl));
    }

    public function testDetectHttpsOff()
    {
        unset($_SERVER['HTTPS']);
        $this->sl->detectHttps();
        $this->assertEquals(false, $this->getProtected('https', $this->sl));
    }


    /**
     * Get the value of a protected/private class property
     */
    protected function getProtected($variable, $object)
    {
        $rc = new ReflectionClass($object);
        $prop = $rc->getProperty($variable);
        $prop->setAccessible(true);
        return $prop->getValue($object);
    }

    /**
     * Call a protected/private class method
     *
     * @param string $method Method name
     * @param mixed  $arg1   Any number of method arguments
     */
    protected function callProtected($method)
    {
        $args = func_get_args();
        array_shift($args);

        $rc = new ReflectionClass($this->sl);
        $method = $rc->getMethod($method);
        $method->setAccessible(true);
        return $method->invokeArgs($this->sl, $args);
    }

    protected function loadSLMock()
    {
        $this->sl = $this->getMock('Services_Libravatar', array('srvGet'));
        $this->sl->expects($this->once())
            ->method('srvGet')
            ->will($this->returnValue('example.org'));
    }
}

?>
