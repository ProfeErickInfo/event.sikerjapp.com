<?php
// ============================================================
// MODELO: AgendaModel
// Maneja sesiones y cronograma de eventos
// ============================================================

class AgendaModel extends Model
{
    protected string $table = 'tbx_sesiones_evt';

    // ── SESIONES ─────────────────────────────────────────────

    // Obtiene sesiones de un evento ordenadas por fecha y orden
    public function getSesiones(int $idEvento): array
    {
        return $this->raw(
            "SELECT * FROM tbx_sesiones_evt 
             WHERE id_evento = ? 
             ORDER BY fecha ASC, orden ASC",
            [$idEvento]
        );
    }

    // Obtiene sesiones con su cronograma
    public function getSesionesConCronograma(int $idEvento): array
    {
        $sesiones = $this->getSesiones($idEvento);

        foreach ($sesiones as &$sesion) {
            $sesion['cronograma'] = $this->getCronograma($sesion['id']);
        }

        return $sesiones;
    }

    // Crea una sesión
    public function crearSesion(array $data): int
    {
        return $this->insert([
            'id_evento'    => $data['id_evento'],
            'nombre'       => $data['nombre'],
            'fecha'        => $data['fecha'],
            'lugar'        => $data['lugar'] ?? null,
            'orden'        => $data['orden'] ?? 1,
            'id_creador'   => $data['id_creador'],
            'fecha_creado' => date('Y-m-d'),
        ]);
    }

    // Actualiza una sesión
    public function actualizarSesion(int $id, array $data): bool
    {
        return $this->update($id, [
            'nombre' => $data['nombre'],
            'fecha'  => $data['fecha'],
            'lugar'  => $data['lugar'] ?? null,
            'orden'  => $data['orden'] ?? 1,
        ]);
    }

    // Elimina una sesión y su cronograma
    public function eliminarSesion(int $id): bool
    {
        $stmt = $this->db->prepare(
            "DELETE FROM tbx_cronohora WHERE id_sesion = ?"
        );
        $stmt->execute([$id]);
        return $this->delete($id);
    }

    // ── CRONOGRAMA ───────────────────────────────────────────

    // Obtiene el cronograma de una sesión
    public function getCronograma(int $idSesion): array
    {
        return $this->raw(
            "SELECT * FROM tbx_cronohora 
             WHERE id_sesion = ? 
             ORDER BY hora_i ASC",
            [$idSesion]
        );
    }

    // Agrega un ítem al cronograma
    public function agregarCronograma(array $data): int
    {
        $stmt = $this->db->prepare(
            "INSERT INTO tbx_cronohora 
             (id_evento, id_sesion, nombre, fecha, hora_i, hora_f, lugar, descripcion, id_create)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)"
        );
        $stmt->execute([
            $data['id_evento'],
            $data['id_sesion'],
            $data['nombre']      ?? null,
            $data['fecha'],
            $data['hora_i'],
            $data['hora_f'],
            $data['lugar']       ?? null,
            $data['descripcion'] ?? null,
            $data['id_create'],
        ]);
        return (int) $this->db->lastInsertId();
    }

    // Actualiza un ítem del cronograma
    public function actualizarCronograma(int $id, array $data): bool
    {
        $stmt = $this->db->prepare(
            "UPDATE tbx_cronohora SET
                nombre      = ?,
                hora_i      = ?,
                hora_f      = ?,
                lugar       = ?,
                descripcion = ?
             WHERE id = ?"
        );
        return $stmt->execute([
            $data['nombre']      ?? null,
            $data['hora_i'],
            $data['hora_f'],
            $data['lugar']       ?? null,
            $data['descripcion'] ?? null,
            $id,
        ]);
    }

    // Elimina un ítem del cronograma
    public function eliminarCronograma(int $id): bool
    {
        $stmt = $this->db->prepare("DELETE FROM tbx_cronohora WHERE id = ?");
        return $stmt->execute([$id]);
    }

    // Busca un ítem del cronograma por ID
    public function findCronograma(int $id): array|false
    {
        $stmt = $this->db->prepare("SELECT * FROM tbx_cronohora WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    // Busca una sesión por ID
    public function findSesion(int $id): array|false
    {
        return $this->find($id);
    }
}
