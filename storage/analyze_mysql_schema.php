<?php
$mysqli = new mysqli('127.0.0.1', 'root', '', 'crudexample', 3306);
if ($mysqli->connect_error) {
    die("Connect Error: " . $mysqli->connect_error);
}

$tablesRes = $mysqli->query("SHOW FULL TABLES WHERE Table_type = 'BASE TABLE'");
$tables = [];
while ($row = $tablesRes->fetch_array()) {
    $tables[] = $row[0];
}

$schema = [];
foreach ($tables as $table) {
    // Row count
    $cntRes = $mysqli->query("SELECT COUNT(*) FROM `$table`");
    $cnt = $cntRes->fetch_row()[0];

    // Columns
    $colRes = $mysqli->query("SHOW FULL COLUMNS FROM `$table`");
    $cols = [];
    while ($c = $colRes->fetch_assoc()) {
        $cols[] = $c;
    }

    // Indexes
    $idxRes = $mysqli->query("SHOW INDEX FROM `$table`");
    $indexes = [];
    while ($idx = $idxRes->fetch_assoc()) {
        $indexes[] = $idx;
    }

    // Foreign keys from information_schema
    $fkRes = $mysqli->query("SELECT 
        k.COLUMN_NAME, 
        k.REFERENCED_TABLE_NAME, 
        k.REFERENCED_COLUMN_NAME,
        r.UPDATE_RULE,
        r.DELETE_RULE
    FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE k
    JOIN INFORMATION_SCHEMA.REFERENTIAL_CONSTRAINTS r
        ON k.CONSTRAINT_NAME = r.CONSTRAINT_NAME
        AND k.CONSTRAINT_SCHEMA = r.CONSTRAINT_SCHEMA
    WHERE k.TABLE_SCHEMA = 'crudexample'
        AND k.TABLE_NAME = '$table'
        AND k.REFERENCED_TABLE_NAME IS NOT NULL");
    
    $fks = [];
    while ($fk = $fkRes->fetch_assoc()) {
        $fks[] = $fk;
    }

    $schema[$table] = [
        'count' => $cnt,
        'columns' => $cols,
        'indexes' => $indexes,
        'foreign_keys' => $fks
    ];
}

file_put_contents('storage/crudexample_schema_analysis.json', json_encode($schema, JSON_PRETTY_PRINT));
echo "Schema analysis saved to storage/crudexample_schema_analysis.json\n";
echo "Total Tables: " . count($tables) . "\n";
foreach ($schema as $t => $info) {
    echo "- $t: {$info['count']} rows, " . count($info['columns']) . " columns, " . count($info['foreign_keys']) . " foreign keys\n";
}
