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
        $this->requireRole('admin', 'manager');

        $evento   = $this->eventoModel->getConCategoria((int) $idEvento);
        if (!$evento) {
            $this->redirect('admin/events');
            return;
        }

        // Verifica acceso del manager
        $user = Session::user();
        if ($user['tipoU'] == 4 && $evento['id_admin'] != $user['id']) {
            Session::flash('error', 'No tienes permiso para acceder a este evento.');
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
    $this->requireRole('admin', 'manager');

    $qrData   = $this->input('qr_data');
    $idEvento = (int) $this->input('id_evento');
    $idSesion = (int) $this->input('id_sesion', 0);

    if (empty($qrData)) {
        $this->json(['success' => false, 'message' => 'Ingresa un código o documento.'], 400);
        return;
    }

    // Detecta si es QR (contiene |) o es número de documento
    if (str_contains($qrData, '|')) {
        // Es QR escaneado
        $usuario = $this->asistenciaModel->getUsuarioByQR($qrData);
    } else {
        // Es número de documento — busca en inscritos del evento
        $usuario = $this->asistenciaModel->getUsuarioByDocumento($qrData, $idEvento);
    }

    if (!$usuario) {
        $this->json(['success' => false, 'message' => 'Documento no encontrado o no inscrito.'], 404);
        return;
    }

    // ... resto del método igual
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
