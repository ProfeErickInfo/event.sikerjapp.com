<?php
// ============================================================
// MODELO: ReporteModel
// Maneja estadísticas y reportes de eventos
// ============================================================

class ReporteModel extends Model
{
    protected string $table = 'tbx_eventos';

    // ── RESUMEN GENERAL ──────────────────────────────────────

    // Resumen de todos los eventos (superadmin)
    public function getResumenGeneral(): array
    {
        return $this->raw(
            "SELECT 
                e.id,
                e.nombre_corto,
                e.fecha,
                e.fecha2,
                e.valor_inscripcion,
                COUNT(DISTINCT i.id) as total_inscritos,
                COUNT(DISTINCT CASE WHEN i.estado = 1 THEN i.id END) as aprobados,
                COUNT(DISTINCT CASE WHEN i.estado = 0 THEN i.id END) as pendientes,
                COUNT(DISTINCT CASE WHEN i.tipo = 1 THEN i.id END) as individuales,
                COUNT(DISTINCT CASE WHEN i.tipo = 2 THEN i.id END) as delegaciones,
                COUNT(DISTINCT a.id) as asistentes,
                COALESCE(SUM(CASE WHEN c.estado = 1 THEN c.valor ELSE 0 END), 0) as recaudado,
                COUNT(DISTINCT CASE WHEN c.estado = 0 THEN c.id END) as pagos_pendientes,
                COUNT(DISTINCT CASE WHEN c.estado = 1 THEN c.id END) as pagos_aprobados
             FROM tbx_eventos e
             LEFT JOIN trn_inscripciones i ON i.id_evento = e.id
             LEFT JOIN trn_asistencia a ON a.id_evento = e.id AND a.id_sesion IS NULL
             LEFT JOIN trn_comprobantes c ON c.id_evento = e.id
             GROUP BY e.id
             ORDER BY e.fecha DESC"
        );
    }

    // Resumen de un evento específico (manager)
    public function getResumenEvento(int $idEvento): array|false
    {
        $stmt = $this->db->prepare(
            "SELECT 
                e.id,
                e.nombre_corto,
                e.fecha,
                e.fecha2,
                e.valor_inscripcion,
                COUNT(DISTINCT i.id) as total_inscritos,
                COUNT(DISTINCT CASE WHEN i.estado = 1 THEN i.id END) as aprobados,
                COUNT(DISTINCT CASE WHEN i.estado = 0 THEN i.id END) as pendientes,
                COUNT(DISTINCT CASE WHEN i.tipo = 1 THEN i.id END) as individuales,
                COUNT(DISTINCT CASE WHEN i.tipo = 2 THEN i.id END) as delegaciones,
                COUNT(DISTINCT a.id) as asistentes,
                COALESCE(SUM(CASE WHEN c.estado = 1 THEN c.valor ELSE 0 END), 0) as recaudado,
                COUNT(DISTINCT CASE WHEN c.estado = 0 THEN c.id END) as pagos_pendientes,
                COUNT(DISTINCT CASE WHEN c.estado = 1 THEN c.id END) as pagos_aprobados
             FROM tbx_eventos e
             LEFT JOIN trn_inscripciones i ON i.id_evento = e.id
             LEFT JOIN trn_asistencia a ON a.id_evento = e.id AND a.id_sesion IS NULL
             LEFT JOIN trn_comprobantes c ON c.id_evento = e.id
             WHERE e.id = ?
             GROUP BY e.id"
        );
        $stmt->execute([$idEvento]);
        return $stmt->fetch();
    }

    // ── LISTADOS ─────────────────────────────────────────────

