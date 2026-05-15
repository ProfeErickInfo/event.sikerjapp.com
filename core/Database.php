<?php
// ============================================================
// CLASE DATABASE
// Maneja la conexión a MySQL usando PDO.
// Usa el patrón Singleton: solo existe UNA conexión en toda
// la aplicación, lo que ahorra recursos.
// ============================================================

class Database
{
    // La única instancia de esta clase
    private static ?Database $instance = null;

    // El objeto de conexión PDO
    private PDO $pdo;

    // El constructor es privado para que nadie haga "new Database()"
    private function __construct()
    {
        $dsn = 'mysql:host=' . DB_HOST
             . ';dbname='    . DB_NAME
             . ';charset='   . DB_CHARSET;

        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,   // Lanza excepciones en errores
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,          // Resultados como arrays asociativos
            PDO::ATTR_EMULATE_PREPARES   => false,                     // Usa prepared statements reales
        ];

        try {
            $this->pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            // En producción nunca mostrar el error real al usuario
            if (APP_ENV === 'development') {
                die('Error de conexión: ' . $e->getMessage());
            } else {
                die('Error de conexión a la base de datos.');
            }
        }
    }

    // Método para obtener la única instancia
    public static function getInstance(): Database
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    // Devuelve el objeto PDO para hacer consultas
    public function getConnection(): PDO
    {
        return $this->pdo;
    }

    // Ejecuta una consulta con parámetros y devuelve el statement
    // Uso: Database::getInstance()->query("SELECT * FROM tbx_eventos WHERE id = ?", [1])
    public function query(string $sql, array $params = []): PDOStatement
    {
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }

    // Evitar clonación y deserialización del singleton
    private function __clone() {}
    public function __wakeup() {}
}
