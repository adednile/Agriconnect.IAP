<?php
/**
 * Backwards-compatible DB connection helper for legacy scripts and phpMyAdmin users.
 *
 * Exposes:
 *  - $conn: mysqli connection (or null on failure)
 *  - $pdo: PDO connection (or null on failure)
 *
 * Provides helper getters:
 *  - get_mysqli(): returns mysqli or null
 *  - get_pdo(): returns PDO or null
 *
 * Reads DB_HOST, DB_USER, DB_PASS, DB_NAME from project .env (one-per-line KEY=VALUE)
 * If .env missing, falls back to sensible local defaults.
 */

// Helper to strip optional quotes
function _strip_quotes($s) {
    $s = trim($s);
    if ((substr($s,0,1) === '"' && substr($s,-1) === '"') || (substr($s,0,1) === "'" && substr($s,-1) === "'")) {
        return substr($s,1,-1);
    }
    return $s;
}

$envPath = __DIR__ . '/../.env';
$host = getenv('DB_HOST') ?: 'localhost';
$user = getenv('DB_USER') ?: 'root';
$pass = getenv('DB_PASS') ?: '';
$dbname = getenv('DB_NAME') ?: '';

if (file_exists($envPath) && is_readable($envPath)) {
    $lines = file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        $line = trim($line);
        if ($line === '' || strpos($line, '#') === 0) continue;
        // support export KEY=VAL or KEY=VAL
        if (stripos($line, 'export ') === 0) $line = trim(substr($line,7));
        if (strpos($line, '=') === false) continue;
        list($k, $v) = explode('=', $line, 2);
        $k = trim($k);
        $v = _strip_quotes(trim($v));
        switch ($k) {
            case 'DB_HOST': $host = $v; break;
            case 'DB_USER': $user = $v; break;
            case 'DB_PASS': $pass = $v; break;
            case 'DB_NAME': $dbname = $v; break;
        }
    }
}

// sensible fallback if DB_NAME is still empty (helps local phpMyAdmin setups)
if (empty($dbname)) {
    $dbname = 'agri_marketplace';
}

// Expose these to legacy code that expects globals
$conn = null;
$pdo = null;

// Create mysqli connection (legacy scripts and phpMyAdmin compatibility)
mysqli_report(MYSQLI_REPORT_OFF);
try {
    $conn = @new mysqli($host, $user, $pass, $dbname);
    if ($conn && !$conn->connect_error) {
        // set charset to utf8mb4 for emoji/emoji-safe text
        @$conn->set_charset('utf8mb4');
    } else {
        error_log('MySQLi connection failed: ' . ($conn ? $conn->connect_error : 'unknown'));
        $conn = null; // keep execution going for admins/scripts that may handle null
    }
} catch (Throwable $e) {
    error_log('MySQLi exception: ' . $e->getMessage());
    $conn = null;
}

// Create PDO connection for modern code
try {
    $dsn = "mysql:host={$host};dbname={$dbname};charset=utf8mb4";
    $pdo = new PDO($dsn, $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);
} catch (Throwable $e) {
    error_log('PDO connection failed: ' . $e->getMessage());
    $pdo = null;
}

// Helper getters for code that prefers functions
function get_mysqli()
{
    global $conn;
    return $conn;
}

function get_pdo()
{
    global $pdo;
    return $pdo;
}

function db_info()
{
    global $host, $user, $dbname;
    return [
        'host' => $host,
        'user' => $user,
        'db' => $dbname,
    ];
}

// Note: We deliberately avoid terminating execution here. Scripts should check
// whether $conn or $pdo are null and handle errors gracefully.

?>

<?php
// Back-compat shim: if mysqli ($conn) is not available but PDO ($pdo) is, provide
// a lightweight object with prepare() and query() methods that map to PDO.
if (empty($conn) && !empty($pdo)) {
    class ConnShim {
        private $pdo;
        public function __construct($pdo) { $this->pdo = $pdo; }
        public function prepare($sql) {
            $stmt = $this->pdo->prepare($sql);
            return $stmt;
        }
        public function query($sql) {
            $stmt = $this->pdo->query($sql);
            return $stmt;
        }
        // minimal execute wrapper for direct calls
        public function executeQuery($sql, $params = []) {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            return $stmt;
        }
    }

    $conn = new ConnShim($pdo);
}

?>
