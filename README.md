## **Chat Application Backend - PHP & Slim Framework**

This project is a simple chat application backend built with PHP and the Slim framework. Users can create chat groups, join groups, send messages within them, and list messages. All data is stored in an SQLite database, and communication is done via a RESTful JSON API over HTTP.

---

### **Technologies Used:**

- **PHP** (8.0+)
- **Slim Framework** (v4)
- **SQLite** (Database)

---

### **Available Endpoints:**

| HTTP Method | Endpoint                           | Description                        |
|-------------|------------------------------------|------------------------------------|
| **GET**     | `/users`                           | Get all users                      |
| **POST**    | `/users`                           | Create a new user                  |
| **GET**     | `/users/{id}`                      | Get a specific user by ID          |
| **GET**     | `/groups`                          | Get all groups                     |
| **POST**    | `/groups`                          | Create a new group                 |
| **POST**    | `/groups/{groupId}/join`           | Join a group                       |
| **GET**     | `/groups/{groupId}/messages`       | Get all messages in a group        |
| **POST**    | `/groups/{groupId}/messages`       | Send a message to a group          |

---

### **Command to Run the Server:**

To start the Slim application using PHP’s built-in server, run the following command:

```bash
php -S localhost:8000
```
You can even run the application using Docker with the following command:

```bash
docker-compose up
```

Here’s how you can write the **Testing** section of your README file, based on the provided test files and steps for running them:

---

### **Testing**

This project includes automated tests for the core features of the application using PHPUnit. The following features are covered by the tests:

#### **Tests Overview**
1. **User Management**
   - `GET /users`: Retrieves a list of all users.
   - `POST /users`: Creates a new user.
   - `GET /users/{id}`: Retrieves a user by their ID.

2. **Group Management**
   - `GET /groups`: Retrieves a list of all groups.
   - `POST /groups`: Creates a new group.
   - `GET /groups/{groupId}/messages`: Retrieves all messages in a specific group.

3. **Messages in Groups**
   - `POST /groups/{groupId}/messages`: Sends a message to a specific group.
   - `GET /groups/{groupId}/messages`: Retrieves all messages in a specific group.

#### **How to Run the Tests**

##### **Prerequisites**
- Make sure that PHPUnit is installed in your project. If it's not installed, you can install it with the following command:
   ```bash
   composer require --dev phpunit/phpunit
   ```

##### **Running the Tests**
To run the tests, execute the following command from the root directory of the project:
```bash
./vendor/bin/phpunit
```

Here is your updated README with the necessary changes based on the provided test classes:

---

### **Running the Tests**

To run the tests, execute the following command from the root directory of the project:

```bash
./vendor/bin/phpunit
```

### **What is Tested:**

- **UserTest.php**
  - `testGetUsers()`: Ensures that the `/users` endpoint returns a list of users with a `200 OK` status.
  - `testGetUserById()`: Tests that a specific user can be fetched by ID after being created, and then ensures the user is deleted from the database after the test.

- **GroupTest.php**
  - `testGetGroups()`: Ensures that the `/groups` endpoint returns a list of groups with a `200 OK` status.
  - `testCreateGroup()`: Tests the ability to create a new group via the `/groups` endpoint, and ensures the group is deleted from the database after the test.
  - `testGetGroupMessages()`: Ensures that the `/groups/{groupId}/messages` endpoint retrieves all messages for a specific group, and the group is deleted from the database after the test.

- **MessageTest.php**
  - `testSendMessageToGroup()`: Tests the ability to send a message to a group via the `/groups/{groupId}/messages` endpoint. After the test, the sent message is deleted from the database.
  - `testGetMessagesInGroup()`: Ensures that messages sent to a group can be retrieved correctly, and the test cleans up by deleting the retrieved message from the database after the test.

