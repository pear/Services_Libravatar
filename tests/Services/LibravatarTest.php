<?php
require_once 'Services/Libravatar.php';

class Services_LibravatarTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->sl = new Services_Libravatar();
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

}

?>
