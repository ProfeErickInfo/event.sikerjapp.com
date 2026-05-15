<?php
// ============================================================
// CONTROLADOR: AsistenciaController
// Maneja escaneo QR y registro de asistencia
// ============================================================

class AsistenciaController extends Controller
{
    private AsistenciaModel $asistenciaModel;
    private EventoModel     $eventoModel;
    private AgendaModel     $agendaModel;

    public function __construct()
    {
        require_once ROOT_PATH . '/app/Models/AsistenciaModel.php';
        require_once ROOT_PATH . '/app/Models/EventoModel.php';
        require_once ROOT_PATH . '/app/Models/AgendaModel.php';
        $this->asistenciaModel = new AsistenciaModel();
        $this->eventoModel     = new EventoModel();
        $this->agendaModel     = new AgendaModel();
    }

    // GET /admin/asistencia/{id_evento}
    public function index(string $idEvento): void
    {
        $this->requireRole('admin', 'admin_torneo');

        $evento   = $this->eventoModel->getConCategoria((int) $idEvento);
        if (!$evento) {
            $this->redirect('admin/events');
            return;
        }

        $sesiones    = $this->agendaModel->getSesiones((int) $idEvento);
        $idSesion    = (int) $this->query('sesion', 0);
        $stats       = $this->asistenciaModel->getStats((int) $idEvento);

        // Lista según si hay sesión seleccionada
        if ($idSesion) {
            $asistencia  = $this->asistenciaModel->getAsistenciaSesion((int) $idEvento, $idSesion);
            $sesionActual = $this->agendaModel->findSesion($idSesion);
        } else {
            $asistencia  = $this->asistenciaModel->getAsistenciaEvento((int) $idEvento);
            $sesionActual = null;
        }

        $this->viewWithLayout('admin/asistencia/index', 'layouts/main', [
            'title'        => 'Asistencia — ' . $evento['nombre_corto'],
            'evento'       => $evento,
            'sesiones'     => $sesiones,
            'asistencia'   => $asistencia,
            'sesionActual' => $sesionActual,
            'idSesion'     => $idSesion,
            'stats'        => $stats,
        ]);
    }

    // POST /admin/asistencia/scan — Procesa el QR escaneado
    public function scan(): void
    {
        $this->requireRole('admin', 'admin_torneo');

        $qrData   = $this->input('qr_data');
        $idEvento = (int) $this->input('id_evento');
        $idSesion = (int) $this->input('id_sesion', 0);

        if (empty($qrData)) {
            $this->json(['success' => false, 'message' => 'QR inválido.'], 400);
            return;
        }

        // Obtiene datos del usuario desde el QR
        $usuario = $this->asistenciaModel->getUsuarioByQR($qrData);

        if (!$usuario) {
            $this->json(['success' => false, 'message' => 'QR no reconocido.'], 404);
            return;
        }

        // Verifica que tenga inscripción aprobada
        if ($usuario['inscripcion_estado'] != 1) {
            $this->json([
                'success' => false,
                'message' => 'El asistente no tiene inscripción aprobada.',
                'nombre'  => $usuario['nombre'],
            ], 403);
            return;
        }

        $idUsuario = $usuario['id'];

        // Registra según tipo (entrada general o sesión)
        if ($idSesion) {
            if ($this->asistenciaModel->yaEntroSesion($idUsuario, $idEvento, $idSesion)) {
                $this->json([
                    'success'  => false,
                    'message'  => 'Ya registró asistencia a esta sesión hoy.',
                    'nombre'   => $usuario['nombre'],
                    'already'  => true,
                ]);
                return;
            }
            $this->asistenciaModel->registrarSesion($idUsuario, $idEvento, $idSesion, $qrData);
        } else {
            if ($this->asistenciaModel->yaEntroHoy($idUsuario, $idEvento)) {
                $this->json([
                    'success'  => false,
                    'message'  => 'Ya registró entrada hoy.',
                    'nombre'   => $usuario['nombre'],
                    'already'  => true,
                ]);
                return;
            }
            $this->asistenciaModel->registrarEntrada($idUsuario, $idEvento, $qrData);
        }

        $this->json([
            'success'     => true,
            'message'     => '✅ Asistencia registrada',
            'nombre'      => $usuario['nombre'],
            'delegacion'  => $usuario['delegacion_nombre'] ?? null,
            'hora'        => date('H:i:s'),
        ]);
    }
}
