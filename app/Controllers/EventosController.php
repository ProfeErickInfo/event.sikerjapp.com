<?php
// ============================================================
// CONTROLADOR: EventosController
// Lista pública, detalle y gestión admin de eventos
// ============================================================

class EventosController extends Controller
{
    private EventoModel $eventoModel;

    public function __construct()
    {
        require_once ROOT_PATH . '/app/Models/EventoModel.php';
        $this->eventoModel = new EventoModel();
    }

    // ── PÚBLICOS ─────────────────────────────────────────────

    // GET /events — Lista pública de eventos
    public function public(): void
    {
        $perPage = 9;
        $page    = max(1, (int) $this->query('page', 1));
        $offset  = ($page - 1) * $perPage;

        $eventos = $this->eventoModel->getPublicos($perPage, $offset);
        $total   = $this->eventoModel->countPublicos();
        $pages   = ceil($total / $perPage);

        $this->viewWithLayout('events/index', 'layouts/main', [
            'title'   => 'Eventos',
            'eventos' => $eventos,
            'page'    => $page,
            'pages'   => $pages,
            'total'   => $total,
        ]);
    }

    // GET /events/{id} — Detalle de un evento
    public function show(string $id): void
    {
        $evento = $this->eventoModel->getConCategoria((int) $id);

        if (!$evento) {
            Session::flash('error', 'Evento no encontrado.');
            $this->redirect('events');
            return;
        }

        // Verifica si el usuario está inscrito
        $inscrito = false;
        if (Session::isLoggedIn()) {
            $user     = Session::user();
           // $inscrito = $this->eventoModel->isInscrito($user['id'], (int) $id);
                  require_once ROOT_PATH . '/app/Models/InscripcionModel.php';
                    $inscripcionModel = new InscripcionModel();
                  $inscrito = $inscripcionModel->getInscripcionUsuario($user['id'], (int) $id);   }

        $this->viewWithLayout('events/show', 'layouts/main', [
            'title'    => $evento['nombre_corto'],
            'evento'   => $evento,
            'inscrito' => $inscrito,
        ]);
    }

    // ── ADMIN ────────────────────────────────────────────────

    // GET /admin/events — Lista admin
    public function index(): void
    {
        $this->requireRole('admin', 'manager');

        $perPage = 15;
        $page    = max(1, (int) $this->query('page', 1));
        $offset  = ($page - 1) * $perPage;

        $eventos = $this->eventoModel->getAdmin($perPage, $offset);
        $total   = $this->eventoModel->countAdmin();
        $pages   = ceil($total / $perPage);

        $this->viewWithLayout('admin/events/index', 'layouts/main', [
            'title'   => 'Gestionar Eventos',
            'eventos' => $eventos,
            'page'    => $page,
            'pages'   => $pages,
            'total'   => $total,
        ]);
    }

    // GET /admin/events/create — Formulario crear evento
    public function create(): void
    {
        $this->requireRole('admin', 'manager');
        $categorias = $this->eventoModel->getCategorias();

        $this->viewWithLayout('admin/events/form', 'layouts/main', [
            'title'      => 'Crear Evento',
            'evento'     => null,
            'categorias' => $categorias,
        ]);
    }

    // POST /admin/events/store — Guarda nuevo evento
    public function store(): void
    {
        $this->requireRole('admin', 'manager');

        $data   = $this->getFormData();
        $errors = $this->validate($data);

        if (!empty($errors)) {
            Session::flash('error', implode('<br>', $errors));
            $this->redirect('admin/events/create');
            return;
        }

        // Maneja la imagen
        $data['pic'] = 'no-disponible.jpeg';
        if (!empty($_FILES['pic']['name'])) {
            $img = uploadImage($_FILES['pic'], 'events');
            if ($img) {
                $data['pic'] = $img;
            } else {
                Session::flash('error', 'La imagen no es válida (máx 5MB, formatos: jpg, png, gif, webp).');
                $this->redirect('admin/events/create');
                return;
            }
        }

        $user        = Session::user();
        $data['usu_reg'] = $user['id'];
        $data['fec_reg'] = date('Y-m-d');

        $id = $this->eventoModel->insert($data);

        if ($id) {
            Session::flash('success', 'Evento creado exitosamente.');
            $this->redirect('admin/events');
        } else {
            Session::flash('error', 'Error al crear el evento.');
            $this->redirect('admin/events/create');
        }
    }

    // GET /admin/events/edit/{id} — Formulario editar evento
    public function edit(string $id): void
    {
        $this->requireRole('admin', 'manager');

        $evento = $this->eventoModel->find((int) $id);
        if (!$evento) {
            Session::flash('error', 'Evento no encontrado.');
            $this->redirect('admin/events');
            return;
        }

        $categorias = $this->eventoModel->getCategorias();

        $this->viewWithLayout('admin/events/form', 'layouts/main', [
            'title'      => 'Editar Evento',
            'evento'     => $evento,
            'categorias' => $categorias,
        ]);
    }

    // POST /admin/events/update/{id} — Actualiza evento
    public function update(string $id): void
    {
        $this->requireRole('admin', 'manager');

        $evento = $this->eventoModel->find((int) $id);
        if (!$evento) {
            Session::flash('error', 'Evento no encontrado.');
            $this->redirect('admin/events');
            return;
        }

        $data   = $this->getFormData();
        $errors = $this->validate($data);

        if (!empty($errors)) {
            Session::flash('error', implode('<br>', $errors));
            $this->redirect('admin/events/edit/' . $id);
            return;
        }

        // Maneja imagen si se sube una nueva
        if (!empty($_FILES['pic']['name'])) {
            $img = uploadImage($_FILES['pic'], 'events');
            if ($img) {
                $data['pic'] = $img;
            }
        }

        $this->eventoModel->update((int) $id, $data);
        Session::flash('success', 'Evento actualizado correctamente.');
        $this->redirect('admin/events');
    }

    // POST /admin/events/delete/{id} — Elimina evento
    public function delete(string $id): void
    {
        $this->requireRole('admin');
        $this->eventoModel->delete((int) $id);
        Session::flash('success', 'Evento eliminado.');
        $this->redirect('admin/events');
    }

    // ── HELPERS PRIVADOS ─────────────────────────────────────

    private function getFormData(): array
    {
        return [
            'id_categoria' => (int)   $this->input('id_categoria'),
            'nombre_corto' =>         $this->input('nombre_corto'),
            'descripcion'  =>         $this->input('descripcion'),
            'fecha'        =>         $this->input('fecha'),
            'fecha2'       =>         $this->input('fecha2'),
            'inscripcion'  => (int)   $this->input('inscripcion', 1),
            'valor_inscripcion' => (float) $this->input('valor_inscripcion', 0),
            'estado'       => (int)   $this->input('estado', 1),
            'edicion'      => (int)   $this->input('edicion', date('Y')),
            'ruta'         =>         $this->input('ruta', 'No Aplica'),
        ];
    }

    private function validate(array $data): array
    {
        $errors = [];
        if (empty($data['nombre_corto']))
            $errors[] = 'El nombre del evento es obligatorio.';
        if (empty($data['fecha']))
            $errors[] = 'La fecha de inicio es obligatoria.';
        if (empty($data['fecha2']))
            $errors[] = 'La fecha de fin es obligatoria.';
        if ($data['fecha'] > $data['fecha2'])
            $errors[] = 'La fecha de fin debe ser igual o posterior a la de inicio.';
        if (empty($data['id_categoria']))
            $errors[] = 'La categoría es obligatoria.';
        return $errors;
    }
}
