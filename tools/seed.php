<?php
// tools/seed.php
require_once __DIR__ . '/../vendor/autoload.php';
use App\Config\Database;

$envPath = __DIR__ . '/../.env';
if (!file_exists($envPath)) {
    echo ".env not found in project root. Please copy .env.example to .env and edit DB credentials.\n";
    exit(1);
}

// load env
$lines = file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
foreach ($lines as $line) {
    if (strpos(trim($line), '#') === 0) continue;
    if (strpos($line, '=') === false) continue;
    list($k,$v) = explode('=', $line, 2);
    putenv(trim($k) . '=' . trim($v));
}

$db = new Database();
$pdo = $db->connect();

try {
    // Quick check: ensure essential tables exist; if not, run schema.sql to create them.
    $tablesOk = false;
    try {
        $check = $pdo->query("SHOW TABLES LIKE 'wallets'");
        if ($check && $check->rowCount() > 0) $tablesOk = true;
    } catch (Exception $e) {
        $tablesOk = false;
    }

    if (!$tablesOk) {
        echo "Essential tables missing. Attempting to apply schema.sql...\n";
        $cmd = escapeshellcmd(PHP_BINARY) . " " . escapeshellarg(__DIR__ . DIRECTORY_SEPARATOR . 'run_sql.php') . " " . escapeshellarg(__DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'schema.sql');
        // fallback: try using project helper
        $runner = __DIR__ . DIRECTORY_SEPARATOR . 'run_sql.php';
        if (file_exists($runner)) {
            // run helper via php
            $out = []; $rc = 0;
            exec(escapeshellarg(PHP_BINARY) . " " . escapeshellarg(__DIR__ . DIRECTORY_SEPARATOR . 'run_sql.php') . " " . escapeshellarg(__DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'schema.sql') . " 2>&1", $out, $rc);
            echo implode("\n", $out) . "\n";
            if ($rc !== 0) {
                echo "Failed to apply schema automatically (rc=$rc). Please run php tools/run_sql.php schema.sql manually.\n";
                exit(1);
            }
            // reconnect after schema applied
            $pdo = $db->connect();
        } else {
            echo "Schema runner not found. Please run tools/run_sql.php schema.sql manually.\n";
            exit(1);
        }
    }

    // Ensure compatibility with older schemas: add 'phone' column if missing
    $dbName = getenv('DB_NAME') ?: null;
    if ($dbName) {
        $colStmt = $pdo->prepare("SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = ? AND TABLE_NAME = 'users' AND COLUMN_NAME = 'phone'");
        $colStmt->execute([$dbName]);
        $hasPhone = (bool)$colStmt->fetch();
        if (!$hasPhone) {
            try {
                $pdo->exec("ALTER TABLE users ADD COLUMN phone VARCHAR(32) DEFAULT NULL AFTER password");
                echo "Added missing column 'phone' to users table.\n";
            } catch (Exception $e) {
                echo "Warning: failed to add phone column: " . $e->getMessage() . "\n";
            }
        }
    }

    // create admin
    $adminEmail = getenv('TEST_ADMIN_EMAIL') ?: 'admin@example.com';
    $adminPass = getenv('TEST_ADMIN_PASS') ?: 'password';
    $stmt = $pdo->prepare('SELECT id FROM users WHERE email = ?');
    $stmt->execute([$adminEmail]);
    $exists = $stmt->fetch();
    if (!$exists) {
        $hash = password_hash($adminPass, PASSWORD_BCRYPT);
        $stmt = $pdo->prepare('INSERT INTO users (name,email,password,phone,role,is_verified,created_at) VALUES (?,?,?,?,?,1,NOW())');
        $stmt->execute(['Admin',$adminEmail,$hash,'0700000000','admin']);
        echo "Inserted admin: $adminEmail\n";
    } else {
        echo "Admin already exists: $adminEmail\n";
    }

    // create farmer
    $farmerEmail = getenv('TEST_FARMER_EMAIL') ?: 'farmer@example.com';
    $farmerPass = getenv('TEST_FARMER_PASS') ?: 'password';
    $stmt = $pdo->prepare('SELECT id FROM users WHERE email = ?');
    $stmt->execute([$farmerEmail]);
    $exists = $stmt->fetch();
    if (!$exists) {
        $hash = password_hash($farmerPass, PASSWORD_BCRYPT);
        $stmt = $pdo->prepare('INSERT INTO users (name,email,password,phone,role,is_verified,created_at) VALUES (?,?,?,?,?,1,NOW())');
        $stmt->execute(['Test Farmer',$farmerEmail,$hash,getenv('TEST_PHONE') ?: '0712345678','farmer']);
        $farmerId = $pdo->lastInsertId();
        echo "Inserted farmer: $farmerEmail (id=$farmerId)\n";

        // create wallet
        $wstmt = $pdo->prepare('INSERT INTO wallets (user_id, farmer_id, balance, created_at) VALUES (?,?,0.00,NOW())');
        $wstmt->execute([$farmerId, $farmerId]);
        echo "Created wallet for farmer id=$farmerId\n";

        // create sample product
        $pstmt = $pdo->prepare('INSERT INTO products (farmer_id, name, description, price, quantity, created_at) VALUES (?,?,?,?,?,NOW())');
        $pstmt->execute([$farmerId, 'Sample Maize', 'Locally grown maize', 1200.00, 10]);
        echo "Created sample product for farmer id=$farmerId\n";
    } else {
        echo "Farmer already exists: $farmerEmail\n";
    }

    echo "Seeding completed.\n";
} catch (Exception $e) {
    echo "Error during seed: " . $e->getMessage() . "\n";
    exit(1);
}

