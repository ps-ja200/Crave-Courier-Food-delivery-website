<?php
// Database checker script
include 'db_connect.php';

echo "<h2>🔍 Database Status Check</h2>";

// Check if database connection works
if ($conn->connect_error) {
    echo "<p style='color: red;'>❌ Database connection failed: " . $conn->connect_error . "</p>";
    exit;
} else {
    echo "<p style='color: green;'>✅ Database connection successful!</p>";
}

// Check what tables exist
echo "<h3>📋 Existing Tables:</h3>";
$result = $conn->query("SHOW TABLES");
if ($result->num_rows > 0) {
    echo "<ul>";
    while($row = $result->fetch_array()) {
        echo "<li>" . $row[0] . "</li>";
    }
    echo "</ul>";
} else {
    echo "<p>❌ No tables found in database 'crave_courier'</p>";
}

// Check if users table exists and show structure
$tables_to_check = ['users', 'categories', 'menu_items', 'cart_items', 'orders'];

foreach ($tables_to_check as $table) {
    echo "<h3>📊 Table: $table</h3>";
    
    // Check if table exists
    $check_table = $conn->query("SHOW TABLES LIKE '$table'");
    if ($check_table->num_rows > 0) {
        echo "<p style='color: green;'>✅ Table exists</p>";
        
        // Show table structure
        $structure = $conn->query("DESCRIBE $table");
        if ($structure->num_rows > 0) {
            echo "<table border='1' cellpadding='5' cellspacing='0' style='border-collapse: collapse;'>";
            echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th></tr>";
            while($row = $structure->fetch_assoc()) {
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
        
        // Show row count
        $count_result = $conn->query("SELECT COUNT(*) as count FROM $table");
        $count = $count_result->fetch_assoc()['count'];
        echo "<p>📈 Records: $count</p>";
        
        // Show sample data
        if ($count > 0) {
            echo "<p><strong>Sample data:</strong></p>";
            $sample = $conn->query("SELECT * FROM $table LIMIT 3");
            if ($sample->num_rows > 0) {
                echo "<table border='1' cellpadding='5' cellspacing='0' style='border-collapse: collapse;'>";
                // Get column names
                $fields = $sample->fetch_fields();
                echo "<tr>";
                foreach ($fields as $field) {
                    echo "<th>" . $field->name . "</th>";
                }
                echo "</tr>";
                
                // Reset pointer and show data
                $sample = $conn->query("SELECT * FROM $table LIMIT 3");
                while($row = $sample->fetch_assoc()) {
                    echo "<tr>";
                    foreach ($row as $value) {
                        echo "<td>" . htmlspecialchars($value) . "</td>";
                    }
                    echo "</tr>";
                }
                echo "</table>";
            }
        }
    } else {
        echo "<p style='color: red;'>❌ Table does not exist</p>";
    }
    echo "<hr>";
}

$conn->close();
?>

<style>
body { font-family: Arial, sans-serif; margin: 20px; }
table { margin: 10px 0; }
th { background-color: #f2f2f2; }
hr { margin: 20px 0; }
</style>

<h3>🚀 What to do next:</h3>
<ol>
<li>If you see missing tables, run the schema.sql file to create them</li>
<li>If tables exist but are empty, we'll populate them with sample data</li>
<li>Once verified, we'll implement the shopping cart functionality</li>
</ol>

<h3>📝 How to run the schema.sql file:</h3>
<p><strong>Method 1 - phpMyAdmin:</strong></p>
<ol>
<li>Open http://localhost/phpmyadmin in your browser</li>
<li>Click on your 'crave_courier' database</li>
<li>Go to 'Import' tab</li>
<li>Choose your schema.sql file</li>
<li>Click 'Go'</li>
</ol>

<p><strong>Method 2 - Command Line:</strong></p>
<code>mysql -u root -p crave_courier < "C:\Users\prave\Food-delivery-website\database\schema.sql"</code>
