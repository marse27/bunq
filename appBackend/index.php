<?php
require __DIR__ . '/vendor/autoload.php';

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;
use Slim\Routing\RouteContext;
use Slim\Middleware\BodyParsingMiddleware;

$app = AppFactory::create();

$app->add(function (Request $request, $handler) {
    $response = $handler->handle($request);
    return $response
        ->withHeader('Access-Control-Allow-Origin', '*')
        ->withHeader('Access-Control-Allow-Headers', 'X-Requested-With, Content-Type, Accept, Origin, Authorization')
        ->withHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS');
});
$db = new SQLite3(__DIR__ . '/chat.db');

$app->addBodyParsingMiddleware();

$app->options('/{routes:.+}', function (Request $request, Response $response, $args) {
    return $response;
});

// Route for testing the app
$app->get('/', function (Request $request, Response $response, array $args) {
    $response->getBody()->write("Hello, BUNQ user :)!");
    return $response;
});

// ------------------------------------------
// 1. Get all users
$app->get('/users', function (Request $request, Response $response, array $args) {
    $db = new SQLite3('chat.db');
    $stmt = $db->query('SELECT * FROM users');
    $users = [];

    while ($row = $stmt->fetchArray(SQLITE3_ASSOC)) {
        $users[] = $row;
    }

    $response->getBody()->write(json_encode($users));
    return $response->withHeader('Content-Type', 'application/json');
});

// 2. Create a new user
$app->post('/users', function (Request $request, Response $response, array $args) {
    $data = $request->getParsedBody();
    $username = $data['username'] ?? '';

    if (empty($username)) {
        $response->getBody()->write(json_encode(['error' => 'Username required']));
        return $response->withStatus(400)->withHeader('Content-Type', 'application/json');
    }

    $db = new SQLite3('chat.db');
    $stmt = $db->prepare('INSERT INTO users (username) VALUES (:username)');
    $stmt->bindValue(':username', $username);
    $stmt->execute();

    $response->getBody()->write(json_encode(['success' => true]));
    return $response->withHeader('Content-Type', 'application/json');
});

// 3. Get a specific user by ID
$app->get('/users/{id}', function (Request $request, Response $response, array $args) {
    $userId = $args['id'];

    $db = new SQLite3('chat.db');
    $stmt = $db->prepare('SELECT * FROM users WHERE id = :id');
    $stmt->bindValue(':id', $userId);
    $result = $stmt->execute();

    $user = $result->fetchArray(SQLITE3_ASSOC);

    if ($user) {
        $response->getBody()->write(json_encode($user));
    } else {
        $response->getBody()->write(json_encode(['error' => 'User not found']));
        return $response->withStatus(404);
    }

    return $response->withHeader('Content-Type', 'application/json');
});

// ------------------------------------------
// 4. Get all groups
$app->get('/groups', function (Request $request, Response $response, array $args) {
    $db = new SQLite3('chat.db');
    $stmt = $db->query('SELECT * FROM groups');
    $groups = [];

    while ($row = $stmt->fetchArray(SQLITE3_ASSOC)) {
        $groups[] = $row;
    }

    $response->getBody()->write(json_encode($groups));
    return $response->withHeader('Content-Type', 'application/json');
});

// 5. Create a new group
$app->post('/groups', function (Request $request, Response $response, array $args) {
    $data = $request->getParsedBody();
    $groupName = $data['name'] ?? '';

    if (empty($groupName)) {
        $response->getBody()->write(json_encode(['error' => 'Group name required']));
        return $response->withStatus(400)->withHeader('Content-Type', 'application/json');
    }

    $db = new SQLite3('chat.db');
    $stmt = $db->prepare('INSERT INTO groups (name) VALUES (:name)');
    $stmt->bindValue(':name', $groupName);
    $stmt->execute();

    $response->getBody()->write(json_encode(['success' => true]));
    return $response->withHeader('Content-Type', 'application/json');
});

// ------------------------------------------
// 6. Join a group
$app->post('/groups/{groupId}/join', function (Request $request, Response $response, array $args) {
    $groupId = $args['groupId'];
    $data = $request->getParsedBody();
    $userId = $data['user_id'] ?? '';

    if (empty($userId)) {
        $response->getBody()->write(json_encode(['error' => 'User ID required']));
        return $response->withStatus(400)->withHeader('Content-Type', 'application/json');
    }

    $db = new SQLite3('chat.db');
    $stmt = $db->prepare('SELECT * FROM groups WHERE id = :groupId');
    $stmt->bindValue(':groupId', $groupId);
    $result = $stmt->execute();
    $group = $result->fetchArray(SQLITE3_ASSOC);

    if (!$group) {
        $response->getBody()->write(json_encode(['error' => 'Group not found']));
        return $response->withStatus(404)->withHeader('Content-Type', 'application/json');
    }

    $response->getBody()->write(json_encode(['success' => 'User joined the group']));
    return $response->withHeader('Content-Type', 'application/json');
});

// ------------------------------------------
// 7. Get all messages in a group
$app->get('/groups/{groupId}/messages', function (Request $request, Response $response, array $args) {
    $groupId = $args['groupId'];

    $db = new SQLite3('chat.db');

    $stmt = $db->prepare('SELECT message, timestamp 
                          FROM messages 
                          WHERE group_id = :group_id 
                          ORDER BY timestamp ASC');
    $stmt->bindValue(':group_id', $groupId, SQLITE3_INTEGER);
    $result = $stmt->execute();

    $messages = [];
    while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
        $messages[] = $row;
    }

    $response->getBody()->write(json_encode($messages));
    return $response->withHeader('Content-Type', 'application/json');
});

// ------------------------------------------
// 8. Send a message to a group
$app->post('/groups/{groupId}/messages', function (Request $request, Response $response, array $args) {
    $groupId = $args['groupId'];
    $data = $request->getParsedBody();
    $userId = $data['user_id'] ?? '';
    $message = $data['message'] ?? '';

    if (empty($userId) || empty($message)) {
        $response->getBody()->write(json_encode(['error' => 'User ID and message required']));
        return $response->withStatus(400)->withHeader('Content-Type', 'application/json');
    }

    $db = new SQLite3('chat.db');
    $stmt = $db->prepare('INSERT INTO messages (user_id, group_id, message) VALUES (:user_id, :group_id, :message)');
    $stmt->bindValue(':user_id', $userId);
    $stmt->bindValue(':group_id', $groupId);
    $stmt->bindValue(':message', $message);
    $stmt->execute();

    $response->getBody()->write(json_encode(['success' => true]));
    return $response->withHeader('Content-Type', 'application/json');
});


if (php_sapi_name() != 'cli') {
    // Run the app in normal web server context
    $app->run();
} else {
    // Return the app when running in CLI context (for tests)
    return $app;
}
