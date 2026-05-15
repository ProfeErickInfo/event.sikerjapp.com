<?php
// ============================================================
// CONTROLADOR: InscripcionController
// Maneja inscripciones individuales y de delegaciones
// ============================================================

class InscripcionController extends Controller
{
    private InscripcionModel $inscripcionModel;
    private DelegacionModel  $delegacionModel;
    private EventoModel      $eventoModel;

    public function __construct()
    {
        require_once ROOT_PATH . '/app/Models/InscripcionModel.php';
        require_once ROOT_PATH . '/app/Models/DelegacionModel.php';
        require_once ROOT_PATH . '/app/Models/EventoModel.php';
        $this->inscripcionModel = new InscripcionModel();
        $this->delegacionModel  = new DelegacionModel();
        $this->eventoModel      = new EventoModel();
    }

    // ── INDIVIDUAL ───────────────────────────────────────────

    // GET /events/{id}/inscribirse
    public function form(string $idEvento): void
    {
        $this->requireAuth();

        $evento = $this->eventoModel->getConCategoria((int) $idEvento);
        if (!$evento || $evento['inscripcion'] != 1) {
            Session::flash('error', 'Las inscripciones para este evento están cerradas.');
            $this->redirect('events/' . $idEvento);
            return;
        }

        $user = Session::user();

        // Si es delegación, redirige al flujo de delegación
        if ($user['tipoU'] == 1) {
            $this->redirect('events/' . $idEvento . '/delegacion');
            return;
        }

        // Verifica si ya está inscrito
        if ($this->inscripcionModel->isInscrito($user['id'], (int) $idEvento)) {
            Session::flash('warning', 'Ya estás inscrito en este evento.');
            $this->redirect('events/' . $idEvento);
            return;
        }

        // Obtiene tipos de documento
        $db       = Database::getInstance()->getConnection();
        $tipoDocs = $db->query("SELECT * FROM tbx_tipo_documento ORDER BY id")->fetchAll();

        $this->viewWithLayout('inscripciones/form_individual', 'layouts/main', [
            'title'    => 'Inscripción — ' . $evento['nombre_corto'],
            'evento'   => $evento,
            'tipoDocs' => $tipoDocs,
            'user'     => $user,
        ]);
    }

    // POST /events/{id}/inscribirse
    public function store(string $idEvento): void
    {
        $this->requireAuth();

        $evento = $this->eventoModel->getConCategoria((int) $idEvento);
        if (!$evento || $evento['inscripcion'] != 1) {
            Session::flash('error', 'Las inscripciones están cerradas.');
            $this->redirect('events/' . $idEvento);
            return;
        }

        $user = Session::user();

        if ($this->inscripcionModel->isInscrito($user['id'], (int) $idEvento)) {
            Session::flash('warning', 'Ya estás inscrito en este evento.');
            $this->redirect('events/' . $idEvento);
            return;
        }

        // Validaciones
        $errors = [];
        $nombre   = $this->input('nombre');
        $tipDoc   = (int) $this->input('tipo_doc');
        $doc      = $this->input('documento');
        $tel      = $this->input('telefono');
        $nac      = $this->input('nacionalidad', 'Colombiana');
        $fechaNac = $this->input('fecha_nac');
        $genero   = (int) $this->input('genero', 1);

        if (empty($nombre))   $errors[] = 'El nombre completo es obligatorio.';
        if (empty($doc))      $errors[] = 'El número de documento es obligatorio.';
        if (empty($tel))      $errors[] = 'El teléfono móvil es obligatorio.';
        if (empty($fechaNac)) $errors[] = 'La fecha de nacimiento es obligatoria.';

        // Verifica mayoría de edad
        if (!empty($fechaNac)) {
            $edad = (int) date_diff(date_create($fechaNac), date_create('today'))->y;
            if ($edad < 18) {
                $errors[] = 'Debes ser mayor de 18 años para inscribirte individualmente.';
            }
        }

        if (!empty($errors)) {
            Session::flash('error', implode('<br>', $errors));
            $this->redirect('events/' . $idEvento . '/inscribirse');
            return;
        }

        $id = $this->inscripcionModel->inscribirIndividual([
            'id_evento'    => (int) $idEvento,
            'id_usuario'   => $user['id'],
            'valor'        => $evento['valor_inscripcion'] ?? 0,
            'nombre'       => $nombre,
            'tipo_doc'     => $tipDoc,
            'documento'    => $doc,
            'telefono'     => $tel,
            'nacionalidad' => $nac,
            'fecha_nac'    => $fechaNac,
            'genero'       => $genero,
        ]);

        if ($id) {
            Session::flash('success', '¡Inscripción realizada! Queda pendiente de aprobación.');
            $this->redirect('events/' . $idEvento);
        } else {
            Session::flash('error', 'Error al procesar la inscripción.');
            $this->redirect('events/' . $idEvento . '/inscribirse');
        }
    }

