<?php

namespace App\Service;

use Doctrine\DBAL\Exception;
use Symfony\Component\HttpFoundation\JsonResponse;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Tools\SchemaTool;
use Doctrine\ORM\Tools\Setup;
use Doctrine\ORM\Mapping\Driver\AnnotationDriver;
use Doctrine\ORM\Mapping\Driver\XmlDriver;
use Doctrine\ORM\Mapping\Driver\YamlDriver;

class ProductService
{
    private $mysqli;

    public function __construct()
    {
        $host = 'db';
        $username = $_ENV['MYSQL_USER'];
        $password = $_ENV['MYSQL_PASSWORD'];
        $database = $_ENV['MYSQL_DATABASE'];

        $this->mysqli = new \mysqli($host, $username, $password, $database);

        if ($this->mysqli->connect_error) {
            die("Connection failed: " . $this->mysqli->connect_error);
        }
    }

    public function getProducts() {
        try {
            $result = $this->mysqli->query('SELECT Product_name_short, EAN_Nummer, Wattage, Primary_Image_link FROM product_json');

            if ($result === false) {
                return 'Query failed: ' . $this->mysqli->error;
            }

            $products = $result->fetch_all(MYSQLI_ASSOC);

            $result->free();
            $this->mysqli->close();

            return $products;
        } catch (\Exception $e) {
            return  $e->getMessage();
        }
    }

    public function getProductByEan($ean) {
        $stmt = $this->mysqli->prepare("SELECT * FROM product_json WHERE EAN_Nummer = ?");
        if (!$stmt) {
            return 'Prepare failed: ' . $this->mysqli->error;
        }

        $stmt->bind_param('s', $ean);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result) {
            $product = $result->fetch_all(MYSQLI_ASSOC);
        } else {
            $product = 'No result.';
        }
        
        $stmt->close();
        return $product[0];
    }

    public function getComparedProducts($ean, $compared) {
        $stmt = $this->mysqli->prepare("SELECT * FROM product_json WHERE EAN_Nummer IN (?, ?)");
        if (!$stmt) {
            return 'Prepare failed: ' . $this->mysqli->error;
        }

        $stmt->bind_param('ss', $ean, $compared);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result) {
            $products = $result->fetch_all(MYSQLI_ASSOC);
        } else {
            $products = 'No result.';
        }

        $stmt->close();
        return $products;
    }

    public function createTableIfNotExists(array $products, string $tableName, string $primaryKey): string
    {
        if (empty($products)) {
            return 'No products provided';
        }

        $tableName = $this->sanitizeIdentifier($tableName);
        $sanitizedPrimaryKey = $this->sanitizeIdentifier($primaryKey);

        $allKeys = [];

        foreach ($products as $product) {
            $allKeys = array_merge($allKeys, array_keys($product));
        }

        $allKeys = array_unique($allKeys);
        $columns = [];

        foreach ($allKeys as $key) {
            $sanitizedKey = $this->sanitizeIdentifier($key);
            $type = 'VARCHAR(255)';
            foreach ($products as $product) {
                if (array_key_exists($key, $product)) {
                    $type = $this->getSqlType(gettype($product[$key]));
                    if($type == 'VARCHAR(255)' && strlen($product[$key]) > 50) {
                        $type = 'TEXT';
                    }
                    break;
                }
            }

            $columns[] = "$sanitizedKey $type";
        }
        if ($sanitizedPrimaryKey && in_array($primaryKey, array_keys($products[0]))) {
            $columns[] = "PRIMARY KEY ($sanitizedPrimaryKey)";
        }

        $columnsSql = implode(', ', $columns);

        $sql = "CREATE TABLE IF NOT EXISTS $tableName ($columnsSql)";

        if ($this->mysqli->query($sql) === TRUE) {
            return 'Table created successfully!';
        } else {
            return "Error: " . $this->mysqli->error;
        }
    }

    public function saveProducts(array $products, string $tableName, string $primaryKey): string
    {
        $tableName = $this->sanitizeIdentifier($tableName);
        
        foreach ($products as $product) {
            $columns = array_keys($product);
            $placeholders = array_fill(0, count($product), '?');
            $values = array_values($product);

            $columns = array_map([$this, 'sanitizeIdentifier'], $columns);
            $primaryKeySanitized = $this->sanitizeIdentifier($primaryKey);
            
            $checkSql = sprintf(
                'SELECT COUNT(*) FROM %s WHERE %s = ?',
                $tableName,
                $primaryKeySanitized
            );
            
            $primaryKeyValue = $values[array_search($primaryKey, array_keys($product))];
            $stmt = $this->mysqli->prepare($checkSql);
            if (!$stmt) {
                die("Prepare failed: " . $this->mysqli->error);
            }
            $stmt->bind_param("s", $primaryKeyValue);
            $stmt->execute();
            $result = $stmt->get_result();
            $exists = $result->fetch_row()[0];
            $stmt->close();

            if ($exists > 0) {
                $updateSql = sprintf(
                    'UPDATE %s SET %s WHERE %s = ?',
                    $tableName,
                    implode(', ', array_map(fn($col) => "$col = ?", $columns)),
                    $primaryKeySanitized
                );
                $this->executeStmnt($updateSql, $values);
            } else {
                $insertSql = sprintf(
                    'INSERT INTO %s (%s) VALUES (%s)',
                    $tableName,
                    implode(', ', $columns),
                    implode(', ', $placeholders)
                );
                $this->executeStmnt($insertSql, $values);
            }
        }
        return 'Products processed successfully';
    }

    private function executeStmnt($updateSql, $values) {
        $stmt = $this->mysqli->prepare($updateSql);
        if (!$stmt) {
            die("Prepare failed: " . $this->mysqli->error);
        }
        $types = str_repeat('s', count($values));
        $stmt->bind_param($types, ...$values);
        $stmt->execute();
        #$affectedRows = $stmt->affected_rows;
        $stmt->close();
        #if ($affectedRows === 0) {
        #    echo "No rows updated.";
        #}
    }

    private function getSqlType(string $phpType): string
    {
        switch ($phpType) {
            case 'integer':
                return 'INT';
            case 'double':
                return 'FLOAT';
            case 'boolean':
                return 'TINYINT(1)';
            default:
                return 'VARCHAR(255)';
        }
    }

    private function sanitizeIdentifier(string $identifier): string
    {
        $identifier = preg_replace('/[^a-zA-Z0-9_]/', '_', $identifier);
        return "`$identifier`";
    }


}