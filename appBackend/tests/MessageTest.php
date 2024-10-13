<?php

use PHPUnit\Framework\TestCase;
use Nyholm\Psr7\Factory\Psr17Factory;

class MessageTest extends TestCase
{
    protected $app;
    protected $db;

    protected function setUp(): void
    {
        $this->app = require __DIR__ . '/../index.php';

        // Clear the tables before each test
        $this->db = new SQLite3('chat.db');
    }

    public function createRequest(string $method, string $uri, array $body = [])
    {
        $factory = new Psr17Factory();
        $request = $factory->createServerRequest($method, $uri);
        if ($method === 'POST' && !empty($body)) {
            $request = $request->withParsedBody($body);
        }
        return $request;
    }

    public function testSendMessageToGroup()
    {
        // Send a message to the group
        $request = $this->createRequest('POST', '/groups/1/messages', [
            'user_id' => 1,
            'message' => 'Hello test!'
        ]);
        $response = $this->app->handle($request);
        
        $this->assertEquals(200, $response->getStatusCode());
    
        // Clean up: Delete the message after the test is run
        $deleteStmt = $this->db->prepare("DELETE FROM messages WHERE message = 'Hello test!'");
        $deleteStmt->execute();
    }
    

    public function testGetMessagesInGroup()
    {

        // Send a message to the group
        $this->createRequest('POST', '/groups/1/messages', [
            'user_id' => 1,
            'message' => 'Hello test!'
        ]);

        // Now get messages in the group
        $request = $this->createRequest('GET', '/groups/1/messages');
        $response = $this->app->handle($request);
        $this->assertEquals(200, $response->getStatusCode());

        // Clean up: Delete the message after the test is run
        $deleteStmt = $this->db->prepare("DELETE FROM messages WHERE message = 'Hello test!'");
        $deleteStmt->execute();
    }
}