    // ── DELEGACIÓN ───────────────────────────────────────────

    // GET /events/{id}/delegacion
    public function delegacion(string $idEvento): void
    {
        $this->requireAuth();

        $user   = Session::user();
        $evento = $this->eventoModel->getConCategoria((int) $idEvento);

        if (!$evento) {
            $this->redirect('events');
            return;
        }

        // Obtiene o crea la delegación del usuario
        $delegacion = $this->delegacionModel->getByUsuario($user['id']);

        if (!$delegacion) {
            // Primera vez — muestra formulario para crear delegación
            $this->viewWithLayout('inscripciones/crear_delegacion', 'layouts/main', [
                'title'  => 'Crear Delegación',
                'evento' => $evento,
            ]);
            return;
        }

        // Tiene delegación — muestra participantes con estado de inscripción
        $participantes = $this->delegacionModel->getParticipantesConInscripcion(
            $delegacion['id'], (int) $idEvento
        );

        // Obtiene tipos de documento para el formulario de agregar
        $db       = Database::getInstance()->getConnection();
        $tipoDocs = $db->query("SELECT * FROM tbx_tipo_documento ORDER BY id")->fetchAll();

        $this->viewWithLayout('inscripciones/delegacion', 'layouts/main', [
            'title'         => 'Inscripción Delegación — ' . $evento['nombre_corto'],
            'evento'        => $evento,
            'delegacion'    => $delegacion,
            'participantes' => $participantes,
            'tipoDocs'      => $tipoDocs,
        ]);
    }

    // POST /delegacion/crear
    public function crearDelegacion(): void
    {
        $this->requireAuth();

        $user    = Session::user();
        $nombre  = $this->input('nombre');
        $rep     = $this->input('representante');
        $tel     = $this->input('telefono');
        $email   = $this->input('email');
        $ciudad  = $this->input('ciudad');
        $idEvento = (int) $this->input('id_evento');

        if (empty($nombre)) {
            Session::flash('error', 'El nombre de la delegación es obligatorio.');
            $this->redirect('events/' . $idEvento . '/delegacion');
            return;
        }

        $this->delegacionModel->crear([
            'id_usuario'    => $user['id'],
            'nombre'        => $nombre,
            'representante' => $rep,
            'telefono'      => $tel,
            'email'         => $email,
            'ciudad'        => $ciudad,
        ]);

        Session::flash('success', '¡Delegación creada! Ahora agrega tus participantes.');
        $this->redirect('events/' . $idEvento . '/delegacion');
    }

