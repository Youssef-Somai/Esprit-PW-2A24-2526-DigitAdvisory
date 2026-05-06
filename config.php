<?php
$localConfigPath = __DIR__ . '/config.local.php';
if (is_file($localConfigPath)) {
    require_once $localConfigPath;
}

class config
{
    private static $pdo = null;

    public static function getConnexion()
    {
        if (!isset(self::$pdo)) {
            $servername = "localhost";
            $username = "root";
            $password = "";
            $dbname = "digitadvisory";
            try {
                self::$pdo = new PDO(
                    "mysql:host=$servername;dbname=$dbname;charset=utf8mb4",
                    $username,
                    $password

                );
                self::$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                self::$pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);


            } catch (Exception $e) {
                die('Erreur: ' . $e->getMessage());
            }
        }
        return self::$pdo;
    }

    public static function getOpenAIKey(): ?string
    {
        $envKey = getenv('OPENAI_API_KEY');
        if (is_string($envKey) && trim($envKey) !== '') {
            return trim($envKey);
        }

        if (defined('OPENAI_API_KEY') && trim((string) OPENAI_API_KEY) !== '') {
            return trim((string) OPENAI_API_KEY);
        }

        return null;
    }
}
?>
