<?php
// ============================================================
// MODELO: ComprobanteModel
// Maneja múltiples comprobantes de pago por evento
// ============================================================

class ComprobanteModel extends Model
{
    protected string $table = 'trn_comprobantes';

    // Obtiene comprobantes de un usuario en un evento
    public function getByUsuario(int $idUsuario, int $idEvento): array
    {
        return $this->raw(
            "SELECT * FROM trn_comprobantes
             WHERE id_usuario = ? AND id_evento = ?
             ORDER BY fec_subida DESC",
            [$idUsuario, $idEvento]
        );
    }

    // Obtiene comprobantes de una delegación en un evento
    public function getByDelegacion(int $idDelegacion, int $idEvento): array
    {
        return $this->raw(
            "SELECT * FROM trn_comprobantes
             WHERE id_delegacion = ? AND id_evento = ?
             ORDER BY fec_subida DESC",
            [$idDelegacion, $idEvento]
        );
    }

    // Total aprobado por usuario en un evento
    public function getTotalAprobado(int $idEvento, ?int $idUsuario = null, ?int $idDelegacion = null): float
    {
        if ($idUsuario) {
            $stmt = $this->db->prepare(
                "SELECT COALESCE(SUM(valor), 0) FROM trn_comprobantes
                 WHERE id_evento = ? AND id_usuario = ? AND estado = 1"
            );
            $stmt->execute([$idEvento, $idUsuario]);
        } else {
            $stmt = $this->db->prepare(
                "SELECT COALESCE(SUM(valor), 0) FROM trn_comprobantes
                 WHERE id_evento = ? AND id_delegacion = ? AND estado = 1"
            );
            $stmt->execute([$idEvento, $idDelegacion]);
        }
        return (float) $stmt->fetchColumn();
    }

    // Sube un nuevo comprobante
    public function subir(array $data): int
    {
        return $this->insert([
            'id_evento'     => $data['id_evento'],
            'id_usuario'    => $data['id_usuario']    ?? null,
            'id_delegacion' => $data['id_delegacion'] ?? null,
            'archivo'       => $data['archivo'],
            'valor'         => $data['valor']         ?? 0,
            'descripcion'   => $data['descripcion']   ?? null,
            'estado'        => 0,
            'fec_subida'    => date('Y-m-d H:i:s'),
        ]);
    }

    // Aprueba un comprobante
    public function aprobar(int $id, int $idAdmin): bool
    {
        return $this->update($id, [
            'estado'       => 1,
            'aprobado_por' => $idAdmin,
        ]);
    }

    // Rechaza un comprobante
    public function rechazar(int $id): bool
    {
        return $this->update($id, ['estado' => 2]);
    }

    // Obtiene todos los comprobantes de un evento para el admin
    public function getByEvento(int $idEvento): array
    {
        return $this->raw(
            "SELECT c.*,
                    u.name as usuario_nombre, u.email as usuario_email,
                    d.nombre as delegacion_nombre
             FROM trn_comprobantes c
             LEFT JOIN wx25_usu u ON c.id_usuario = u.id
             LEFT JOIN tbx_delegaciones d ON c.id_delegacion = d.id
             WHERE c.id_evento = ?
             ORDER BY c.fec_subida DESC",
            [$idEvento]
        );
    }

    // Estadísticas de comprobantes por evento
    public function getStats(int $idEvento): array
    {
        $stmt = $this->db->prepare(
            "SELECT
                COUNT(*) as total,
                SUM(CASE WHEN estado = 0 THEN 1 ELSE 0 END) as pendientes,
                SUM(CASE WHEN estado = 1 THEN 1 ELSE 0 END) as aprobados,
                SUM(CASE WHEN estado = 2 THEN 1 ELSE 0 END) as rechazados,
                SUM(CASE WHEN estado = 1 THEN valor ELSE 0 END) as total_recaudado
             FROM trn_comprobantes WHERE id_evento = ?"
        );
        $stmt->execute([$idEvento]);
        return $stmt->fetch() ?: [];
    }

    // Verifica si el pago está completo
    public function isPagoCompleto(int $idEvento, float $valorTotal,
                                    ?int $idUsuario = null, ?int $idDelegacion = null): bool
    {
        $totalAprobado = $this->getTotalAprobado($idEvento, $idUsuario, $idDelegacion);
        return $totalAprobado >= $valorTotal;
    }
}
