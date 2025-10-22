<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Farmer Registration</title>
    <link rel="stylesheet" href="/public/css/style.css">
</head>
<body>
    <h2>Farmer Registration</h2>
    <form action="/routes/web.php?action=register_farmer" method="POST">
        <input type="text" name="name" placeholder="Full name" required><br>
        <input type="email" name="email" placeholder="Email address" required><br>
        <input type="text" name="phone" placeholder="Phone number" required><br>
        <input type="text" name="location" placeholder="Location" required><br>
        <input type="password" name="password" placeholder="Password" required><br>
        <button type="submit">Register</button>
    </form>
</body>
</html>
