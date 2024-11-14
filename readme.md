# Zotec Framework Documentation

Welcome to the Zotec Framework! This documentation covers the essential components used in the framework, including **Cycle ORM**, **Twig**, **Intervention Image**, **Tracy Debug Bar**, and configuration details for setting up **database** and **routing** in your project. The framework is designed to streamline your development process with a clean and efficient structure.

## Key Components

1. **Cycle ORM** – A powerful object-relational mapper (ORM) for handling database operations.
2. **Twig** – A flexible and fast template engine for PHP, used for rendering views.
3. **Intervention Image** – A simple image handling and manipulation library.
4. **Tracy Debug Bar** – A powerful debugger for PHP to help with error tracking and debugging.
5. **Routing System** – Handles routing for your application, making URL management simple.

---

## Installation

1. Clone or download the repository.
2. Run `composer install` to install dependencies.
3. Configure your `.env` file with the correct settings for the database, routing, and other services.

---

## Configuration: `config/env.php`

All configuration settings related to your database, routing system, and other components are defined in the `config/env.php` file. Below is an example of how to configure these services:

```php
return [
    'database' => [
        'driver'   => 'mysql',
        'host'     => 'localhost',
        'dbname'   => 'your_database',
        'username' => 'root',
        'password' => '',
        'charset'  => 'utf8mb4',
    ],
    'routing' => [
        'base_url' => 'http://localhost',
        'routes' => [
            '/home' => 'HomeController@index',
            '/about' => 'AboutController@index',
            // Add more routes here
        ],
    ],
    'twig' => [
        'cache' => '/path/to/cache',
        'debug' => true,
    ],
    'intervention_image' => [
        'driver' => 'gd', // Or 'imagick'
    ],
    'tracy_debug_bar' => [
        'enabled' => true,
    ],
];
```

---

## **Cycle ORM** Usage

Cycle ORM is an advanced object-relational mapper (ORM) used to handle database interactions in your project. Here's how you can perform various database operations.

### **Database Insert Builder**

To insert data into a table, we use the `InsertBuilder`:

```php
// Get InsertBuilder instance
$insert = $db->insert('test');

// Insert a single row
$insert->values([
    'time_created' => new \DateTime(),
    'name'         => 'Anton',
    'email'        => 'test@email.com',
    'balance'      => 800.90
]);
```

### **Batch Insert**

To insert multiple rows efficiently, use a batch insert:

```php
$insert->columns([
    'time_created',
    'name',
    'email',
    'balance'
]);

for ($i = 0; $i < 20; $i++) {
    // Add multiple values
    $insert->values([
        new \DateTime(),
        $this->faker->randomNumber(2),
        $this->faker->email,
        $this->faker->randomFloat(2)
    ]);
}

// Execute the insert operation
$insert->run();
```

### **Quick Inserts**

You can skip the `InsertQuery` creation by directly interacting with the table:

```php
$table = $db->table('test');

print_r($table->insertOne([
    'time_created' => new \DateTime(),
    'name'         => 'Anton',
    'email'        => 'test@email.com',
    'balance'      => 800.90
]));
```

### **Select Query Builder**

Cycle ORM’s `SelectQuery` builder allows you to fetch data from the database efficiently.

```php
// Select from 'test' table
$select = $db->table('test')->select();

// Fetch specific columns
$select->columns(['id', 'status', 'name']);
foreach ($select as $row) {
    print_r($row);
}
```

### **Where Statements**

You can add `WHERE` conditions to your queries:

```php
$select = $db->select()
    ->from('test')
    ->columns(['id', 'status', 'name'])
    ->where('status', '=', 'active');

foreach ($select as $row) {
    print_r($row);
}
```

You can also chain multiple conditions:

```php
$select->where('id', 1)
    ->andWhere('status', 'active');
```

### **Debugging Queries**

To debug the generated SQL statement, use:

```php
print_r($db->users->select()->columns('name')->sqlStatement());
```

---

## **Intervention Image** – Image Manipulation

The **Intervention Image** library allows you to easily manipulate images in your application.

### **Basic Example**

```php
// Import the Intervention Image Manager Class
use Intervention\Image\ImageManager;

// Create an ImageManager instance with the favored driver
$manager = new ImageManager(['driver' => 'imagick']);

// Create an image instance and resize it
$image = $manager->make('public/foo.jpg')->resize(300, 200);
$image->save('public/foo_resized.jpg');
```

---

## **Tracy Debug Bar** – Debugging Tool

Tracy is a powerful PHP debugger that provides real-time insights into your application. It includes a Debug Bar for viewing logs, exceptions, and more.

### **Enable Tracy Debug Bar**

To use the Tracy Debug Bar, ensure it is enabled in the configuration:

```php
// Example configuration in env.php
'tracy_debug_bar' => [
    'enabled' => true,
]
```

In your application, you can trigger Tracy’s debugging features:

```php
// Enable Tracy Debugging
if ($config['tracy_debug_bar']['enabled']) {
    \Tracy\Debugger::enable();
}
```

Once enabled, the Tracy Debug Bar will appear at the bottom of the page, providing real-time debugging information.

---

## **Twig** – Template Engine

Twig is the templating engine used in this framework for rendering views. It is highly flexible and fast, making it ideal for building dynamic web applications.

### **Using Twig**

Here’s how you can render a template with Twig:

```php
// Load Twig environment
$loader = new \Twig\Loader\FilesystemLoader('/path/to/templates');
$twig = new \Twig\Environment($loader);

// Render a template
echo $twig->render('index.twig', ['name' => 'Anton']);
```

### **Template Example**

In the `index.twig` file:

```twig
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ name }}'s Page</title>
</head>
<body>
    <h1>Hello, {{ name }}!</h1>
</body>
</html>
```

---
