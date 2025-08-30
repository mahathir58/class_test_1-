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

// Get tags for forms
$tags = $conn->query("SELECT * FROM tags");

// Handle delete task
if (isset($_GET['delete'])) {
    $delete_id = intval($_GET['delete']);
    $conn->query("DELETE FROM tasks WHERE id=$delete_id");
    header("Location: todo.php");
    exit;
}

// Handle edit task
if (isset($_POST['edit_task'])) {
    $edit_id = intval($_POST['edit_id']);
    $edit_title = $_POST['edit_title'];
    $edit_tag_id = $_POST['edit_tag_id'];
    $stmt = $conn->prepare("UPDATE tasks SET title=?, tag_id=? WHERE id=?");
    $stmt->bind_param("sii", $edit_title, $edit_tag_id, $edit_id);
    $stmt->execute();
    $stmt->close();
    header("Location: todo.php");
    exit;
}

// Handle filter
$filter_tag = isset($_GET['filter_tag']) ? intval($_GET['filter_tag']) : '';
if ($filter_tag) {
    $tasks = $conn->query("SELECT tasks.*, tags.name AS tag_name FROM tasks LEFT JOIN tags ON tasks.tag_id = tags.id WHERE tasks.tag_id=$filter_tag ORDER BY tasks.created_at DESC");
} else {
    $tasks = $conn->query("SELECT tasks.*, tags.name AS tag_name FROM tasks LEFT JOIN tags ON tasks.tag_id = tags.id ORDER BY tasks.created_at DESC");
}
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
        <h2>Filter Tasks by Tag</h2>
        <form method="get">
            <select name="filter_tag">
                <option value="">All Tags</option>
                <?php 
                $tags_filter = $conn->query("SELECT * FROM tags");
                while($row = $tags_filter->fetch_assoc()): ?>
                    <option value="<?php echo $row['id']; ?>" <?php if(isset($_GET['filter_tag']) && $_GET['filter_tag']==$row['id']) echo 'selected'; ?>><?php echo $row['name']; ?></option>
                <?php endwhile; ?>
            </select>
            <button type="submit">Filter</button>
        </form>
        <h2>Tasks</h2>
        <ul>
            <?php 
            // For edit form
            $edit_mode = isset($_GET['edit']) ? intval($_GET['edit']) : 0;
            while($row = $tasks->fetch_assoc()): ?>
                <li>
                    <?php if ($edit_mode && $edit_mode == $row['id']): ?>
                        <form method="post" style="display:inline;">
                            <input type="hidden" name="edit_id" value="<?php echo $row['id']; ?>">
                            <input type="text" name="edit_title" value="<?php echo htmlspecialchars($row['title']); ?>" required>
                            <select name="edit_tag_id">
                                <option value="">No Tag</option>
                                <?php 
                                $tags_edit = $conn->query("SELECT * FROM tags");
                                while($tag = $tags_edit->fetch_assoc()): ?>
                                    <option value="<?php echo $tag['id']; ?>" <?php if($row['tag_id']==$tag['id']) echo 'selected'; ?>><?php echo $tag['name']; ?></option>
                                <?php endwhile; ?>
                            </select>
                            <button type="submit" name="edit_task">Save</button>
                            <a href="todo.php">Cancel</a>
                        </form>
                    <?php else: ?>
                        <?php echo htmlspecialchars($row['title']); ?>
                        <?php if ($row['tag_name']) echo " (" . htmlspecialchars($row['tag_name']) . ")"; ?>
                        <a href="?edit=<?php echo $row['id']; ?>">Edit</a>
                        <a href="?delete=<?php echo $row['id']; ?>" onclick="return confirm('Delete this task?');">Delete</a>
                    <?php endif; ?>
                </li>
            <?php endwhile; ?>
        </ul>
    </div>
</body>
</html>
<?php $conn->close(); ?>