    // POST /delegacion/participante/agregar
    public function agregarParticipante(): void
    {
        $this->requireAuth();

        $user       = Session::user();
        $idEvento   = (int) $this->input('id_evento');
        $delegacion = $this->delegacionModel->getByUsuario($user['id']);

        if (!$delegacion) {
            Session::flash('error', 'No tienes una delegación registrada.');
            $this->redirect('events/' . $idEvento . '/delegacion');
            return;
        }

        $errors  = [];
        $nombre  = $this->input('nombre');
        $doc     = $this->input('documento');
        $tipDoc  = (int) $this->input('tipo_doc', 1);
        $email   = $this->input('email');
        $tel     = $this->input('telefono');
        $nac     = $this->input('nacionalidad', 'Colombiana');
        $fechaNac= $this->input('fecha_nac');
        $genero  = (int) $this->input('genero', 1);

        if (empty($nombre)) $errors[] = 'El nombre es obligatorio.';
        if (empty($doc))    $errors[] = 'El documento es obligatorio.';

        if (!empty($errors)) {
            Session::flash('error', implode('<br>', $errors));
            $this->redirect('events/' . $idEvento . '/delegacion');
            return;
        }

        $this->delegacionModel->agregarParticipante([
            'id_delegacion' => $delegacion['id'],
            'nombre'        => $nombre,
            'tipo_doc'      => $tipDoc,
            'documento'     => $doc,
            'email'         => $email,
            'telefono'      => $tel,
            'nacionalidad'  => $nac,
            'fecha_nac'     => $fechaNac,
            'genero'        => $genero,
        ]);

        Session::flash('success', 'Participante agregado correctamente.');
        $this->redirect('events/' . $idEvento . '/delegacion');
    }

    // POST /delegacion/inscribir/masivo
    public function inscribirMasivo(): void
    {
        $this->requireAuth();

        $user       = Session::user();
        $idEvento   = (int) $this->input('id_evento');
        $ids        = $_POST['participantes'] ?? [];
        $delegacion = $this->delegacionModel->getByUsuario($user['id']);
        $evento     = $this->eventoModel->find($idEvento);

        if (!$delegacion || empty($ids)) {
            Session::flash('error', 'Selecciona al menos un participante.');
            $this->redirect('events/' . $idEvento . '/delegacion');
            return;
        }

        $count = $this->inscripcionModel->inscribirMasivo(
            $idEvento,
            $delegacion['id'],
            $ids,
            $evento['valor_inscripcion'] ?? 0
        );

        Session::flash('success', "{$count} participante(s) inscrito(s) correctamente.");
        $this->redirect('events/' . $idEvento . '/delegacion');
    }

// GET /delegacion/participante/editar/{id}
public function editarParticipante(string $id): void
{
    $this->requireAuth();
    $idEvento    = (int) $this->query('evento');
    $participante = $this->delegacionModel->findParticipante((int) $id);

    if (!$participante) {
        Session::flash('error', 'Participante no encontrado.');
        $this->redirect('events/' . $idEvento . '/delegacion');
        return;
    }

    $db       = Database::getInstance()->getConnection();
    $tipoDocs = $db->query("SELECT * FROM tbx_tipo_documento ORDER BY id")->fetchAll();

    $this->viewWithLayout('inscripciones/editar_participante', 'layouts/main', [
        'title'        => 'Editar Participante',
        'participante' => $participante,
        'tipoDocs'     => $tipoDocs,
        'idEvento'     => $idEvento,
    ]);
}

// POST /delegacion/participante/actualizar/{id}
public function actualizarParticipante(string $id): void
{
    $this->requireAuth();
    $idEvento = (int) $this->input('id_evento');

    $this->delegacionModel->actualizarParticipante((int) $id, [
        'nombre'       => $this->input('nombre'),
        'tipo_doc'     => (int) $this->input('tipo_doc', 1),
        'documento'    => $this->input('documento'),
        'email'        => $this->input('email'),
        'telefono'     => $this->input('telefono'),
        'nacionalidad' => $this->input('nacionalidad', 'Colombiana'),
        'fecha_nac'    => $this->input('fecha_nac'),
        'genero'       => (int) $this->input('genero', 1),
    ]);

    Session::flash('success', 'Participante actualizado correctamente.');
    $this->redirect('events/' . $idEvento . '/delegacion');
}






    
    // POST /delegacion/participante/eliminar/{id}
    public function eliminarParticipante(string $id): void
    {
        $this->requireAuth();
        $idEvento = (int) $this->input('id_evento');
        $this->delegacionModel->eliminarParticipante((int) $id);
        Session::flash('success', 'Participante eliminado.');
        $this->redirect('events/' . $idEvento . '/delegacion');
    }

