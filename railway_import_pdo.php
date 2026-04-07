<?php
/**
 * Railway Database Importer (PDO)
 *
 * Why: some hosts don't enable mysqli/mysqli_report().
 * This importer uses PDO and works on Railway.
 *
 * Uses Railway env vars when present:
 *   MYSQLHOST, MYSQLUSER, MYSQLPASSWORD, MYSQLPORT, MYSQLDATABASE
 */

declare(strict_types=1);

error_reporting(E_ALL);
ini_set('display_errors', '1');

$host = getenv('MYSQLHOST') ?: 'localhost';
$user = getenv('MYSQLUSER') ?: 'root';
$pass = getenv('MYSQLPASSWORD') ?: '';
$port = (int)(getenv('MYSQLPORT') ?: 3306);
$db   = getenv('MYSQLDATABASE') ?: 'railway';

header('Content-Type: text/html; charset=utf-8');
echo "<h1>Railway Database Setup (PDO)</h1>";
echo "<pre>";
echo "Attempting connection to: {$user}@{$host}:{$port}/{$db}\n";

/**
 * Very simple SQL splitter:
 * - handles ; line endings
 * - ignores ; inside single/double/backtick strings
 * - strips -- and # comments
 *
 * Note: does NOT support stored procedures with custom DELIMITER.
 */
function split_sql_statements(string $sql): array
{
    // Normalize line endings
    $sql = str_replace(["\r\n", "\r"], "\n", $sql);

    $out = [];
    $buf = '';
    $len = strlen($sql);
    $inSingle = false;
    $inDouble = false;
    $inBacktick = false;

    for ($i = 0; $i < $len; $i++) {
        $ch = $sql[$i];
        $next = ($i + 1 < $len) ? $sql[$i + 1] : '';

        // Line comments (only when not in string)
        if (!$inSingle && !$inDouble && !$inBacktick) {
            // -- comment (requires space or line end after -- typically, but we keep it simple)
            if ($ch === '-' && $next === '-') {
                // consume until newline
                while ($i < $len && $sql[$i] !== "\n") $i++;
                $buf .= "\n";
                continue;
            }
            // # comment
            if ($ch === '#') {
                while ($i < $len && $sql[$i] !== "\n") $i++;
                $buf .= "\n";
                continue;
            }
        }

        // Track string state (handle escapes)
        if ($ch === "'" && !$inDouble && !$inBacktick) {
            // ignore escaped quotes like \'
            $escaped = ($i > 0 && $sql[$i - 1] === '\\');
            if (!$escaped) $inSingle = !$inSingle;
        } elseif ($ch === '"' && !$inSingle && !$inBacktick) {
            $escaped = ($i > 0 && $sql[$i - 1] === '\\');
            if (!$escaped) $inDouble = !$inDouble;
        } elseif ($ch === '`' && !$inSingle && !$inDouble) {
            $inBacktick = !$inBacktick;
        }

        // Statement terminator
        if ($ch === ';' && !$inSingle && !$inDouble && !$inBacktick) {
            $stmt = trim($buf);
            if ($stmt !== '') $out[] = $stmt;
            $buf = '';
            continue;
        }

        $buf .= $ch;
    }

    $tail = trim($buf);
    if ($tail !== '') $out[] = $tail;

    return $out;
}

try {
    $dsn = sprintf('mysql:host=%s;port=%d;dbname=%s;charset=utf8mb4', $host, $port, $db);
    $pdo = new PDO($dsn, $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);

    echo "<b>Connection Successful!</b>\n\n";

    $file = __DIR__ . '/sms_db.sql';
    if (!file_exists($file)) {
        throw new RuntimeException("Error: sms_db.sql missing in project root. Upload it to Railway with the app.");
    }

    echo "Reading sms_db.sql...\n";
    $sql = file_get_contents($file);
    if ($sql === false) {
        throw new RuntimeException("Error: could not read sms_db.sql");
    }

    echo "Splitting statements...\n";
    $stmts = split_sql_statements($sql);
    echo "Found " . count($stmts) . " statements.\n";

    echo "Importing...\n";
    $pdo->exec('SET FOREIGN_KEY_CHECKS=0');

    $count = 0;
    foreach ($stmts as $s) {
        // Skip empty
        if ($s === '') continue;
        $pdo->exec($s);
        $count++;
        if ($count % 100 === 0) {
            echo "  executed {$count}...\n";
            @ob_flush(); @flush();
        }
    }

    $pdo->exec('SET FOREIGN_KEY_CHECKS=1');
    echo "Successfully imported {$count} statements.\n";

    echo "\n<b>SETUP COMPLETE!</b> Please delete this file (railway_import_pdo.php) for security.\n";
} catch (Throwable $e) {
    echo "\n<b style='color:red'>DATABASE ERROR:</b>\n" . htmlspecialchars($e->getMessage(), ENT_QUOTES) . "\n";
    echo "\n\n<b>What to do next:</b>\n";
    echo "1) If it says 'Access denied', verify MYSQLUSER/MYSQLPASSWORD/MYSQLHOST/MYSQLPORT/MYSQLDATABASE on Railway.\n";
    echo "2) If it fails on a specific statement, your sql file may contain DELIMITER/procedures (this script doesn't support that).\n";
    echo "3) Fastest option: import using your local mysql client (see commands below).\n";
}

echo "</pre>";

