<?php

namespace AdirKuhn\UserBundle\Tests\Controller;

use Doctrine\ORM\Tools\SchemaTool;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\JsonResponse;

class UserControllerTest extends WebTestCase
{

    private $client;
    private $schemaTool;
    private $entityManager;

    private $user;

    protected function setUp()
    {
        $this->client = static::createClient(array('test', true));
        $this->entityManager = $this->client->getContainer()->get('doctrine')->getManager();

        $this->schemaTool = new SchemaTool($this->entityManager);

        $this->schemaTool->createSchema($this->entityManager->getMetadataFactory()->getAllMetadata());
    }

    protected function tearDown() {
        $this->schemaTool->dropDatabase();
    }

    //testa adicionar novo usuario atraves de post
    public function testUserAdd()
    {
        $this->user = array(
            'name' => 'John Last',
            'email' => 'john@last.com',
            'password' => 'soyjohn',
            'isActive' => true
        );

        //add user
        $request = $this->client->request('POST', '/user/add', $this->user);
        $response = $this->client->getResponse();
        $user = json_decode($response->getContent(), true);
        $this->user['id'] = $user['id'];

        $this->assertEquals(JsonResponse::HTTP_CREATED, $response->getStatusCode());
    }

    //teste para pegar informacoes de um usuario
    public function testUserGetInfo()
    {
        //adduser
        $this->testUserAdd();

        //test Get user
        $request = $this->client->request('GET', '/user/' . $this->user['id']);
        $response = $this->client->getResponse();

        $content = json_decode($response->getContent(), true);

        //test get user data
        $this->assertEquals(JsonResponse::HTTP_OK, $response->getStatusCode());
        $this->assertEquals($this->user['id'], $content['id']);
        $this->assertEquals($this->user['name'], $content['name']);
        $this->assertEquals($this->user['email'], $content['email']);
        $this->assertEquals($this->user['isActive'], $content['isActive']);
    }

    public function testUserDelete()
    {
        //create user
        $this->testUserAdd();

        //delete user
        $request = $this->client->request('DELETE', '/user/delete/' . $this->user['id']);
        $response = $this->client->getResponse();

        $this->assertEquals(JsonResponse::HTTP_NO_CONTENT, $response->getStatusCode());
    }

    public function testUserUpdate() {

        //adduser
        $this->testUserAdd();

        //new user data
        $userUpdate = [
            'id' => $this->user['id'],
            'name' => 'John First',
        ];

        $request = $this->client->request('POST', '/user/update', $userUpdate);
        $response = $this->client->getResponse();

        $content = json_decode($response->getContent(), true);

        //check name
        $this->assertEquals(JsonResponse::HTTP_OK, $response->getStatusCode());
        $this->assertEquals($userUpdate['id'], $content['id']);
        $this->assertEquals($userUpdate['name'], $content['name']);

        //new user data
        $userUpdate = [
            'id' => $this->user['id'],
            'email' => 'johnf@patobeta3.com',
        ];

        $request = $this->client->request('POST', '/user/update', $userUpdate);
        $response = $this->client->getResponse();

        $content = json_decode($response->getContent(), true);

        //check email
        $this->assertEquals(JsonResponse::HTTP_OK, $response->getStatusCode());
        $this->assertEquals($userUpdate['id'], $content['id']);
        $this->assertEquals($userUpdate['email'], $content['email']);

        //new user data
        $userUpdate = [
            'id' => $this->user['id'],
            'isActive' => false
        ];

        $request = $this->client->request('POST', '/user/update', $userUpdate);
        $response = $this->client->getResponse();

        $content = json_decode($response->getContent(), true);

        //check email
        $this->assertEquals(JsonResponse::HTTP_OK, $response->getStatusCode());
        $this->assertEquals($userUpdate['id'], $content['id']);
        $this->assertEquals($userUpdate['isActive'], $content['isActive']);
    }


    //test list all users
    public function testListAllUsers()
    {
        $users = array(
            array(
                'name' => 'User One',
                'email' => 'userone@pb.com',
                'password' => 'user',
                'isActive' => true
            ),
            array(
                'name' => 'User Two',
                'email' => 'usertwo@pb.com',
                'password' => 'user',
                'isActive' => true
            ),
            array(
                'name' => 'User Three',
                'email' => 'userthree@pb.com',
                'password' => 'user',
                'isActive' => true
            ),
        );

        //test users inserts
        foreach($users as &$user) {
            $request = $this->client->request('POST', '/user/add', $user);
            $response = $this->client->getResponse();
            $user = json_decode($response->getContent(), true);
            $user['id'] = $user['id'];

            $this->assertEquals(JsonResponse::HTTP_CREATED, $response->getStatusCode());
        }

        //get all users
        $request = $this->client->request("GET", '/user/all');
        $response = $this->client->getResponse();
        //$responseUsers = json_decode($response->getContent(), true);

        var_dump($response->getContent());
    }


}
