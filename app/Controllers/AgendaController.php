<?php
// ============================================================
// CONTROLADOR: AgendaController
// Maneja sesiones y cronograma de eventos
// ============================================================

class AgendaController extends Controller
{
    private AgendaModel $agendaModel;
    private EventoModel $eventoModel;

    public function __construct()
    {
        require_once ROOT_PATH . '/app/Models/AgendaModel.php';
        require_once ROOT_PATH . '/app/Models/EventoModel.php';
        $this->agendaModel = new AgendaModel();
        $this->eventoModel = new EventoModel();
    }

    // ── PÚBLICO ──────────────────────────────────────────────

    // GET /events/{id}/agenda
    public function index(string $idEvento): void
    {
        $evento = $this->eventoModel->getConCategoria((int) $idEvento);

        if (!$evento) {
            Session::flash('error', 'Evento no encontrado.');
            $this->redirect('events');
            return;
        }

        $sesiones = $this->agendaModel->getSesionesConCronograma((int) $idEvento);

        $this->viewWithLayout('agenda/index', 'layouts/main', [
            'title'   => 'Agenda — ' . $evento['nombre_corto'],
            'evento'  => $evento,
            'sesiones'=> $sesiones,
        ]);
    }

    // ── ADMIN SESIONES ───────────────────────────────────────

    // GET /admin/agenda/{id_evento}
    public function admin(string $idEvento): void
    {
        $this->requireRole('admin', 'manager');

        $evento   = $this->eventoModel->getConCategoria((int) $idEvento);
        if (!$evento) {
            $this->redirect('admin/events');
            return;
        }

        $sesiones = $this->agendaModel->getSesionesConCronograma((int) $idEvento);

        $this->viewWithLayout('admin/agenda/index', 'layouts/main', [
            'title'   => 'Agenda — ' . $evento['nombre_corto'],
            'evento'  => $evento,
            'sesiones'=> $sesiones,
        ]);
    }

    // POST /admin/agenda/sesion/store
    public function storeSesion(): void
    {
        $this->requireRole('admin', 'manager');

        $user     = Session::user();
        $idEvento = (int) $this->input('id_evento');

        $errors = [];
        $nombre = $this->input('nombre');
        $fecha  = $this->input('fecha');

        if (empty($nombre)) $errors[] = 'El nombre de la sesión es obligatorio.';
        if (empty($fecha))  $errors[] = 'La fecha es obligatoria.';

        if (!empty($errors)) {
            Session::flash('error', implode('<br>', $errors));
            $this->redirect('admin/agenda/' . $idEvento);
            return;
        }

        $this->agendaModel->crearSesion([
            'id_evento'  => $idEvento,
            'nombre'     => $nombre,
            'fecha'      => $fecha,
            'lugar'      => $this->input('lugar'),
            'orden'      => (int) $this->input('orden', 1),
            'id_creador' => $user['id'],
        ]);

        Session::flash('success', 'Sesión creada correctamente.');
        $this->redirect('admin/agenda/' . $idEvento);
    }

    // POST /admin/agenda/sesion/update/{id}
    public function updateSesion(string $id): void
    {
        $this->requireRole('admin', 'manager');

        $idEvento = (int) $this->input('id_evento');

        $this->agendaModel->actualizarSesion((int) $id, [
            'nombre' => $this->input('nombre'),
            'fecha'  => $this->input('fecha'),
            'lugar'  => $this->input('lugar'),
            'orden'  => (int) $this->input('orden', 1),
        ]);

        Session::flash('success', 'Sesión actualizada.');
        $this->redirect('admin/agenda/' . $idEvento);
    }

    // POST /admin/agenda/sesion/delete/{id}
    public function deleteSesion(string $id): void
    {
        $this->requireRole('admin', 'manager');

        $idEvento = (int) $this->input('id_evento');
        $this->agendaModel->eliminarSesion((int) $id);

        Session::flash('success', 'Sesión eliminada.');
        $this->redirect('admin/agenda/' . $idEvento);
    }

    // ── ADMIN CRONOGRAMA ─────────────────────────────────────

    // POST /admin/agenda/cronograma/store
    public function storeCronograma(): void
    {
        $this->requireRole('admin', 'manager');

        $user     = Session::user();
        $idEvento = (int) $this->input('id_evento');
        $idSesion = (int) $this->input('id_sesion');
        $sesion   = $this->agendaModel->findSesion($idSesion);

        $errors = [];
        $horaI  = $this->input('hora_i');
        $horaF  = $this->input('hora_f');

        if (empty($horaI)) $errors[] = 'La hora de inicio es obligatoria.';
        if (empty($horaF)) $errors[] = 'La hora de fin es obligatoria.';
        if ($horaI >= $horaF) $errors[] = 'La hora de fin debe ser mayor a la de inicio.';

        if (!empty($errors)) {
            Session::flash('error', implode('<br>', $errors));
            $this->redirect('admin/agenda/' . $idEvento);
            return;
        }

        $this->agendaModel->agregarCronograma([
            'id_evento'   => $idEvento,
            'id_sesion'   => $idSesion,
            'nombre'      => $this->input('nombre'),
            'fecha'       => $sesion['fecha'],
            'hora_i'      => $horaI,
            'hora_f'      => $horaF,
            'lugar'       => $this->input('lugar'),
            'descripcion' => $this->input('descripcion'),
            'id_create'   => $user['id'],
        ]);

        Session::flash('success', 'Ítem agregado al cronograma.');
        $this->redirect('admin/agenda/' . $idEvento);
    }

    // POST /admin/agenda/cronograma/delete/{id}
    public function deleteCronograma(string $id): void
    {
        $this->requireRole('admin', 'manager');

        $idEvento = (int) $this->input('id_evento');
        $this->agendaModel->eliminarCronograma((int) $id);

        Session::flash('success', 'Ítem eliminado del cronograma.');
        $this->redirect('admin/agenda/' . $idEvento);
    }
}
