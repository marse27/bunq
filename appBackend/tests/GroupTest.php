<?php

use PHPUnit\Framework\TestCase;
use Nyholm\Psr7\Factory\Psr17Factory;

class GroupTest extends TestCase
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

    public function testGetGroups()
    {
        // Create a GET request to the /groups endpoint
        $request = $this->createRequest('GET', '/groups');

        $response = $this->app->handle($request);

        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testCreateGroup()
    {
        // Create a POST request to /groups with a body
        $request = $this->createRequest('POST', '/groups', ['name' => 'TestGroup']);

        $response = $this->app->handle($request);

        $this->assertEquals(200, $response->getStatusCode());

        // Retrieve the last inserted group ID from the SQLite database
        $selectStmt = $this->db->prepare('SELECT id FROM groups WHERE name = :name LIMIT 1');
        $selectStmt->bindValue(':name', 'TestGroup');
        $result = $selectStmt->execute();

        // Fetch the group ID
        $group = $result->fetchArray(SQLITE3_ASSOC);
        $lastInsertId = $group['id'];

        // Clean up by deleting the group from the database
        $deleteStmt = $this->db->prepare('DELETE FROM groups WHERE id = :id');
        $deleteStmt->bindValue(':id', $lastInsertId);
        $deleteStmt->execute();
    }

    public function testGetGroupMessages()
    {
        // Create a POST request to /groups to create a group first
        $request = $this->createRequest('POST', '/groups', ['name' => 'TestGroup']);
        $this->app->handle($request);

        // Retrieve the last inserted group ID from the SQLite database
        $selectStmt = $this->db->prepare('SELECT id FROM groups WHERE name = :name LIMIT 1');
        $selectStmt->bindValue(':name', 'TestGroup');
        $result = $selectStmt->execute();

        // Fetch the group ID
        $group = $result->fetchArray(SQLITE3_ASSOC);
        $lastInsertId = $group['id']; 

        // Now create a GET request to /groups/{groupId}/messages using the last inserted group ID
        $request = $this->createRequest('GET', "/groups/{$lastInsertId}/messages");
        $response = $this->app->handle($request);

        $this->assertEquals(200, $response->getStatusCode());

        // Clean up by deleting the group from the database
        $deleteStmt = $this->db->prepare('DELETE FROM groups WHERE id = :id');
        $deleteStmt->bindValue(':id', $lastInsertId);
        $deleteStmt->execute();
    }
}
