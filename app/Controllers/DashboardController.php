<?php
// ============================================================
// CONTROLADOR: DashboardController
// ============================================================

class DashboardController extends Controller
{
    public function index(): void
    {
        $this->requireAuth();

        $db   = Database::getInstance()->getConnection();
        $user = Session::user();

        // Estadísticas
        $stats = [
            'total_eventos'   => (int) $db->query("SELECT COUNT(*) FROM tbx_eventos")->fetchColumn(),
            'total_usuarios'  => (int) $db->query("SELECT COUNT(*) FROM wx25_usu")->fetchColumn(),
            'eventos_activos' => (int) $db->query("SELECT COUNT(*) FROM tbx_eventos WHERE estado = 1")->fetchColumn(),
        ];

       // Para delegaciones busca por id_delegacion
if ($user['tipoU'] == 1) {
    $stmt = $db->prepare(
        "SELECT d.id FROM tbx_delegaciones d WHERE d.id_usuario = ? AND d.estado = 1 LIMIT 1"
    );
    $stmt->execute([$user['id']]);
    $delegacion = $stmt->fetch();

    if ($delegacion) {
        $stmt = $db->prepare(
            "SELECT i.*,
                    e.nombre_corto as evento_nombre,
                    e.fecha, e.fecha2,
                    e.valor_inscripcion,
                    p.nombre as participante_nombre
             FROM trn_inscripciones i
             LEFT JOIN tbx_eventos e ON i.id_evento = e.id
             LEFT JOIN tbx_participantes p ON i.id_participante = p.id
             WHERE i.id_delegacion = ? AND i.estado != 2
             ORDER BY e.fecha DESC, p.nombre ASC"
        );
        $stmt->execute([$delegacion['id']]);
    } else {
        $stmt = $db->query("SELECT * FROM trn_inscripciones WHERE 1=0");
    }
} else {
    $stmt = $db->prepare(
        "SELECT i.*,
                e.nombre_corto as evento_nombre,
                e.fecha, e.fecha2,
                e.valor_inscripcion
         FROM trn_inscripciones i
         LEFT JOIN tbx_eventos e ON i.id_evento = e.id
         WHERE i.id_usuario = ? AND i.tipo = 1 AND i.estado != 2
         ORDER BY i.fec_inscripcion DESC"
    );
    $stmt->execute([$user['id']]);
}
$misInscripciones = $stmt->fetchAll();
$stats['mis_inscripciones'] = count($misInscripciones);

        // Próximos eventos
        $stmt = $db->prepare(
            "SELECT e.*, c.nombre as detalle_categoria
             FROM tbx_eventos e
             LEFT JOIN tbx_categorias c ON e.id_categoria = c.id
             WHERE e.estado = 1 AND e.fecha >= CURDATE()
             ORDER BY e.fecha ASC
             LIMIT 5"
        );
        $stmt->execute();
        $proximos_eventos = $stmt->fetchAll();

        $this->viewWithLayout('dashboard/index', 'layouts/main', [
            'title'            => 'Dashboard',
            'stats'            => $stats,
            'proximos_eventos' => $proximos_eventos,
            'misInscripciones' => $misInscripciones,
        ]);
    }
}