<?php
/**
 * Created by PhpStorm.
 * User: Rachid
 * Date: 28/08/2018
 * Time: 13:20
 */

namespace Tests\AppBundle\Security;


use AppBundle\Entity\User;
use AppBundle\Security\GithubUserProvider;
use GuzzleHttp\Client;
use JMS\Serializer\Serializer;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;

class GithubUserProviderTest extends TestCase
{
    private $response;
    private $client;
    private $serializer;
    private $streamResponse;

    public function setUp()
    {
        $this->streamResponse = $this->createMock(StreamInterface::class);

        $this->response = $this->getMockBuilder(ResponseInterface::class)
            ->getMock();


        $this->client = $this->getMockBuilder(Client::class)
            ->setMethods(['get'])
            ->getMock();



        $this->serializer = $this->getMockBuilder(Serializer::class)
            ->disableOriginalConstructor()
            ->getMock();
    }

    public function tearDown()
    {
        $this->client = null;
        $this->serializer = null;
        $this->streamedResponse = null;
        $this->response = null;
    }

    public function testLoadUserByUsernameReturningUser(){


        $this->response->method('getBody')
                 ->willReturn($this->streamResponse);


        $this->client->expects(self::once())
                ->method('get')
                ->willReturn($this->response);

        $userData = ['login' => 'a login', 'name' => 'user name', 'email' => 'adress@mail.com', 'avatar_url' => 'url to the avatar', 'html_url' => 'url to profile'];

        $this->serializer->expects(self::once())
                    ->method('deserialize')
                    ->willReturn($userData);


        $provider = new GithubUserProvider($this->client,$this->serializer);

        $expectedUser = new User($userData['login'], $userData['name'], $userData['email'], $userData['avatar_url'], $userData['html_url']);

        $user = $provider->loadUserByUsername('rachid');

        $this->assertEquals($expectedUser,$user);
        $this->assertInstanceOf(User::class,$user);

    }

    public function testLoadUserByUsernameThrowingException(){;

        $this->response->method('getBody')
            ->willReturn($this->streamResponse);


        $this->client->expects(self::once())
            ->method('get')
            ->willReturn($this->response);


        $this->serializer->expects(self::once())
            ->method('deserialize')
            ->willReturn([]);

        $provider = new GithubUserProvider($this->client,$this->serializer);

        $this->expectException('LogicExceptionn');

        $user = $provider->loadUserByUsername('rachid');
    }
}