    // ── ADMIN ────────────────────────────────────────────────

    // GET /admin/inscripciones
    public function index(): void
    {
        $this->requireRole('admin', 'admin_torneo');

        $idEvento = (int) $this->query('evento', 0);
        $db       = Database::getInstance()->getConnection();
        $eventos  = $db->query(
            "SELECT id, nombre_corto FROM tbx_eventos ORDER BY fecha DESC"
        )->fetchAll();

        $inscripciones = [];
        $stats         = [];
        $eventoActual  = null;

        if ($idEvento) {
            $inscripciones = $this->inscripcionModel->getByEvento($idEvento);
            $stats         = $this->inscripcionModel->getStats($idEvento);
            $eventoActual  = $this->eventoModel->find($idEvento);
        }

        $this->viewWithLayout('admin/inscripciones/index', 'layouts/main', [
            'title'         => 'Inscripciones',
            'eventos'       => $eventos,
            'inscripciones' => $inscripciones,
            'stats'         => $stats,
            'eventoActual'  => $eventoActual,
            'idEvento'      => $idEvento,
        ]);
    }

    // POST /admin/inscripciones/aprobar/{id}
    public function aprobar(string $id): void
    {
        $this->requireRole('admin', 'admin_torneo');
        $idEvento = (int) $this->input('id_evento');
        $this->inscripcionModel->aprobar((int) $id);
        Session::flash('success', 'Inscripción aprobada.');
        $this->redirect('admin/inscripciones?evento=' . $idEvento);
    }

    // POST /admin/inscripciones/cancelar/{id}
    public function cancelar(string $id): void
    {
        $this->requireRole('admin', 'admin_torneo');
        $idEvento = (int) $this->input('id_evento');
        $this->inscripcionModel->cancelar((int) $id);
        Session::flash('success', 'Inscripción cancelada.');
        $this->redirect('admin/inscripciones?evento=' . $idEvento);
    }

// POST /inscripciones/cancelar/{id}
public function cancelarPropia(string $id): void
{
    $this->requireAuth();

    $user        = Session::user();
    $idEvento    = (int) $this->input('id_evento');
    $inscripcion = $this->inscripcionModel->find((int) $id);

    // Verifica que la inscripción pertenezca al usuario
    if (!$inscripcion || $inscripcion['id_usuario'] != $user['id']) {
        Session::flash('error', 'No tienes permiso para cancelar esta inscripción.');
        $this->redirect('events/' . $idEvento);
        return;
    }

    // Solo se puede cancelar si está Pendiente
    if ($inscripcion['estado'] != 0) {
        Session::flash('error', 'Solo puedes cancelar inscripciones pendientes.');
        $this->redirect('events/' . $idEvento);
        return;
    }

    $this->inscripcionModel->cancelar((int) $id);
    Session::flash('success', 'Inscripción cancelada correctamente.');
    $this->redirect('events/' . $idEvento);
}

// POST /inscripciones/cancelar/participante/{id}
public function cancelarParticipante(string $id): void
{
    $this->requireAuth();

    $user        = Session::user();
    $idEvento    = (int) $this->input('id_evento');
    $inscripcion = $this->inscripcionModel->find((int) $id);

    if (!$inscripcion || $inscripcion['id_usuario'] != $user['id'] && $inscripcion['estado'] != 0) {
        Session::flash('error', 'No puedes cancelar esta inscripción.');
        $this->redirect('events/' . $idEvento . '/delegacion');
        return;
    }

    $this->inscripcionModel->cancelar((int) $id);
    Session::flash('success', 'Inscripción cancelada correctamente.');
    $this->redirect('events/' . $idEvento . '/delegacion');
}

}
