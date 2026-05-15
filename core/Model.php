<?php
// ============================================================
// CLASE BASE MODEL
// Todos tus modelos (EventoModel, UsuarioModel, etc.)
// heredan de esta clase y reciben automáticamente los
// métodos básicos de base de datos.
// ============================================================

class Model
{
    // Nombre de la tabla — cada modelo hijo lo define
    protected string $table = '';

    // Conexión PDO
    protected PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    // ── MÉTODOS BÁSICOS ──────────────────────────────────────

    // Obtener todos los registros
    public function all(string $orderBy = 'id DESC'): array
    {
        $stmt = $this->db->query("SELECT * FROM {$this->table} ORDER BY {$orderBy}");
        return $stmt->fetchAll();
    }

    // Buscar por ID
    public function find(int $id): array|false
    {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    // Buscar con condiciones
    // Uso: $model->where(['estado' => 1, 'id_categoria' => 3])
    public function where(array $conditions): array
    {
        $columns = array_keys($conditions);
        $placeholders = array_map(fn($col) => "{$col} = ?", $columns);
        $sql = "SELECT * FROM {$this->table} WHERE " . implode(' AND ', $placeholders);

        $stmt = $this->db->prepare($sql);
        $stmt->execute(array_values($conditions));
        return $stmt->fetchAll();
    }

    // Insertar un registro
    // Uso: $model->insert(['nombre' => 'Torneo', 'estado' => 1])
    public function insert(array $data): int
    {
        $columns      = implode(', ', array_keys($data));
        $placeholders = implode(', ', array_fill(0, count($data), '?'));

        $sql  = "INSERT INTO {$this->table} ({$columns}) VALUES ({$placeholders})";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(array_values($data));

        return (int) $this->db->lastInsertId();
    }

    // Actualizar un registro por ID
    // Uso: $model->update(5, ['nombre' => 'Nuevo nombre'])
    public function update(int $id, array $data): bool
    {
        $placeholders = implode(', ', array_map(fn($col) => "{$col} = ?", array_keys($data)));
        $sql  = "UPDATE {$this->table} SET {$placeholders} WHERE id = ?";
        $stmt = $this->db->prepare($sql);

        $values   = array_values($data);
        $values[] = $id;

        return $stmt->execute($values);
    }

    // Eliminar por ID
    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare("DELETE FROM {$this->table} WHERE id = ?");
        return $stmt->execute([$id]);
    }

    // Contar registros
    public function count(array $conditions = []): int
    {
        if (empty($conditions)) {
            $stmt = $this->db->query("SELECT COUNT(*) FROM {$this->table}");
        } else {
            $placeholders = implode(' AND ', array_map(fn($col) => "{$col} = ?", array_keys($conditions)));
            $sql  = "SELECT COUNT(*) FROM {$this->table} WHERE {$placeholders}";
            $stmt = $this->db->prepare($sql);
            $stmt->execute(array_values($conditions));
        }
        return (int) $stmt->fetchColumn();
    }

    // Ejecutar SQL personalizado
    // Uso: $model->raw("SELECT * FROM tbx_eventos WHERE fecha >= ?", ['2025-01-01'])
    public function raw(string $sql, array $params = []): array
    {
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    // Ejecutar SQL que no devuelve filas (INSERT, UPDATE, DELETE complejos)
    public function execute(string $sql, array $params = []): bool
    {
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($params);
    }
}
