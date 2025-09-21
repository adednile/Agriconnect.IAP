<?php
// farmers.php: Farmer profile & listings CRUD
require_once 'config.php';
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'farmer') {
    header('Location: index.php?show=login');
    exit();
}
$farmer_id = $_SESSION['user_id'];
// Fetch farmer profile
$stmt = $mysqli->prepare('SELECT * FROM farmers WHERE farmer_id = ?');
$stmt->bind_param('i', $farmer_id);
$stmt->execute();
$result = $stmt->get_result();
$farmer = $result->fetch_assoc();
// Update profile
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $county = trim($_POST['county']);
    $language = $_POST['language'];
    $stmt = $mysqli->prepare('UPDATE farmers SET name=?, county=?, language=? WHERE farmer_id=?');
    $stmt->bind_param('sssi', $name, $county, $language, $farmer_id);
    $stmt->execute();
    header('Location: farmers.php?updated=1');
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Farmer Profile</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body>
<div class="container mt-4">
    <h2>My Profile</h2>
    <form method="post" class="mb-4">
        <div class="mb-2">
            <label>Name</label>
            <input type="text" name="name" class="form-control" value="<?php echo htmlspecialchars($farmer['name']); ?>" required>
        </div>
        <div class="mb-2">
            <label>County</label>
            <input type="text" name="county" class="form-control" value="<?php echo htmlspecialchars($farmer['county']); ?>">
        </div>
        <div class="mb-2">
            <label>Language</label>
            <select name="language" class="form-control">
                <option value="en" <?php if($farmer['language']==='en') echo 'selected'; ?>>English</option>
                <option value="sw" <?php if($farmer['language']==='sw') echo 'selected'; ?>>Kiswahili</option>
            </select>
        </div>
        <button type="submit" class="btn btn-primary">Update Profile</button>
    </form>
    <a href="dashboard.php">Back to Dashboard</a>
</div>
</body>
</html>