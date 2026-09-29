<?php
$data = json_decode(file_get_contents('storage/crudexample_schema_analysis.json'), true);
foreach ($data as $t => $info) {
    if (!empty($info['foreign_keys'])) {
        echo "Table: $t\n";
        foreach ($info['foreign_keys'] as $fk) {
            echo "  - {$fk['COLUMN_NAME']} -> {$fk['REFERENCED_TABLE_NAME']}.{$fk['REFERENCED_COLUMN_NAME']} (UPDATE {$fk['UPDATE_RULE']}, DELETE {$fk['DELETE_RULE']})\n";
        }
    }
}
