<?php

namespace NovaFlow\Core;

use PDO;
use Exception;

/**
 * Advanced Model & CRUD Generator (Full Version)
 * Database থেকে tables পড়ে Model, Controller এবং API তৈরি করে।
 */
class ModelGenerator
{
    private $db;
    private $modelsPath = 'app/models/';
    private $controllersPath = 'app/controllers/';
    private $apisPath = 'app/controllers/api/';
    
    // Namespaces
    private $modelNamespace = ''; 
    
    private $pkToTableMap = [];

    public function __construct()
    {
        $this->db = Container::getInstance()->make(DatabaseInterface::class);
        $this->buildPkToTableMap();

        // Ensure directories exist
        foreach ([$this->modelsPath, $this->controllersPath, $this->apisPath] as $dir) {
            if (!is_dir(BASE_PATH . '/' . $dir)) {
                mkdir(BASE_PATH . '/' . $dir, 0755, true);
            }
        }
    }

    private function buildPkToTableMap()
    {
        $tables = $this->getAllTables();
        foreach ($tables as $table) {
            $pk = $this->getPrimaryKey($table);
            if ($pk) {
                $this->pkToTableMap[$pk] = $table;
            }
        }
    }

    public function getAllTables()
    {
        $stmt = $this->db->query("SHOW TABLES");
        $tables = [];
        while ($row = $stmt->fetch(PDO::FETCH_NUM)) {
            $tables[] = $row[0];
        }
        return $tables;
    }

    public function tableToModelName($tableName)
    {
        $name = str_replace('tbl_', '', $tableName);
        $name = rtrim($name, 's');
        return str_replace('_', '', ucwords($name, '_')) . 'Model';
    }

    public function getPrimaryKey($tableName)
    {
        $stmt = $this->db->query("SHOW KEYS FROM `{$tableName}` WHERE Key_name = 'PRIMARY'");
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row['Column_name'] ?? 'id';
    }

    public function generateModel($tableName)
    {
        $modelName = $this->tableToModelName($tableName);
        $columns = $this->getTableColumns($tableName);
        $primaryKey = $this->getPrimaryKey($tableName);
        
        $code = "<?php\n\n";
        $code .= "/**\n * {$modelName} Model\n * Auto-generated at " . date('Y-m-d H:i:s') . "\n */\n";
        $code .= "class {$modelName} extends Model\n{\n";
        $code .= "    protected string \$table = '{$tableName}';\n";
        $code .= "    protected string \$primaryKey = '{$primaryKey}';\n\n";

        // Allowed Fields
        $allowed = [];
        foreach ($columns as $col) {
            if ($col['Field'] !== $primaryKey && !in_array($col['Field'], ['created_at', 'updated_at', 'deleted_at'])) {
                $allowed[] = $col['Field'];
            }
        }
        $code .= "    protected array \$allowedFields = [\n        '" . implode("',\n        '", $allowed) . "'\n    ];\n\n";

        // Relationships (BelongsTo)
        foreach ($columns as $col) {
            if (strpos($col['Field'], '_id') !== false) {
                $relTableBase = str_replace('_id', '', $col['Field']);
                // Try to find matching table
                $potentialTable = 'tbl_' . $relTableBase . 's';
                $relModel = str_replace('_', '', ucwords($relTableBase, '_')) . 'Model';
                $methodName = $relTableBase;
                
                $code .= "    public function {$methodName}()\n    {\n";
                $code .= "        return \$this->belongsTo({$relModel}::class, '{$col['Field']}', 'id');\n";
                $code .= "    }\n\n";
            }
        }

        // Potential HasMany Relationships
        $allTables = $this->getAllTables();
        foreach ($allTables as $otherTable) {
            if ($otherTable === $tableName) continue;
            $otherCols = $this->getTableColumns($otherTable);
            foreach ($otherCols as $oc) {
                if ($oc['Field'] === $tableName . '_id' || $oc['Field'] === str_replace('tbl_', '', $tableName) . '_id') {
                    $childMethod = str_replace('tbl_', '', $otherTable);
                    $childModel = str_replace('_', '', ucwords(rtrim($childMethod, 's'), '_')) . 'Model';
                    $code .= "    public function {$childMethod}()\n    {\n";
                    $code .= "        return \$this->hasMany({$childModel}::class, '{$oc['Field']}', 'id');\n";
                    $code .= "    }\n\n";
                }
            }
        }

        $code .= "}\n";

        file_put_contents(BASE_PATH . '/' . $this->modelsPath . $modelName . '.php', $code);
        return $modelName;
    }

    public function generateReport()
    {
        $tables = $this->getAllTables();
        $report = [];
        foreach ($tables as $table) {
            $cols = $this->getTableColumns($table);
            $report[] = [
                'table' => $table,
                'model' => $this->tableToModelName($table),
                'primary_key' => $this->getPrimaryKey($table),
                'columns_count' => count($cols),
                'has_timestamps' => $this->hasField($cols, 'created_at'),
                'has_status' => $this->hasField($cols, 'status')
            ];
        }
        return $report;
    }

    public function getTableColumns($tableName)
    {
        $stmt = $this->db->query("SHOW COLUMNS FROM `{$tableName}`");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    private function hasField($columns, $name)
    {
        foreach ($columns as $c) if ($c['Field'] === $name) return true;
        return false;
    }
}
