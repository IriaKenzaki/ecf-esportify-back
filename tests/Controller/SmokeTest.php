<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class SmokeTest extends WebTestCase
{
    /* User organisateur for test:
    *'email' => 'camille@orga.com'
    *'username' => 'organisateur'
    *'password' => 'c@mw0rd'
    *'ROLE_ORGANISATEUR'
    * User admin for test:
    *'email' => 'armin@admin.com'
    *'username' => 'Armin'
    *'password' => 'arm1npa$$'
    * User for test:
    *'email' => 'andi@arthe.com'
    *'username' => 'Username 1'
    *'password' => '@ndya23'
    * Il y as 20 ROLE_USER généré en base pour en utiliser un autre
    * il faudras changer le numero 1 du mail, de l'username et du mot de passe par un autre numero.
    */
    public function testApiDocUrlIsSuccessful(): void
    {
        $client = self::createClient();
        $client->request('GET', '/api/doc');

        self::assertResponseIsSuccessful();
    }

    public function testApiAccountUrlIsSecure(): void
    {
        $client = self::createClient();
        $client->request('GET', '/api/account/me');
        self::assertResponseStatusCodeSame(401);
    }

    public function testLoginRouteCanConnecteAValidUser(): void
    {
        $client = self::createClient();
        $client->followRedirects(false);
        $client->request('POST', 'api/login', [], [], ['CONTENT_TYPE'=> 'application/json',], json_encode([
            'username' => 'andi@arthe.com',
            'password' => '@ndya23',
        ], JSON_THROW_ON_ERROR));

        
        $statusCode = $client->getResponse()->getStatusCode();
        $this->assertEquals(200, $statusCode);
    }

    public function testGetTheListOfUser(): void
    {
        $client = self::createClient();
        $client->request('POST', 'api/login', [], [], ['CONTENT_TYPE'=> 'application/json',], json_encode([
            'username' => 'armin@admin.com',
            'password' => 'arm1npa$$',
        ], JSON_THROW_ON_ERROR), 'GET', '/api/admin/users');
        self::assertResponseIsSuccessful();

    }

    public function testDeleteAUser(): void
    {
        $client = self::createClient();
        $client->followRedirects(false);
        $client->request('POST', 'api/login', [], [], ['CONTENT_TYPE'=> 'application/json',], json_encode([
            'username' => 'armin@admin.com',
            'password' => 'arm1npa$$',
        ], JSON_THROW_ON_ERROR), 'POST', 'api/registration', [], [], ['CONTENT_TYPE'=> 'application/json',], json_encode([
            'email' => 'test2@test.com',
            'password' => 'toto', ], JSON_THROW_ON_ERROR), 'POST', "/api/admin/users/{4}/delete", );

        
        $statusCode = $client->getResponse()->getStatusCode();
        $this->assertEquals(200, $statusCode);
    }


    public function testAccountMeIsSuccessfulWithAValidUser(): void
    {
        $client = self::createClient();
        $client->request('POST', 'api/login', [], [], ['CONTENT_TYPE'=> 'application/json',], json_encode([
            'username' => 'andi@arthe.com',
            'password' => '@ndya23',
        ], JSON_THROW_ON_ERROR), 'GET', '/api/account/me');
        
        $statusCode = $client->getResponse()->getStatusCode();
        $this->assertEquals(200, $statusCode);
    }

    public function testCreateAEventAndShowAllEventIsSuccessful(): void
    {
        $client = self::createClient();
        $client->request('POST', 'api/login', [], [], ['CONTENT_TYPE'=> 'application/json',], json_encode([
            'username' => 'camille@orga.com',
            'password' => 'c@mw0rd',
        ], JSON_THROW_ON_ERROR),'POST', '/api/event', [], [], ['CONTENT_TYPE'=> 'application/json',], json_encode([
            "title" => "Soirée de jeux",
            "description" => "Venez passe un bon moment",
            "players" => 4,
            "dateTimeStart" => "2025-12-01T15:00:00",
            "dateTimeEnd" => "2025-12-01T18:00:00",
            "image" => "Lien de l'image",
            "visibility" => false,], JSON_THROW_ON_ERROR),'GET', '/api/event/all');

            $statusCode = $client->getResponse()->getStatusCode();
            $this->assertEquals(200, $statusCode);
    }

    public function testCreateAEventAndDeleteIt(): void
    {
        $client = self::createClient();
        $client->request('POST', 'api/login', [], [], ['CONTENT_TYPE'=> 'application/json',], json_encode([
            'username' => 'camille@orga.com',
            'password' => 'c@mw0rd',
        ], JSON_THROW_ON_ERROR),'POST', '/api/event', [], [], ['CONTENT_TYPE'=> 'application/json',], json_encode([
            "title" => "Soirée gaming",
            "description" => "Venez pour un moment",
            "players" => 4,
            "dateTimeStart" => "2025-12-01T10:00:00",
            "dateTimeEnd" => "2025-12-01T11:00:00",
            "image" => "Lien / non de l'image",
            "visibility" => false,], JSON_THROW_ON_ERROR),'DELETE', '/api/event/{1}');

            $statusCode = $client->getResponse()->getStatusCode();
            $this->assertEquals(200, $statusCode);
    }


    public function testCreateAEventAndEditIt(): void
    {
        $client = self::createClient();
        $client->request('POST', 'api/login', [], [], ['CONTENT_TYPE'=> 'application/json',], json_encode([
            'username' => 'camille@orga.com',
            'password' => 'c@mw0rd',
        ], JSON_THROW_ON_ERROR),'POST', '/api/event', [], [], ['CONTENT_TYPE'=> 'application/json',], json_encode([
            "title" => "Soirée des jeux",
            "description" => "Venez avec nous pour un bon moment",
            "players" => 4,
            "dateTimeStart" => "2025-12-01T09:00:00",
            "dateTimeEnd" => "2025-12-01T10:00:00",
            "image" => "Image",
            "visibility" => false,], JSON_THROW_ON_ERROR),'PUT', '/api/event/{1}', [], [], ['CONTENT_TYPE'=> 'application/json',], json_encode([
                "title" => "Soirée game",
                "description" => "Venez avec eux pour un bon moment",
                "players" => 10,
                "dateTimeStart" => "2025-12-01T08:00:00",
                "dateTimeEnd" => "2025-12-01T09:00:00",
                "image" => "Lien image",
                "visibility" => false,], JSON_THROW_ON_ERROR));

            $statusCode = $client->getResponse()->getStatusCode();
            $this->assertEquals(200, $statusCode);
    }


    public function testCreateAEventAddAndRemoveParticipant(): void
    {
        $client = self::createClient();
        $client->request('POST', 'api/login', [], [], ['CONTENT_TYPE'=> 'application/json',], json_encode([
            'username' => 'camille@orga.com',
            'password' => 'c@mw0rd',
        ], JSON_THROW_ON_ERROR),'POST', '/api/event', [], [], ['CONTENT_TYPE'=> 'application/json',], json_encode([
            "title" => "Soirée de game",
            "description" => "Venez aux un bon moment",
            "players" => 4,
            "dateTimeStart" => "2025-12-01T19:00:00",
            "dateTimeEnd" => "2025-12-01T20:00:00",
            "image" => "Lien de l'image",
            "visibility" => false,], JSON_THROW_ON_ERROR),'POST', '/api/event/{1}/add-participant', 'DELETE', "/api/event/api/events/{1}/remove-participant");

            $statusCode = $client->getResponse()->getStatusCode();
            $this->assertEquals(200, $statusCode);
    }

    public function testCreateAEventAddAndShowParticipant(): void
    {
        $client = self::createClient();
        $client->request('POST', 'api/login', [], [], ['CONTENT_TYPE'=> 'application/json',], json_encode([
            'username' => 'camille@orga.com',
            'password' => 'c@mw0rd',
        ], JSON_THROW_ON_ERROR),'POST', '/api/event', [], [], ['CONTENT_TYPE'=> 'application/json',], json_encode([
            "title" => "Journée jeux",
            "description" => "Allez pour un bon moment",
            "players" => 4,
            "dateTimeStart" => "2025-12-01T14:00:00",
            "dateTimeEnd" => "2025-12-01T15:00:00",
            "image" => "Lien / non de l'image",
            "visibility" => false,], JSON_THROW_ON_ERROR),'POST', '/api/event/{1}/add-participant', 'GET', "/api/event/api/events/{1}/participants");

            $statusCode = $client->getResponse()->getStatusCode();
            $this->assertEquals(200, $statusCode);
    }

    public function testCreateAEventAndShowMyEvent(): void
    {
        $client = self::createClient();
        $client->request('POST', 'api/login', [], [], ['CONTENT_TYPE'=> 'application/json',], json_encode([
            'username' => 'camille@orga.com',
            'password' => 'c@mw0rd',
        ], JSON_THROW_ON_ERROR),'POST', '/api/event', [], [], ['CONTENT_TYPE'=> 'application/json',], json_encode([
            "title" => "Soirée jeux",
            "description" => "Venez pour un bon moment",
            "players" => 4,
            "dateTimeStart" => "2025-12-01T16:00:00",
            "dateTimeEnd" => "2025-12-01T17:00:00",
            "image" => "Lien ou non de l'image",
            "visibility" => false,], JSON_THROW_ON_ERROR),'POST', '/api/event/{1}/add-participant', 'GET', "/api/event/api/my-events");

            $statusCode = $client->getResponse()->getStatusCode();
            $this->assertEquals(200, $statusCode);
    }

    public function testCreateAEventAndShowMyCreateEvent(): void
    {
        $client = self::createClient();
        $client->request('POST', 'api/login', [], [], ['CONTENT_TYPE'=> 'application/json',], json_encode([
            'username' => 'camille@orga.com',
            'password' => 'c@mw0rd',
        ], JSON_THROW_ON_ERROR),'POST', '/api/event', [], [], ['CONTENT_TYPE'=> 'application/json',], json_encode([
            "title" => "Soirée jeux",
            "description" => "Venez pour un bon moment",
            "players" => 4,
            "dateTimeStart" => "2025-12-01T16:00:00",
            "dateTimeEnd" => "2025-12-01T17:00:00",
            "image" => "Lien ou non de l'image",
            "visibility" => false,], JSON_THROW_ON_ERROR),'POST', '/api/event/{1}/add-participant', 'GET', "/api/event/api/my-created-events");

            $statusCode = $client->getResponse()->getStatusCode();
            $this->assertEquals(200, $statusCode);
    }

    public function testCreateAEventAddAndShowMyCreateEventParticipants(): void
    {
        $client = self::createClient();
        $client->request('POST', 'api/login', [], [], ['CONTENT_TYPE'=> 'application/json',], json_encode([
            'username' => 'camille@orga.com',
            'password' => 'c@mw0rd',
        ], JSON_THROW_ON_ERROR),'POST', '/api/event', [], [], ['CONTENT_TYPE'=> 'application/json',], json_encode([
            "title" => "Soirée jeux",
            "description" => "Venez pour un bon moment",
            "players" => 4,
            "dateTimeStart" => "2025-12-01T16:00:00",
            "dateTimeEnd" => "2025-12-01T17:00:00",
            "image" => "Lien ou non de l'image",
            "visibility" => false,], JSON_THROW_ON_ERROR),'POST', '/api/event/{1}/add-participant', 'GET', "/api/event/api/remove-participant/{1}/{2}");

            $statusCode = $client->getResponse()->getStatusCode();
            $this->assertEquals(200, $statusCode);
    }

    public function testShowNotVisibleEvent(): void
    {
        $client = self::createClient();
        $client->request('POST', 'api/login', [], [], ['CONTENT_TYPE'=> 'application/json',], json_encode([
            'username' => 'armin@admin.com',
            'password' => 'arm1npa$$',
        ], JSON_THROW_ON_ERROR),'POST', '/api/event', [], [], ['CONTENT_TYPE'=> 'application/json',], json_encode([
            "title" => "Soirée jeux",
            "description" => "Venez pour un bon moment",
            "players" => 4,
            "dateTimeStart" => "2025-12-01T16:00:00",
            "dateTimeEnd" => "2025-12-01T17:00:00",
            "image" => "Lien ou non de l'image",
            "visibility" => false,], JSON_THROW_ON_ERROR),'GET', "/api/event/all/not-visible",);

            $statusCode = $client->getResponse()->getStatusCode();
            $this->assertEquals(200, $statusCode);
    }

    public function testEditVisiblityOfEvent(): void
    {
        $client = self::createClient();
        $client->request('POST', 'api/login', [], [], ['CONTENT_TYPE'=> 'application/json',], json_encode([
            'username' => 'armin@admin.com',
            'password' => 'arm1npa$$',
        ], JSON_THROW_ON_ERROR),'POST', '/api/event', [], [], ['CONTENT_TYPE'=> 'application/json',], json_encode([
            "title" => "Soirée jeux",
            "description" => "Venez pour un bon moment",
            "players" => 4,
            "dateTimeStart" => "2025-12-01T16:00:00",
            "dateTimeEnd" => "2025-12-01T17:00:00",
            "image" => "Lien ou non de l'image",
            "visibility" => false,], JSON_THROW_ON_ERROR),'PUT', "/api/event/all/{1}",[], [], ['CONTENT_TYPE'=> 'application/json',], json_encode([
                "visibility" => true,], JSON_THROW_ON_ERROR));

            $statusCode = $client->getResponse()->getStatusCode();
            $this->assertEquals(200, $statusCode);
    }

    public function testAddScores(): void
    {
        $client = self::createClient();
        $client->request('POST', 'api/login', [], [], ['CONTENT_TYPE'=> 'application/json',], json_encode([
            'username' => 'camille@orga.com',
            'password' => 'c@mw0rd',
        ], JSON_THROW_ON_ERROR),'POST', '/api/event', [], [], ['CONTENT_TYPE'=> 'application/json',], json_encode([
            "title" => "Soirée jeux",
            "description" => "Venez pour un bon moment",
            "players" => 4,
            "dateTimeStart" => "2025-12-01T16:00:00",
            "dateTimeEnd" => "2025-12-01T17:00:00",
            "image" => "Lien ou non de l'image",
            "visibility" => false,], JSON_THROW_ON_ERROR),'POST', '/api/event/{1}/add-participant', 'POST', "/api/events/{1}/add-scores", [], [], ['CONTENT_TYPE'=> 'application/json',], json_encode([
            "username" => 'Tota',
            "score" => 100],));

            $statusCode = $client->getResponse()->getStatusCode();
            $this->assertEquals(200, $statusCode);
    }

    public function testShowScores(): void
    {
        $client = self::createClient();
        $client->request('POST', 'api/login', [], [], ['CONTENT_TYPE'=> 'application/json',], json_encode([
            'username' => 'andi@arthe.com',
            'password' => '@ndya23',
        ], JSON_THROW_ON_ERROR),'POST', '/api/event', [], [], ['CONTENT_TYPE'=> 'application/json',], json_encode([
            "title" => "Soirée jeux",
            "description" => "Venez pour un bon moment",
            "players" => 4,
            "dateTimeStart" => "2025-12-01T16:00:00",
            "dateTimeEnd" => "2025-12-01T17:00:00",
            "image" => "Lien ou non de l'image",
            "visibility" => false,], JSON_THROW_ON_ERROR),'POST', '/api/event/{1}/add-participant', 'POST', "/api/events/{1}/add-scores", [], [], ['CONTENT_TYPE'=> 'application/json',], json_encode([
            "username" => 'Tota',
            "score" => 100],), 'GET', '/api/scores');

            $statusCode = $client->getResponse()->getStatusCode();
            $this->assertEquals(200, $statusCode);
    }

    public function testShowDashboard(): void
    {
        $client = self::createClient();
        $client->request('POST', 'api/login', [], [], ['CONTENT_TYPE'=> 'application/json',], json_encode([
            'username' => 'armin@admin.com',
            'password' => 'arm1npa$$',
        ], JSON_THROW_ON_ERROR),'POST', '/api/event', [], [], ['CONTENT_TYPE'=> 'application/json',], json_encode([
            "title" => "Soirée jeux",
            "description" => "Venez pour un bon moment",
            "players" => 4,
            "dateTimeStart" => "2025-12-01T16:00:00",
            "dateTimeEnd" => "2025-12-01T17:00:00",
            "image" => "Lien ou non de l'image",
            "visibility" => false,], JSON_THROW_ON_ERROR), 'GET', '/api/event/admin/dashboard');

            $statusCode = $client->getResponse()->getStatusCode();
            $this->assertEquals(200, $statusCode);

    }


}
