<?php
// ============================================================
// CONTROLADOR: PagosController
// Maneja múltiples comprobantes de pago por evento
// ============================================================

class PagosController extends Controller
{
    private ComprobanteModel $comprobanteModel;
    private InscripcionModel $inscripcionModel;
    private EventoModel      $eventoModel;

    public function __construct()
    {
        require_once ROOT_PATH . '/app/Models/ComprobanteModel.php';
        require_once ROOT_PATH . '/app/Models/InscripcionModel.php';
        require_once ROOT_PATH . '/app/Models/EventoModel.php';
        $this->comprobanteModel = new ComprobanteModel();
        $this->inscripcionModel = new InscripcionModel();
        $this->eventoModel      = new EventoModel();
    }

    // ── USUARIO ──────────────────────────────────────────────

    // GET /events/{id}/pago
    public function form(string $idEvento): void
    {
        $this->requireAuth();

        $user   = Session::user();
        $evento = $this->eventoModel->getConCategoria((int) $idEvento);

        if (!$evento) {
            $this->redirect('events');
            return;
        }

        // Verifica que tenga inscripción activa
        $inscripcion = $this->getInscripcionActiva($user, (int) $idEvento);
        if (!$inscripcion) {
            Session::flash('error', 'No tienes una inscripción activa en este evento.');
            $this->redirect('events/' . $idEvento);
            return;
        }

        // Obtiene comprobantes según tipo de usuario
        $idDelegacion = null;
        if ($user['tipoU'] == 2) {
            require_once ROOT_PATH . '/app/Models/DelegacionModel.php';
            $delegacionModel = new DelegacionModel();
            $delegacion      = $delegacionModel->getByUsuario($user['id']);
            $idDelegacion    = $delegacion['id'] ?? null;
            $comprobantes    = $idDelegacion
                ? $this->comprobanteModel->getByDelegacion($idDelegacion, (int) $idEvento)
                : [];
        } else {
            $comprobantes = $this->comprobanteModel->getByUsuario($user['id'], (int) $idEvento);
        }

        // Total aprobado
        $totalAprobado = $this->comprobanteModel->getTotalAprobado(
            (int) $idEvento,
            $user['tipoU'] != 1 ? $user['id'] : null,
            $idDelegacion
        );

        $this->viewWithLayout('pagos/form', 'layouts/main', [
            'title'         => 'Pagos — ' . $evento['nombre_corto'],
            'evento'        => $evento,
            'inscripcion'   => $inscripcion,
            'comprobantes'  => $comprobantes,
            'totalAprobado' => $totalAprobado,
            'idDelegacion'  => $idDelegacion,
        ]);
    }

    // POST /events/{id}/pago/subir
    public function subir(string $idEvento): void
    {
        $this->requireAuth();

        $user   = Session::user();
        $evento = $this->eventoModel->find((int) $idEvento);

        $inscripcion = $this->getInscripcionActiva($user, (int) $idEvento);
        if (!$inscripcion) {
            Session::flash('error', 'No tienes una inscripción activa.');
            $this->redirect('events/' . $idEvento);
            return;
        }

        // Valida archivo
        if (empty($_FILES['comprobante']['name'])) {
            Session::flash('error', 'Debes seleccionar un archivo.');
            $this->redirect('events/' . $idEvento . '/pago');
            return;
        }

        $file    = $_FILES['comprobante'];
        $ext     = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png', 'pdf', 'webp'];

        if (!in_array($ext, $allowed)) {
            Session::flash('error', 'Formato no permitido. Usa JPG, PNG, PDF o WEBP.');
            $this->redirect('events/' . $idEvento . '/pago');
            return;
        }

        if ($file['size'] > 5 * 1024 * 1024) {
            Session::flash('error', 'El archivo no puede superar 5MB.');
            $this->redirect('events/' . $idEvento . '/pago');
            return;
        }

        // Guarda el archivo
        $uploadDir = UPLOADS_PATH . '/comprobantes/';
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

        $filename    = 'pago_' . $user['id'] . '_' . time() . '.' . $ext;
        $destination = $uploadDir . $filename;

        if (!move_uploaded_file($file['tmp_name'], $destination)) {
            Session::flash('error', 'Error al subir el archivo.');
            $this->redirect('events/' . $idEvento . '/pago');
            return;
        }

        // Obtiene delegación si aplica
        $idDelegacion = null;
        if ($user['tipoU'] == 2) {
            require_once ROOT_PATH . '/app/Models/DelegacionModel.php';
            $delegacionModel = new DelegacionModel();
            $delegacion      = $delegacionModel->getByUsuario($user['id']);
            $idDelegacion    = $delegacion['id'] ?? null;
        }

        // Guarda el comprobante
        $this->comprobanteModel->subir([
            'id_evento'     => (int) $idEvento,
            'id_usuario'    => $user['tipoU'] != 1 ? $user['id'] : null,
            'id_delegacion' => $idDelegacion,
            'archivo'       => 'comprobantes/' . $filename,
            'valor'         => (float) $this->input('valor', 0),
            'descripcion'   => $this->input('descripcion'),
        ]);

        Session::flash('success', '¡Comprobante subido! El administrador lo revisará pronto.');
        $this->redirect('events/' . $idEvento . '/pago');
    }

