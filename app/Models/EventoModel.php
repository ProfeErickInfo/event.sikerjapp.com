<?php
// ============================================================
// MODELO: EventoModel
// Maneja todo lo relacionado con tbx_eventos
// ============================================================

class EventoModel extends Model
{
    protected string $table = 'tbx_eventos';

    // Lista de eventos públicos (activos) con categoría
  public function getPublicos(int $limit = 12, int $offset = 0): array
{
    return $this->raw(
        "SELECT e.*, c.nombre as categoria
         FROM tbx_eventos e
         LEFT JOIN tbx_categorias c ON e.id_categoria = c.id
         ORDER BY e.estado DESC, e.fecha DESC
         LIMIT ? OFFSET ?",
        [$limit, $offset]
    );
}

    // Cuenta eventos públicos (para paginación)
   public function countPublicos(): int
{
    $stmt = $this->db->query("SELECT COUNT(*) FROM tbx_eventos");
    return (int) $stmt->fetchColumn();
}
    // Detalle de un evento con categoría
    public function getConCategoria(int $id): array|false
    {
        $stmt = $this->db->prepare(
            "SELECT e.*, c.nombre as categoria
             FROM tbx_eventos e
             LEFT JOIN tbx_categorias c ON e.id_categoria = c.id
             WHERE e.id = ?"
        );
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    // Todos los eventos para el admin con paginación
    public function getAdmin(int $limit = 15, int $offset = 0): array
    {
        return $this->raw(
            "SELECT e.*, c.nombre as categoria
             FROM tbx_eventos e
             LEFT JOIN tbx_categorias c ON e.id_categoria = c.id
             ORDER BY e.fecha DESC
             LIMIT ? OFFSET ?",
            [$limit, $offset]
        );
    }

    // Cuenta todos los eventos
    public function countAdmin(): int
    {
        $stmt = $this->db->query("SELECT COUNT(*) FROM tbx_eventos");
        return (int) $stmt->fetchColumn();
    }

    // Próximos eventos activos
    public function getProximos(int $limit = 5): array
    {
        return $this->raw(
            "SELECT e.*, c.nombre as categoria
             FROM tbx_eventos e
             LEFT JOIN tbx_categorias c ON e.id_categoria = c.id
             WHERE e.estado = 1 AND e.fecha >= CURDATE()
             ORDER BY e.fecha ASC
             LIMIT ?",
            [$limit]
        );
    }

    // Obtiene todas las categorías
    public function getCategorias(): array
    {
        return $this->raw("SELECT * FROM tbx_categorias WHERE estado = 1 ORDER BY nombre");
    }

    // Verifica si un usuario ya está inscrito en un evento
    public function isInscrito(int $idUser, int $idEvento): bool
    {
        $stmt = $this->db->prepare(
            "SELECT id FROM trn_user_evento WHERE iduser = ? AND idevento = ?"
        );
        $stmt->execute([$idUser, $idEvento]);
        return (bool) $stmt->fetch();
    }
}
