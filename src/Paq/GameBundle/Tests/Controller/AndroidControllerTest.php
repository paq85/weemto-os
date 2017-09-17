<?php
/**
 * (c) Damian Sromek <damian.sromek@gmail.com>
 *
 * Do not redistribute.
 */
namespace Controller;

use Paq\GameBundle\Tests\Integration\IntegrationTestCase;
use Symfony\Component\HttpFoundation\RedirectResponse;

class AndroidControllerTest extends IntegrationTestCase
{
    public function testRedirectAndroidApp()
    {
        // this is a landing page for Android's App WebView
        $crawler = $this->client->request('GET', '/pl/android-pl');

        $response = $this->client->getResponse();
        $this->assertRedirectToRoute('paqgame_gui_start', $response, 'Android app landing page should in the end be the main page');
    }

    public function testAutoLogInNewUser()
    {
        $this->assertCanAutoLogIn('damianisko@gmail.com', 'aaaaaaaaa');
    }

    public function testAutoLogInExistingUser()
    {
        $this->assertCanAutoLogIn('damian@foo.bar', 'Damian');
    }

    private function assertCanAutoLogIn($email, $username)
    {
        $androidBridge = $this->getContainer()->get('paqgame.android_bridge');
        $checksum = $androidBridge->createChecksum($email);
        // this is a landing page for Android's App WebView
        $this->client->followRedirects();
        $crawler = $this->client->request('GET', "/pl/android-pl?email=$email&email_checksum=$checksum");

        /* @var RedirectResponse $response */
        $response = $this->client->getResponse();

        $this->assertEquals(200, $response->getStatusCode(), $response->getContent());
        $this->assertContains($username, $response->getContent());
        $this->assertUserExists(['username' => $username, 'email' => $email]);
    }
}
