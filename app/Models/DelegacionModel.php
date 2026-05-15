<?php
// ============================================================
// MODELO: DelegacionModel
// Maneja delegaciones y sus participantes
// ============================================================

class DelegacionModel extends Model
{
    protected string $table = 'tbx_delegaciones';

    // Obtiene la delegación de un usuario
    public function getByUsuario(int $idUsuario): array|false
    {
        $stmt = $this->db->prepare(
            "SELECT * FROM tbx_delegaciones WHERE id_usuario = ? AND estado = 1 LIMIT 1"
        );
        $stmt->execute([$idUsuario]);
        return $stmt->fetch();
    }

    // Crea una delegación para un usuario
    public function crear(array $data): int
    {
        return $this->insert([
            'id_usuario'    => $data['id_usuario'],
            'nombre'        => $data['nombre'],
            'representante' => $data['representante'] ?? 'No Aplica',
            'telefono'      => $data['telefono'] ?? null,
            'email'         => $data['email'] ?? null,
            'ciudad'        => $data['ciudad'] ?? null,
            'fec_reg'       => date('Y-m-d'),
            'estado'        => 1,
        ]);
    }

    // ── PARTICIPANTES ────────────────────────────────────────

    // Lista participantes de una delegación
    public function getParticipantes(int $idDelegacion): array
    {
        return $this->raw(
            "SELECT * FROM tbx_participantes 
             WHERE id_delegacion = ? AND estado = 1 
             ORDER BY nombre ASC",
            [$idDelegacion]
        );
    }

    // Participantes con estado de inscripción en un evento
    public function getParticipantesConInscripcion(int $idDelegacion, int $idEvento): array
    {
        return $this->raw(
            "SELECT p.*, 
                    i.id as id_inscripcion,
                    i.estado as inscripcion_estado
             FROM tbx_participantes p
             LEFT JOIN trn_inscripciones i 
                ON i.id_participante = p.id AND i.id_evento = ?
             WHERE p.id_delegacion = ? AND p.estado = 1
             ORDER BY p.nombre ASC",
            [$idEvento, $idDelegacion]
        );
    }

    // Agrega un participante
   public function agregarParticipante(array $data): int
{
    $stmt = $this->db->prepare(
        "INSERT INTO tbx_participantes 
         (id_delegacion, nombre, tipo_doc, documento, email, telefono, 
          nacionalidad, fecha_nac, genero, estado, fec_reg)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 1, ?)"
    );
    $stmt->execute([
        $data['id_delegacion'],
        $data['nombre'],
        $data['tipo_doc']      ?? 1,
        $data['documento']     ?? null,
        $data['email']         ?? null,
        $data['telefono']      ?? null,
        $data['nacionalidad']  ?? 'Colombiana',
        $data['fecha_nac']     ?? null,
        $data['genero']        ?? 1,
        date('Y-m-d'),
    ]);
    return (int) $this->db->lastInsertId();
}

// Actualiza datos de un participante
public function actualizarParticipante(int $id, array $data): bool
{
    $stmt = $this->db->prepare(
        "UPDATE tbx_participantes SET
            nombre       = ?,
            tipo_doc     = ?,
            documento    = ?,
            email        = ?,
            telefono     = ?,
            nacionalidad = ?,
            fecha_nac    = ?,
            genero       = ?
         WHERE id = ?"
    );
    return $stmt->execute([
        $data['nombre'],
        $data['tipo_doc']     ?? 1,
        $data['documento']    ?? null,
        $data['email']        ?? null,
        $data['telefono']     ?? null,
        $data['nacionalidad'] ?? 'Colombiana',
        $data['fecha_nac']    ?? null,
        $data['genero']       ?? 1,
        $id,
    ]);
}




    // Elimina un participante (soft delete)
    public function eliminarParticipante(int $id): bool
    {
        $stmt = $this->db->prepare(
            "UPDATE tbx_participantes SET estado = 0 WHERE id = ?"
        );
        return $stmt->execute([$id]);
    }

    // Busca un participante por ID
    public function findParticipante(int $id): array|false
    {
        $stmt = $this->db->prepare(
            "SELECT * FROM tbx_participantes WHERE id = ?"
        );
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    // Cuenta participantes de una delegación
    public function countParticipantes(int $idDelegacion): int
    {
        $stmt = $this->db->prepare(
            "SELECT COUNT(*) FROM tbx_participantes WHERE id_delegacion = ? AND estado = 1"
        );
        $stmt->execute([$idDelegacion]);
        return (int) $stmt->fetchColumn();
    }
}
