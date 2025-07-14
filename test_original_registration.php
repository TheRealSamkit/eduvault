<?php
// Test registration with original database structure
require_once 'includes/db_connect.php';

echo "<h1>Original Registration Test</h1>";

// Test 1: Check database connection
echo "<h2>Test 1: Database Connection</h2>";
if ($mysqli->ping()) {
    echo "✓ Database connection successful<br>";
} else {
    echo "✗ Database connection failed<br>";
    exit;
}

// Test 2: Check users table structure
echo "<h2>Test 2: Users Table Structure</h2>";
$result = $mysqli->query("DESCRIBE users");
if ($result) {
    echo "<table border='1'>";
    echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th></tr>";
    while ($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $row['Field'] . "</td>";
        echo "<td>" . $row['Type'] . "</td>";
        echo "<td>" . $row['Null'] . "</td>";
        echo "<td>" . $row['Key'] . "</td>";
        echo "<td>" . $row['Default'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
}

// Test 3: Test simple user creation
echo "<h2>Test 3: User Creation Test</h2>";
$test_email = 'test_original_' . time() . '@example.com';
$test_name = 'Test Original User';
$test_password = password_hash('testpass123', PASSWORD_DEFAULT);

$stmt = $mysqli->prepare("INSERT INTO users (name, email, password) VALUES (?, ?, ?)");
if ($stmt) {
    $stmt->bind_param("sss", $test_name, $test_email, $test_password);
    $success = $stmt->execute();

    if ($success) {
        $user_id = $mysqli->insert_id;
        echo "✓ Test user created successfully with ID: $user_id<br>";

        // Verify the user was created
        $verify_stmt = $mysqli->prepare("SELECT * FROM users WHERE id = ?");
        $verify_stmt->bind_param("i", $user_id);
        $verify_stmt->execute();
        $result = $verify_stmt->get_result();
        $user = $result->fetch_assoc();

        if ($user) {
            echo "✓ User verification successful<br>";
            echo "  - ID: " . $user['id'] . "<br>";
            echo "  - Name: " . $user['name'] . "<br>";
            echo "  - Email: " . $user['email'] . "<br>";
            echo "  - Status: " . $user['status'] . "<br>";
            echo "  - Created: " . $user['created_at'] . "<br>";
        } else {
            echo "✗ User verification failed<br>";
        }

        // Clean up
        $mysqli->query("DELETE FROM users WHERE id = $user_id");
        echo "✓ Test user cleaned up<br>";

    } else {
        echo "✗ Test user creation failed: " . $stmt->error . "<br>";
    }
    $stmt->close();
} else {
    echo "✗ Failed to prepare user creation: " . $mysqli->error . "<br>";
}

// Test 4: Check existing users
echo "<h2>Test 4: Existing Users</h2>";
$result = $mysqli->query("SELECT COUNT(*) as count FROM users");
if ($result) {
    $row = $result->fetch_assoc();
    echo "✓ Total users in database: " . $row['count'] . "<br>";

    if ($row['count'] > 0) {
        $users_result = $mysqli->query("SELECT id, name, email, status FROM users LIMIT 5");
        echo "<h3>Sample Users:</h3>";
        echo "<table border='1'>";
        echo "<tr><th>ID</th><th>Name</th><th>Email</th><th>Status</th></tr>";
        while ($user = $users_result->fetch_assoc()) {
            echo "<tr>";
            echo "<td>" . $user['id'] . "</td>";
            echo "<td>" . htmlspecialchars($user['name']) . "</td>";
            echo "<td>" . htmlspecialchars($user['email']) . "</td>";
            echo "<td>" . $user['status'] . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
}

echo "<h2>Test Complete</h2>";
echo "<p>If all tests pass, the original registration should work correctly.</p>";
echo "<p><a href='auth/register.php'>Try Registration</a></p>";
echo "<p><a href='dashboard/dashboard.php'>Go to Dashboard</a></p>";
?>