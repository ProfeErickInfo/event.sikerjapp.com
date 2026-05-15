<?php
// ============================================================
// MODELO: InscripcionModel
// Maneja inscripciones individuales y de delegaciones
// ============================================================

class InscripcionModel extends Model
{
    protected string $table = 'trn_inscripciones';

    // Verifica si un usuario ya está inscrito en un evento
  public function isInscrito(int $idUsuario, int $idEvento): bool
{
    $stmt = $this->db->prepare(
        "SELECT id FROM trn_inscripciones 
         WHERE id_usuario = ? AND id_evento = ? AND tipo = 1 AND estado != 2"
    );
    $stmt->execute([$idUsuario, $idEvento]);
    return (bool) $stmt->fetch();
}
// Obtiene la inscripción de un usuario en un evento
public function getInscripcionUsuario(int $idUsuario, int $idEvento): array|false
{
    $stmt = $this->db->prepare(
        "SELECT * FROM trn_inscripciones 
         WHERE id_usuario = ? AND id_evento = ? AND tipo = 1 AND estado != 2 LIMIT 1"
    );
    $stmt->execute([$idUsuario, $idEvento]);
    return $stmt->fetch();
}
    // Verifica si un participante ya está inscrito en un evento
   public function isParticipanteInscrito(int $idParticipante, int $idEvento): bool
{
    $stmt = $this->db->prepare(
        "SELECT id FROM trn_inscripciones 
         WHERE id_participante = ? AND id_evento = ? AND estado != 2"
    );
    $stmt->execute([$idParticipante, $idEvento]);
    return (bool) $stmt->fetch();
}

    // Inscripción individual
    public function inscribirIndividual(array $data): int
    {
        return $this->insert([
            'id_evento'      => $data['id_evento'],
            'id_usuario'     => $data['id_usuario'],
            'tipo'           => 1,
            'estado'         => 0,
            'valor'          => $data['valor'] ?? 0,
            'nombre'         => $data['nombre'],
            'tipo_doc'       => $data['tipo_doc'],
            'documento'      => $data['documento'],
            'telefono'       => $data['telefono'],
            'nacionalidad'   => $data['nacionalidad'] ?? 'Colombiana',
            'fecha_nac'      => $data['fecha_nac'],
            'genero'         => $data['genero'],
            'fec_inscripcion'=> date('Y-m-d H:i:s'),
        ]);
    }

    // Inscripción de un participante de delegación
    public function inscribirParticipante(array $data): int
{
    // Verifica si existe una inscripción cancelada previa
    $stmt = $this->db->prepare(
        "SELECT id FROM trn_inscripciones 
         WHERE id_participante = ? AND id_evento = ? AND estado = 2"
    );
    $stmt->execute([$data['id_participante'], $data['id_evento']]);
    $existente = $stmt->fetch();

    if ($existente) {
        // Reactiva la inscripción cancelada
        $this->update($existente['id'], [
            'estado'          => 0,
            'fec_inscripcion' => date('Y-m-d H:i:s'),
        ]);
        return $existente['id'];
    }

    // Crea nueva inscripción
    return $this->insert([
        'id_evento'       => $data['id_evento'],
        'id_delegacion'   => $data['id_delegacion'],
        'id_participante' => $data['id_participante'],
        'tipo'            => 2,
        'estado'          => 0,
        'valor'           => $data['valor'] ?? 0,
        'fec_inscripcion' => date('Y-m-d H:i:s'),
    ]);
}

    // Inscripción masiva de participantes
    public function inscribirMasivo(int $idEvento, int $idDelegacion, array $ids, float $valor): int
    {
        $count = 0;
        foreach ($ids as $idParticipante) {
            if (!$this->isParticipanteInscrito((int)$idParticipante, $idEvento)) {
                $this->inscribirParticipante([
                    'id_evento'       => $idEvento,
                    'id_delegacion'   => $idDelegacion,
                    'id_participante' => (int)$idParticipante,
                    'valor'           => $valor,
                ]);
                $count++;
            }
        }
        return $count;
    }

