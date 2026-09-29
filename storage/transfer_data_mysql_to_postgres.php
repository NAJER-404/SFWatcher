<?php
// Transfer data from MySQL (crudexample) to PostgreSQL 18 (spectrawatch)

$mysqlHost = '127.0.0.1';
$mysqlPort = 3306;
$mysqlDb   = 'crudexample';
$mysqlUser = 'root';
$mysqlPass = '';

$pgHost = '127.0.0.1';
$pgPort = 5432;
$pgDb   = 'spectrawatch';
$pgUser = 'postgres';
$pgPass = 'alwayswin123';

try {
    $mysql = new PDO("mysql:host=$mysqlHost;port=$mysqlPort;dbname=$mysqlDb;charset=utf8mb4", $mysqlUser, $mysqlPass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);
    echo "[✓] Connected to MySQL: $mysqlDb\n";

    $pg = new PDO("pgsql:host=$pgHost;port=$pgPort;dbname=$pgDb", $pgUser, $pgPass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);
    echo "[✓] Connected to PostgreSQL: $pgDb\n\n";
} catch (Exception $e) {
    die("[✗] Connection error: " . $e->getMessage() . "\n");
}

// Get column metadata from PostgreSQL for type conversion (e.g. boolean)
$pgColumnsStmt = $pg->query("
    SELECT table_name, column_name, data_type, udt_name 
    FROM information_schema.columns 
    WHERE table_schema = 'public'
");
$pgColumnTypes = [];
while ($row = $pgColumnsStmt->fetch()) {
    $pgColumnTypes[$row['table_name']][$row['column_name']] = $row['data_type'];
}

// Ordered table list respecting foreign key dependencies
$tables = [
    'users',
    'barangays',
    'incidents',
    'ward_stations',
    'resources',
    'incident_evidence',
    'investigations',
    'responder_assignments',
    'equipment',
    'promotion_histories',
    'libraries',
    'students',
    'failed_jobs',
    'jobs',
    'job_batches',
    'cache',
    'cache_locks',
    'password_reset_tokens',
    'sessions',
    'migrations'
];

echo "Beginning table-by-table data migration...\n";
echo str_repeat('-', 70) . "\n";

$results = [];

foreach ($tables as $table) {
    // Check if table exists in both databases
    $mysqlCheck = $mysql->query("SHOW TABLES LIKE '$table'")->fetch();
    if (!$mysqlCheck) {
        echo "[!] Table '$table' does not exist in MySQL. Skipping.\n";
        continue;
    }

    // Read rows from MySQL
    $rows = $mysql->query("SELECT * FROM `$table`")->fetchAll();
    $mysqlCount = count($rows);

    // If migrations table, clear what migrate command inserted so we have exact batch matches from MySQL
    if ($table === 'migrations') {
        $pg->exec("TRUNCATE TABLE \"$table\"");
    }

    if ($mysqlCount === 0) {
        $pgCount = (int)$pg->query("SELECT COUNT(*) FROM \"$table\"")->fetchColumn();
        $results[$table] = [
            'mysql' => 0,
            'pg' => $pgCount,
            'status' => 'OK (empty)'
        ];
        echo sprintf("Table %-24s: %4d MySQL rows -> %4d PG rows [OK]\n", $table, 0, $pgCount);
        continue;
    }

    // Get column names from the first row
    $columns = array_keys($rows[0]);
    $quotedCols = array_map(fn($c) => "\"$c\"", $columns);
    $placeholders = array_fill(0, count($columns), '?');

    $sql = "INSERT INTO \"$table\" (" . implode(', ', $quotedCols) . ") VALUES (" . implode(', ', $placeholders) . ")";
    $insertStmt = $pg->prepare($sql);

    $pg->beginTransaction();
    try {
        foreach ($rows as $row) {
            $values = [];
            foreach ($columns as $col) {
                $val = $row[$col];
                $pgType = $pgColumnTypes[$table][$col] ?? null;

                // Handle boolean conversion if PG column is boolean
                if ($pgType === 'boolean' && $val !== null) {
                    $val = ($val == 1 || $val === true || $val === '1' || $val === 'true') ? 't' : 'f';
                }

                $values[] = $val;
            }
            $insertStmt->execute($values);
        }
        $pg->commit();
    } catch (Exception $e) {
        $pg->rollBack();
        echo "[✗] Error migrating table '$table': " . $e->getMessage() . "\n";
        $results[$table] = [
            'mysql' => $mysqlCount,
            'pg' => 0,
            'status' => 'ERROR: ' . $e->getMessage()
        ];
        continue;
    }

    $pgCount = (int)$pg->query("SELECT COUNT(*) FROM \"$table\"")->fetchColumn();
    $status = ($mysqlCount === $pgCount) ? 'MATCH' : 'MISMATCH';
    $results[$table] = [
        'mysql' => $mysqlCount,
        'pg' => $pgCount,
        'status' => $status
    ];

    echo sprintf("Table %-24s: %4d MySQL rows -> %4d PG rows [%s]\n", $table, $mysqlCount, $pgCount, $status);
}

echo str_repeat('-', 70) . "\n";
echo "Synchronizing PostgreSQL sequences...\n";

// Synchronize all sequences for tables with id column
$seqStmt = $pg->query("
    SELECT table_name, column_name, column_default 
    FROM information_schema.columns 
    WHERE table_schema = 'public' 
      AND (column_default LIKE 'nextval%' OR is_identity = 'YES')
");

while ($seqRow = $seqStmt->fetch()) {
    $t = $seqRow['table_name'];
    $c = $seqRow['column_name'];
    try {
        $maxVal = (int)$pg->query("SELECT COALESCE(MAX(\"$c\"), 0) FROM \"$t\"")->fetchColumn();
        $nextVal = max($maxVal, 1);
        $pg->exec("SELECT setval(pg_get_serial_sequence('$t', '$c'), $nextVal, " . ($maxVal > 0 ? "true" : "false") . ")");
        echo "  [✓] Sequence for \"$t\".\"$c\" synced to $nextVal\n";
    } catch (Exception $e) {
        echo "  [!] Note on sequence for \"$t\".\"$c\": " . $e->getMessage() . "\n";
    }
}

echo "\nMigration script completed!\n";
file_put_contents('storage/migration_results.json', json_encode($results, JSON_PRETTY_PRINT));
