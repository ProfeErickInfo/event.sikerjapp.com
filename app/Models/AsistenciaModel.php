<?php
// ============================================================
// MODELO: AsistenciaModel
// Maneja registro de asistencia por evento y por sesión
// ============================================================

class AsistenciaModel extends Model
{
    protected string $table = 'trn_asistencia';

    // Verifica si ya registró entrada general hoy
    public function yaEntroHoy(int $idUsuario, int $idEvento): bool
    {
        $stmt = $this->db->prepare(
            "SELECT id FROM trn_asistencia
             WHERE id_usuario = ? AND id_evento = ? 
             AND id_sesion IS NULL
             AND DATE(fecha_hora) = CURDATE()"
        );
        $stmt->execute([$idUsuario, $idEvento]);
        return (bool) $stmt->fetch();
    }

    // Verifica si ya registró asistencia a una sesión hoy
    public function yaEntroSesion(int $idUsuario, int $idEvento, int $idSesion): bool
    {
        $stmt = $this->db->prepare(
            "SELECT id FROM trn_asistencia
             WHERE id_usuario = ? AND id_evento = ? AND id_sesion = ?
             AND DATE(fecha_hora) = CURDATE()"
        );
        $stmt->execute([$idUsuario, $idEvento, $idSesion]);
        return (bool) $stmt->fetch();
    }

    // Registra entrada general al evento
    public function registrarEntrada(int $idUsuario, int $idEvento, string $qrData): int
    {
        return $this->insert([
            'id_usuario' => $idUsuario,
            'id_evento'  => $idEvento,
            'id_sesion'  => null,
            'fecha_hora' => date('Y-m-d H:i:s'),
            'qr_data'    => $qrData,
        ]);
    }

    // Registra asistencia a una sesión
    public function registrarSesion(int $idUsuario, int $idEvento, int $idSesion, string $qrData): int
    {
        return $this->insert([
            'id_usuario' => $idUsuario,
            'id_evento'  => $idEvento,
            'id_sesion'  => $idSesion,
            'fecha_hora' => date('Y-m-d H:i:s'),
            'qr_data'    => $qrData,
        ]);
    }

    // Lista de asistencia general de un evento
    public function getAsistenciaEvento(int $idEvento): array
    {
        return $this->raw(
            "SELECT a.*,
                    COALESCE(u.name, p.nombre) as nombre,
                    COALESCE(u.email, p.email) as email,
                    d.nombre as delegacion_nombre
             FROM trn_asistencia a
             LEFT JOIN wx25_usu u ON a.id_usuario = u.id
             LEFT JOIN tbx_participantes p ON a.id_usuario = p.id
            LEFT JOIN trn_inscripciones i ON i.id_participante = p.id AND i.id_evento = a.id_evento
             LEFT JOIN tbx_delegaciones d ON d.id = i.id_delegacion
             WHERE a.id_evento = ? AND a.id_sesion IS NULL
             ORDER BY a.fecha_hora DESC",
            [$idEvento]
        );
    }

    // Lista de asistencia por sesión
    public function getAsistenciaSesion(int $idEvento, int $idSesion): array
    {
        return $this->raw(
            "SELECT a.*,
                    COALESCE(u.name, p.nombre) as nombre,
                    COALESCE(u.email, p.email) as email
             FROM trn_asistencia a
             LEFT JOIN wx25_usu u ON a.id_usuario = u.id
             LEFT JOIN tbx_participantes p ON a.id_usuario = p.id
             WHERE a.id_evento = ? AND a.id_sesion = ?
             ORDER BY a.fecha_hora DESC",
            [$idEvento, $idSesion]
        );
    }

    // Estadísticas de asistencia
    public function getStats(int $idEvento): array
    {
        $stmt = $this->db->prepare(
            "SELECT 
                COUNT(DISTINCT id_usuario) as total_asistentes,
                COUNT(*) as total_registros,
                DATE(fecha_hora) as fecha
             FROM trn_asistencia
             WHERE id_evento = ? AND id_sesion IS NULL
             GROUP BY DATE(fecha_hora)
             ORDER BY fecha ASC"
        );
        $stmt->execute([$idEvento]);
        return $stmt->fetchAll();
    }

    // Obtiene datos del usuario por QR
    public function getUsuarioByQR(string $qrData): array|false
    {
        $parts = explode('|', $qrData);
        if (count($parts) < 3) return false;

        [$idUsuario, $idEvento, $tipo] = $parts;

        if ($tipo === 'individual') {
            $stmt = $this->db->prepare(
                "SELECT u.id, u.name as nombre, u.email, NULL as delegacion_nombre,
                        i.estado as inscripcion_estado, ? as id_evento, ? as tipo
                 FROM wx25_usu u
                 LEFT JOIN trn_inscripciones i ON i.id_usuario = u.id AND i.id_evento = ?
                 WHERE u.id = ?"
            );
            $stmt->execute([$idEvento, $tipo, $idEvento, $idUsuario]);
        } else {
            $stmt = $this->db->prepare(
                "SELECT p.id, p.nombre, p.email,
                        d.nombre as delegacion_nombre,
                        i.estado as inscripcion_estado, ? as id_evento, ? as tipo
                 FROM tbx_participantes p
                 LEFT JOIN tbx_delegaciones d ON d.id = p.id_delegacion
                 LEFT JOIN trn_inscripciones i ON i.id_participante = p.id AND i.id_evento = ?
                 WHERE p.id = ?"
            );
            $stmt->execute([$idEvento, $tipo, $idEvento, $idUsuario]);
        }

        return $stmt->fetch();
    }


    // Busca usuario inscrito por número de documento
public function getUsuarioByDocumento(string $documento, int $idEvento): array|false
{
    // Busca en inscripciones individuales
    $stmt = $this->db->prepare(
        "SELECT u.id, u.name as nombre, u.email,
                NULL as delegacion_nombre,
                i.estado as inscripcion_estado,
                ? as id_evento, 'individual' as tipo
         FROM trn_inscripciones i
         LEFT JOIN wx25_usu u ON i.id_usuario = u.id
         WHERE i.documento = ? AND i.id_evento = ? AND i.tipo = 1
         LIMIT 1"
    );
    $stmt->execute([$idEvento, $documento, $idEvento]);
    $resultado = $stmt->fetch();

    if ($resultado) return $resultado;

    // Busca en participantes de delegación
    $stmt = $this->db->prepare(
        "SELECT p.id, p.nombre, p.email,
                d.nombre as delegacion_nombre,
                i.estado as inscripcion_estado,
                ? as id_evento, 'participante' as tipo
         FROM tbx_participantes p
         LEFT JOIN tbx_delegaciones d ON d.id = p.id_delegacion
         LEFT JOIN trn_inscripciones i ON i.id_participante = p.id AND i.id_evento = ?
         WHERE p.documento = ? AND i.id_evento = ?
         LIMIT 1"
    );
    $stmt->execute([$idEvento, $idEvento, $documento, $idEvento]);
    return $stmt->fetch();
}
}


