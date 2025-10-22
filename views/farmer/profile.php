<!DOCTYPE html>
<html>
<head>
    <title>Farmer Profile</title>
    <link rel="stylesheet" href="../../public/assets/css/style.css">
</head>
<body>
    <div class="container">
        <h2>Farmer Profile</h2>

        <form method="POST" action="../../routes/farmer_router.php">
            <input type="hidden" name="id" value="<?= htmlspecialchars($farmer['id']) ?>">

            <label>Name</label>
            <input type="text" name="name" value="<?= htmlspecialchars($farmer['name']) ?>">

            <label>Email</label>
            <input type="email" name="email" value="<?= htmlspecialchars($farmer['email']) ?>">

            <label>Phone</label>
            <input type="text" name="phone" value="<?= htmlspecialchars($farmer['phone']) ?>">

            <label>Location</label>
            <input type="text" name="location" value="<?= htmlspecialchars($farmer['location']) ?>">

            <button type="submit">Update Profile</button>
        </form>

        <a href="dashboard.php">⬅ Back to Dashboard</a>
    </div>
</body>
</html>
