<?php
// ============================================================
// MODELO: CredencialModel
// Maneja credenciales y certificados
// ============================================================

class CredencialModel extends Model
{
    protected string $table = 'trn_documentos_aprobados';

    // Verifica si una credencial está aprobada
    public function isAprobada(int $idUsuario, int $idEvento, int $tipo = 1): bool
    {
        $stmt = $this->db->prepare(
            "SELECT id FROM trn_documentos_aprobados
             WHERE id_usuario = ? AND id_evento = ? AND tipo = ? AND aprobado = 1"
        );
        $stmt->execute([$idUsuario, $idEvento, $tipo]);
        return (bool) $stmt->fetch();
    }

    // Obtiene datos completos para la credencial individual
    public function getDatosIndividual(int $idUsuario, int $idEvento): array|false
    {
        $stmt = $this->db->prepare(
            "SELECT 
                u.id as id_usuario,
                u.name as nombre,
                u.email,
                i.documento,
                i.tipo_doc,
                i.nacionalidad,
                e.id as id_evento,
                e.nombre_corto as evento_nombre,
                e.fecha as evento_fecha,
                e.fecha2 as evento_fecha2,
                e.pic as evento_pic,
                NULL as delegacion_nombre
             FROM wx25_usu u
             LEFT JOIN trn_inscripciones i ON i.id_usuario = u.id AND i.id_evento = ?
             LEFT JOIN tbx_eventos e ON e.id = ?
             WHERE u.id = ? AND i.estado = 1"
        );
        $stmt->execute([$idEvento, $idEvento, $idUsuario]);
        return $stmt->fetch();
    }

    // Obtiene datos para credencial de participante de delegación
    public function getDatosParticipante(int $idParticipante, int $idEvento): array|false
    {
        $stmt = $this->db->prepare(
            "SELECT
                p.id as id_participante,
                p.nombre,
                p.documento,
                p.tipo_doc,
                p.nacionalidad,
                d.nombre as delegacion_nombre,
                d.id as id_delegacion,
                e.id as id_evento,
                e.nombre_corto as evento_nombre,
                e.fecha as evento_fecha,
                e.fecha2 as evento_fecha2,
                e.pic as evento_pic
             FROM tbx_participantes p
             LEFT JOIN tbx_delegaciones d ON d.id = p.id_delegacion
             LEFT JOIN trn_inscripciones i ON i.id_participante = p.id AND i.id_evento = ?
             LEFT JOIN tbx_eventos e ON e.id = ?
             WHERE p.id = ? AND i.estado = 1"
        );
        $stmt->execute([$idEvento, $idEvento, $idParticipante]);
        return $stmt->fetch();
    }

    // Lista de inscritos con estado de credencial para el admin
    public function getListaCredenciales(int $idEvento): array
    {
       return $this->raw(
    "SELECT 
        i.id,
        i.tipo,
        i.id_usuario,
        i.id_participante,
        i.id_delegacion,
        COALESCE(u.name, p.nombre) as nombre,
        COALESCE(u.email, p.email) as email,
        d.nombre as delegacion_nombre,
        da.aprobado as credencial_aprobada,
        da.id as id_aprobacion,
        -- Estado de pago
        COALESCE(
            (SELECT MAX(c.estado) FROM trn_comprobantes c
             WHERE c.id_evento = i.id_evento
             AND (c.id_usuario = i.id_usuario OR c.id_delegacion = i.id_delegacion)
            ), 0
        ) as pago_estado
     FROM trn_inscripciones i
     LEFT JOIN wx25_usu u ON i.id_usuario = u.id
     LEFT JOIN tbx_participantes p ON i.id_participante = p.id
     LEFT JOIN tbx_delegaciones d ON i.id_delegacion = d.id
     LEFT JOIN trn_documentos_aprobados da 
   ON da.id_evento = i.id_evento 
   AND (
       (i.tipo = 1 AND da.id_usuario = i.id_usuario AND da.tipo = 1) OR
       (i.tipo = 2 AND da.id_usuario = i.id_participante AND da.tipo = 2)
   )
     WHERE i.id_evento = ? AND i.estado = 1
     ORDER BY nombre ASC",
    [$idEvento]
);
    }

    // Aprueba credencial
    public function aprobarCredencial(int $idUsuario, int $idEvento, int $idAdmin, int $tipo = 1): void
    {
        // Verifica si ya existe
        $stmt = $this->db->prepare(
            "SELECT id FROM trn_documentos_aprobados
             WHERE id_usuario = ? AND id_evento = ? AND tipo = ?"
        );
        $stmt->execute([$idUsuario, $idEvento, $tipo]);
        $existe = $stmt->fetch();

        if ($existe) {
            $this->update($existe['id'], [
                'aprobado'          => 1,
                'fecha_aprobacion'  => date('Y-m-d H:i:s'),
                'id_admin'          => $idAdmin,
            ]);
        } else {
            $this->insert([
                'id_usuario'        => $idUsuario,
                'id_evento'         => $idEvento,
                'tipo'              => $tipo,
                'aprobado'          => 1,
                'fecha_aprobacion'  => date('Y-m-d H:i:s'),
                'id_admin'          => $idAdmin,
            ]);
        }
    }
// Revierte aprobación de credencial
public function revocarCredencial(int $idUsuario, int $idEvento, int $tipo = 1): void
{
    $stmt = $this->db->prepare(
        "UPDATE trn_documentos_aprobados SET
            aprobado         = 0,
            fecha_aprobacion = NULL,
            id_admin         = NULL
         WHERE id_usuario = ? AND id_evento = ? AND tipo = ?"
    );
    $stmt->execute([$idUsuario, $idEvento, $tipo]);
}

}