    // Lista de inscripciones de un evento (admin)
    public function getByEvento(int $idEvento): array
    {
        return $this->raw(
            "SELECT i.*,
                    u.name as usuario_nombre, u.email as usuario_email,
                    d.nombre as delegacion_nombre,
                    p.nombre as participante_nombre, p.documento as participante_doc
             FROM trn_inscripciones i
             LEFT JOIN wx25_usu u ON i.id_usuario = u.id
             LEFT JOIN tbx_delegaciones d ON i.id_delegacion = d.id
             LEFT JOIN tbx_participantes p ON i.id_participante = p.id
             WHERE i.id_evento = ?
             ORDER BY i.fec_inscripcion DESC",
            [$idEvento]
        );
    }

    // Inscripciones de un usuario individual
    public function getByUsuario(int $idUsuario): array
    {
        return $this->raw(
            "SELECT i.*, e.nombre_corto as evento_nombre, e.fecha, e.fecha2
             FROM trn_inscripciones i
             LEFT JOIN tbx_eventos e ON i.id_evento = e.id
             WHERE i.id_usuario = ? AND i.tipo = 1
             ORDER BY i.fec_inscripcion DESC",
            [$idUsuario]
        );
    }

    // Inscripciones de una delegación
    public function getByDelegacion(int $idDelegacion, int $idEvento): array
    {
        return $this->raw(
            "SELECT i.*, p.nombre as participante_nombre, p.documento
             FROM trn_inscripciones i
             LEFT JOIN tbx_participantes p ON i.id_participante = p.id
             WHERE i.id_delegacion = ? AND i.id_evento = ?
             ORDER BY p.nombre ASC",
            [$idDelegacion, $idEvento]
        );
    }

    // Aprobar inscripción
    public function aprobar(int $id): bool
    {
        return $this->update($id, ['estado' => 1]);
    }

    // Cancelar inscripción
    public function cancelar(int $id): bool
    {
        return $this->update($id, ['estado' => 2]);
    }

    // Estadísticas de inscripciones por evento
    public function getStats(int $idEvento): array
    {
        $stmt = $this->db->prepare(
            "SELECT 
                COUNT(*) as total,
                SUM(CASE WHEN estado = 0 THEN 1 ELSE 0 END) as pendientes,
                SUM(CASE WHEN estado = 1 THEN 1 ELSE 0 END) as activas,
                SUM(CASE WHEN estado = 2 THEN 1 ELSE 0 END) as canceladas,
                SUM(CASE WHEN tipo = 1 THEN 1 ELSE 0 END) as individuales,
                SUM(CASE WHEN tipo = 2 THEN 1 ELSE 0 END) as delegaciones
             FROM trn_inscripciones WHERE id_evento = ?"
        );
        $stmt->execute([$idEvento]);
        return $stmt->fetch() ?: [];
    }
// Lista pagos de un evento para el admin
public function getPagos(int $idEvento): array
{
    return $this->raw(
        "SELECT i.*,
                u.name as usuario_nombre, u.email as usuario_email,
                d.nombre as delegacion_nombre,
                p.nombre as participante_nombre
         FROM trn_inscripciones i
         LEFT JOIN wx25_usu u ON i.id_usuario = u.id
         LEFT JOIN tbx_delegaciones d ON i.id_delegacion = d.id
         LEFT JOIN tbx_participantes p ON i.id_participante = p.id
         WHERE i.id_evento = ? AND i.pago_estado > 0
         ORDER BY i.pago_fecha DESC",
        [$idEvento]
    );
}

// Estadísticas de pagos por evento
public function getStatsPagos(int $idEvento): array
{
    $stmt = $this->db->prepare(
        "SELECT
            SUM(CASE WHEN pago_estado = 1 THEN 1 ELSE 0 END) as pendientes,
            SUM(CASE WHEN pago_estado = 2 THEN 1 ELSE 0 END) as aprobados,
            SUM(CASE WHEN pago_estado = 3 THEN 1 ELSE 0 END) as rechazados,
            SUM(CASE WHEN pago_estado = 2 THEN valor ELSE 0 END) as total_recaudado
         FROM trn_inscripciones WHERE id_evento = ?"
    );
    $stmt->execute([$idEvento]);
    return $stmt->fetch() ?: [];
}

}
