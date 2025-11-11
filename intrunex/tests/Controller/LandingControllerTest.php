<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class LandingControllerTest extends WebTestCase
{
    public function testLandingPageLoads()
    {
        $client = static::createClient();
        $client->request('GET', '/');

        $this->assertResponseIsSuccessful();
    }

    public function testLoginPageLoads()
    {
        $client = static::createClient();
        $client->request('GET', '/login');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Please Sign In'); // Assuming your login page has an h1 with "Please Sign In"
    }

    public function testAnalyticsPageRequiresAuthentication()
    {
        $client = static::createClient();
        $client->request('GET', '/analytics');

        // Expect a redirect to the login page
        $this->assertResponseRedirects('/login');
    }

    public function testPageNotFound()
    {
        $client = static::createClient();
        $client->request('GET', '/non-existent-page');

        $this->assertResponseStatusCodeSame(404);
    }
}