<?php
/**
 * (c) Damian Sromek <damian.sromek@gmail.com>
 *
 * Do not redistribute.
 */
namespace Paq\GameBundle\Tests\Unit\Service;

use Paq\GameBundle\Service\AndroidBridge;

class AndroidBridgeTest extends \PHPUnit_Framework_TestCase
{
    public function testConstructorRequiresSecret()
    {
        try {
            $androidBridge = new AndroidBridge(21);
            $this->fail('Android Bridge must force passing a "secret" string');
        } catch (\Exception $ex) {
            $this->assertInstanceOf('\InvalidArgumentException', $ex);
        }

        $androidBridge = new AndroidBridge('someSecret');
        $this->assertInstanceOf('\Paq\GameBundle\Service\AndroidBridge', $androidBridge);
    }

    public function testCreateChecksum()
    {
        $androidBridge = new AndroidBridge('someSecret');
        $checksum = $androidBridge->createChecksum('someValue');
        $this->assertInternalType('string', $checksum);
        $this->assertNotEmpty($checksum);
    }

    public function testGetUsernameFromEmail()
    {
        $email = 'jack@somemail.com';
        $androidBridge = new AndroidBridge('foo');
        $this->assertEquals('jack', $androidBridge->getUsernameFromEmail($email));
    }

    public function testCreatePasswordForEmail()
    {
        $androidBridge = new AndroidBridge('foo');
        $password = $androidBridge->getDefaultPasswordForEmail('jack@somemail.com');
        $this->assertInternalType('string', $password);
        $this->assertNotEmpty($password);
    }
}