    // ── ADMIN ────────────────────────────────────────────────

    // GET /admin/pagos
    public function index(): void
    {
        $this->requireRole('admin', 'manager');

        $idEvento = (int) $this->query('evento', 0);
        $db       = Database::getInstance()->getConnection();
        $eventos  = $db->query(
            "SELECT id, nombre_corto FROM tbx_eventos ORDER BY fecha DESC"
        )->fetchAll();

        $comprobantes = [];
        $eventoActual = null;
        $stats        = [];

        if ($idEvento) {
            $comprobantes = $this->comprobanteModel->getByEvento($idEvento);
            $eventoActual = $this->eventoModel->find($idEvento);
            $stats        = $this->comprobanteModel->getStats($idEvento);
        }

        $this->viewWithLayout('admin/pagos/index', 'layouts/main', [
            'title'         => 'Gestión de Pagos',
            'eventos'       => $eventos,
            'comprobantes'  => $comprobantes,
            'eventoActual'  => $eventoActual,
            'idEvento'      => $idEvento,
            'stats'         => $stats,
        ]);
    }

    // POST /admin/pagos/aprobar/{id}
    public function aprobar(string $id): void
    {
        $this->requireRole('admin', 'manager');

        $user     = Session::user();
        $idEvento = (int) $this->input('id_evento');

        $this->comprobanteModel->aprobar((int) $id, $user['id']);

        // Verifica si el pago está completo y actualiza inscripciones
        $comprobante = $this->comprobanteModel->find((int) $id);
        $evento      = $this->eventoModel->find($idEvento);
        $valorTotal  = (float) ($evento['valor_inscripcion'] ?? 0);

        $totalAprobado = $this->comprobanteModel->getTotalAprobado(
            $idEvento,
            $comprobante['id_usuario']    ?? null,
            $comprobante['id_delegacion'] ?? null
        );

        // Si el total aprobado cubre el valor — actualiza estado de inscripciones
        if ($valorTotal > 0 && $totalAprobado >= $valorTotal) {
            $db = Database::getInstance()->getConnection();

            if ($comprobante['id_delegacion']) {
                $stmt = $db->prepare(
                    "UPDATE trn_inscripciones SET pago_estado = 2
                     WHERE id_delegacion = ? AND id_evento = ?"
                );
                $stmt->execute([$comprobante['id_delegacion'], $idEvento]);
            } else {
                $stmt = $db->prepare(
                    "UPDATE trn_inscripciones SET pago_estado = 2
                     WHERE id_usuario = ? AND id_evento = ? AND tipo = 1"
                );
                $stmt->execute([$comprobante['id_usuario'], $idEvento]);
            }
        }

        Session::flash('success', 'Comprobante aprobado correctamente.');
        $this->redirect('admin/pagos?evento=' . $idEvento);
    }

    // POST /admin/pagos/rechazar/{id}
    public function rechazar(string $id): void
    {
        $this->requireRole('admin', 'manager');

        $idEvento = (int) $this->input('id_evento');
        $this->comprobanteModel->rechazar((int) $id);

        Session::flash('success', 'Comprobante rechazado.');
        $this->redirect('admin/pagos?evento=' . $idEvento);
    }

    // ── HELPERS ──────────────────────────────────────────────

    private function getInscripcionActiva(array $user, int $idEvento): array|false
    {
        if ($user['tipoU'] == 2) {
            require_once ROOT_PATH . '/app/Models/DelegacionModel.php';
            $delegacionModel = new DelegacionModel();
            $delegacion      = $delegacionModel->getByUsuario($user['id']);
            if (!$delegacion) return false;

            $stmt = Database::getInstance()->getConnection()->prepare(
                "SELECT * FROM trn_inscripciones
                 WHERE id_delegacion = ? AND id_evento = ? AND estado != 2
                 LIMIT 1"
            );
            $stmt->execute([$delegacion['id'], $idEvento]);
            return $stmt->fetch();
        }

        return $this->inscripcionModel->getInscripcionUsuario($user['id'], $idEvento);
    }
}
