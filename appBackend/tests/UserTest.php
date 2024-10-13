<?php

use PHPUnit\Framework\TestCase;
use Nyholm\Psr7\Factory\Psr17Factory;

class UserTest extends TestCase
{
    protected $app;
    protected $db;

    protected function setUp(): void
    {
        $this->app = require __DIR__ . '/../index.php';

        $this->db = new SQLite3(__DIR__ . '/../chat.db');
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

    public function testGetUsers()
    {
        // Create a GET request to the /users endpoint
        $request = $this->createRequest('GET', '/users');

        $response = $this->app->handle($request);

        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testGetUserById()
    {
        // Insert a test user
        $request = $this->createRequest('POST', '/users', ['username' => 'TestUser']);
        $this->app->handle($request);
    
        // Now retrieve the ID of the user with the username 'TestUser'
        $selectStmt = $this->db->prepare('SELECT id FROM users WHERE username = :username LIMIT 1');
        $selectStmt->bindValue(':username', 'TestUser');
        $result = $selectStmt->execute();
        
        // Fetch the user ID
        $user = $result->fetchArray(SQLITE3_ASSOC);
        $lastInsertId = $user['id'];
        
        // Now retrieve the user by the retrieved ID
        $request = $this->createRequest('GET', "/users/{$lastInsertId}");
        $response = $this->app->handle($request);
    
        $this->assertEquals(200, $response->getStatusCode());
    
        // Clean up by deleting the user from the database
        $deleteStmt = $this->db->prepare('DELETE FROM users WHERE id = :id');
        $deleteStmt->bindValue(':id', $lastInsertId);
        $deleteStmt->execute();
    }
    
}
