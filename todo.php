<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "todo_app";

$conn = new mysqli($servername, $username, $password);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
$sql = "CREATE DATABASE IF NOT EXISTS $dbname";
$conn->query($sql);
$conn->select_db($dbname);

$sql_tasks = "CREATE TABLE IF NOT EXISTS tasks (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    tag_id INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";
$conn->query($sql_tasks);

$sql_tags = "CREATE TABLE IF NOT EXISTS tags (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL
)";
$conn->query($sql_tags);

if (isset($_POST['add_tag'])) {
    $tag_name = $_POST['tag_name'];
    $stmt = $conn->prepare("INSERT INTO tags (name) VALUES (?)");
    $stmt->bind_param("s", $tag_name);
    $stmt->execute();
    $stmt->close();
}

if (isset($_POST['add_task'])) {
    $title = $_POST['title'];
    $tag_id = $_POST['tag_id'];
    $stmt = $conn->prepare("INSERT INTO tasks (title, tag_id) VALUES (?, ?)");
    $stmt->bind_param("si", $title, $tag_id);
    $stmt->execute();
    $stmt->close();
}

$tags = $conn->query("SELECT * FROM tags");

$tasks = $conn->query("SELECT tasks.*, tags.name AS tag_name FROM tasks LEFT JOIN tags ON tasks.tag_id = tags.id ORDER BY tasks.created_at DESC");
?>
<!DOCTYPE html>
<html>
<head>
    <title>Simple To-Do List</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f4f4f4; margin: 0; padding: 0; }
        .container { max-width: 500px; margin: 40px auto; background: #fff; padding: 20px 30px; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); }
        h2 { color: #333; margin-top: 0; }
        form { margin-bottom: 20px; }
        input[type="text"], select { padding: 8px; margin-right: 8px; border: 1px solid #ccc; border-radius: 4px; }
        button { padding: 8px 16px; background: #007bff; color: #fff; border: none; border-radius: 4px; cursor: pointer; }
        button:hover { background: #0056b3; }
        ul { list-style: none; padding: 0; }
        li { background: #e9ecef; margin-bottom: 8px; padding: 10px; border-radius: 4px; }
    </style>
</head>
<body>
    <div class="container">
        <h2>Add Tag</h2>
        <form method="post">
            <input type="text" name="tag_name" placeholder="Tag name" required>
            <button type="submit" name="add_tag">Add Tag</button>
        </form>
        <h2>Add Task</h2>
        <form method="post">
            <input type="text" name="title" placeholder="Task title" required>
            <select name="tag_id">
                <option value="">No Tag</option>
                <?php while($row = $tags->fetch_assoc()): ?>
                    <option value="<?php echo $row['id']; ?>"><?php echo $row['name']; ?></option>
                <?php endwhile; ?>
            </select>
            <button type="submit" name="add_task">Add Task</button>
        </form>
        <h2>Tasks</h2>
        <ul>
            <?php while($row = $tasks->fetch_assoc()): ?>
                <li>
                    <?php echo htmlspecialchars($row['title']); ?>
                    <?php if ($row['tag_name']) echo " (" . htmlspecialchars($row['tag_name']) . ")"; ?>
                </li>
            <?php endwhile; ?>
        </ul>
    </div>
</body>
</html>
<?php $conn->close(); ?>