    // Lista de inscritos por evento
 public function getInscritos(int $idEvento): array
{
    return $this->raw(
        "SELECT 
            i.id,
            CASE 
                WHEN i.tipo = 1 THEN COALESCE(u.name, i.nombre)
                ELSE p.nombre
            END as nombre,
            COALESCE(u.email, p.email, '') as email,
            CASE
                WHEN i.tipo = 1 THEN i.documento
                ELSE p.documento
            END as documento,
            CASE
                WHEN i.tipo = 1 THEN i.telefono
                ELSE p.telefono
            END as telefono,
            i.nacionalidad,
            CASE WHEN i.genero = 1 THEN 'Masculino' 
                 WHEN i.genero = 2 THEN 'Femenino' 
                 ELSE 'Otro' END as genero,
            CASE WHEN i.tipo = 1 THEN 'Individual' ELSE 'Delegacion' END as tipo,
            d.nombre as delegacion,
            CASE WHEN i.estado = 0 THEN 'Pendiente'
                 WHEN i.estado = 1 THEN 'Aprobada'
                 ELSE 'Cancelada' END as estado_inscripcion,
            CASE WHEN i.pago_estado = 0 THEN 'Sin pago'
                 WHEN i.pago_estado = 1 THEN 'En revision'
                 WHEN i.pago_estado = 2 THEN 'Aprobado'
                 ELSE 'Rechazado' END as estado_pago,
            i.valor,
            i.fec_inscripcion
         FROM trn_inscripciones i
         LEFT JOIN wx25_usu u ON i.id_usuario = u.id
         LEFT JOIN tbx_participantes p ON i.id_participante = p.id
         LEFT JOIN tbx_delegaciones d ON i.id_delegacion = d.id
         WHERE i.id_evento = ? AND i.estado != 2
         ORDER BY d.nombre ASC, nombre ASC",
        [$idEvento]
    );
}
    // Lista de inscritos agrupados por delegación
    public function getInscritosPorDelegacion(int $idEvento): array
    {
        return $this->raw(
            "SELECT 
                d.nombre as delegacion,
                COUNT(i.id) as total,
                SUM(CASE WHEN i.estado = 1 THEN 1 ELSE 0 END) as aprobados,
                SUM(i.valor) as valor_total,
                GROUP_CONCAT(COALESCE(p.nombre, u.name) ORDER BY COALESCE(p.nombre, u.name) SEPARATOR ', ') as participantes
             FROM trn_inscripciones i
             LEFT JOIN wx25_usu u ON i.id_usuario = u.id
             LEFT JOIN tbx_participantes p ON i.id_participante = p.id
             LEFT JOIN tbx_delegaciones d ON i.id_delegacion = d.id
             WHERE i.id_evento = ? AND i.tipo = 2 AND i.estado != 2
             GROUP BY d.id
             ORDER BY d.nombre ASC",
            [$idEvento]
        );
    }

    // Lista de pagos por evento
    public function getPagos(int $idEvento): array
    {
        return $this->raw(
            "SELECT 
                c.id,
                COALESCE(u.name, d.nombre) as nombre,
                CASE WHEN c.id_delegacion IS NOT NULL THEN 'Delegación' ELSE 'Individual' END as tipo,
                c.valor,
                c.descripcion,
                CASE WHEN c.estado = 0 THEN 'Pendiente'
                     WHEN c.estado = 1 THEN 'Aprobado'
                     ELSE 'Rechazado' END as estado,
                c.fec_subida,
                adm.name as aprobado_por
             FROM trn_comprobantes c
             LEFT JOIN wx25_usu u ON c.id_usuario = u.id
             LEFT JOIN tbx_delegaciones d ON c.id_delegacion = d.id
             LEFT JOIN wx25_usu adm ON c.aprobado_por = adm.id
             WHERE c.id_evento = ?
             ORDER BY c.fec_subida DESC",
            [$idEvento]
        );
    }

    // Lista de asistencia por evento
    public function getAsistencia(int $idEvento): array
    {
        return $this->raw(
            "SELECT 
                a.fecha_hora,
                COALESCE(u.name, p.nombre) as nombre,
                CASE WHEN a.id_sesion IS NULL THEN 'Entrada general' 
                     ELSE s.nombre END as sesion,
                DATE(a.fecha_hora) as fecha,
                TIME(a.fecha_hora) as hora
             FROM trn_asistencia a
             LEFT JOIN wx25_usu u ON a.id_usuario = u.id
             LEFT JOIN tbx_participantes p ON a.id_usuario = p.id
             LEFT JOIN tbx_sesiones_evt s ON a.id_sesion = s.id
             WHERE a.id_evento = ?
             ORDER BY a.fecha_hora DESC",
            [$idEvento]
        );
    }

    // Asistencia agrupada por día y sesión
    public function getAsistenciaPorDia(int $idEvento): array
    {
        return $this->raw(
            "SELECT 
                DATE(a.fecha_hora) as fecha,
                CASE WHEN a.id_sesion IS NULL THEN 'Entrada general' 
                     ELSE s.nombre END as sesion,
                COUNT(DISTINCT a.id_usuario) as total
             FROM trn_asistencia a
             LEFT JOIN tbx_sesiones_evt s ON a.id_sesion = s.id
             WHERE a.id_evento = ?
             GROUP BY DATE(a.fecha_hora), a.id_sesion
             ORDER BY fecha ASC",
            [$idEvento]
        );
    }

    // Estado de pagos detallado
    public function getEstadoPagos(int $idEvento): array
    {
        $stmt = $this->db->prepare(
            "SELECT
                COUNT(DISTINCT CASE WHEN c.estado = 0 THEN c.id END) as pendientes,
                COUNT(DISTINCT CASE WHEN c.estado = 1 THEN c.id END) as aprobados,
                COUNT(DISTINCT CASE WHEN c.estado = 2 THEN c.id END) as rechazados,
                COALESCE(SUM(CASE WHEN c.estado = 1 THEN c.valor ELSE 0 END), 0) as total_recaudado,
                COALESCE(SUM(CASE WHEN c.estado = 0 THEN c.valor ELSE 0 END), 0) as total_pendiente
             FROM trn_comprobantes c
             WHERE c.id_evento = ?"
        );
        $stmt->execute([$idEvento]);
        return $stmt->fetch() ?: [];
    }
}
