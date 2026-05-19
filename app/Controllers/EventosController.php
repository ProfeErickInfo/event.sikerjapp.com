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
        $user = Session::user();

        $perPage = 15;
        $page    = max(1, (int) $this->query('page', 1));
        $offset  = ($page - 1) * $perPage;

        // Manager solo ve su evento
        if ($user['tipoU'] == 4) {
            $eventos = $this->eventoModel->raw(
                "SELECT e.*, c.nombre as categoria
                 FROM tbx_eventos e
                 LEFT JOIN tbx_categorias c ON e.id_categoria = c.id
                 WHERE e.id_admin = ?",
                [$user['id']]
            );
            $total = count($eventos);
            $pages = 1;
            $page  = 1;
        } else {
            $eventos = $this->eventoModel->getAdmin($perPage, $offset);
            $total   = $this->eventoModel->countAdmin();
            $pages   = ceil($total / $perPage);
        }

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
            // Crea usuario manager si se proporcionó email
            $emailAdmin = $this->input('email_admin');
            if (!empty($emailAdmin) && filter_var($emailAdmin, FILTER_VALIDATE_EMAIL)) {
                $this->crearManager($id, $emailAdmin);
            }

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

        $this->verificarAcceso($evento);

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

        $this->verificarAcceso($evento);

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

        $emailAdmin = $this->input('email_admin');
        if (!empty($emailAdmin) && filter_var($emailAdmin, FILTER_VALIDATE_EMAIL)) {
            $this->crearManager((int)$id, $emailAdmin);
        }

        Session::flash('success', 'Evento actualizado correctamente.');
        $this->redirect('admin/events');
    }

    // POST /admin/events/delete/{id} — Elimina evento
    public function delete(string $id): void
    {
        $this->requireRole('admin');

        $evento = $this->eventoModel->find((int) $id);
        if (!$evento) {
            Session::flash('error', 'Evento no encontrado.');
            $this->redirect('admin/events');
            return;
        }

        $this->verificarAcceso($evento);

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
            'email_admin'  =>         $this->input('email_admin'),
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

    // GET /admin/certificado/{id}
public function certificado(string $id): void
{
    $this->requireRole('admin', 'manager');

    $evento = $this->eventoModel->find((int) $id);
    if (!$evento) {
        $this->redirect('admin/events');
        return;
    }

    $this->verificarAcceso($evento);

    $this->viewWithLayout('admin/events/certificado', 'layouts/main', [
        'title'  => 'Certificado — ' . $evento['nombre_corto'],
        'evento' => $evento,
    ]);
}

// POST /admin/certificado/plantilla/{id}
public function subirPlantilla(string $id): void
{
    $this->requireRole('admin', 'manager');

    $evento = $this->eventoModel->find((int) $id);
    if (!$evento) {
        Session::flash('error', 'Evento no encontrado.');
        $this->redirect('admin/events');
        return;
    }

    $this->verificarAcceso($evento);

    if (empty($_FILES['plantilla']['name'])) {
        Session::flash('error', 'Debes seleccionar una imagen.');
        $this->redirect('admin/certificado/' . $id);
        return;
    }

    $file    = $_FILES['plantilla'];
    $ext     = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $allowed = ['jpg', 'jpeg', 'png'];

    if (!in_array($ext, $allowed)) {
        Session::flash('error', 'Solo se permiten imágenes JPG o PNG.');
        $this->redirect('admin/certificado/' . $id);
        return;
    }

    if ($file['size'] > 10 * 1024 * 1024) {
        Session::flash('error', 'La imagen no puede superar 10MB.');
        $this->redirect('admin/certificado/' . $id);
        return;
    }

    $uploadDir = UPLOADS_PATH . '/certificados/';
    if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

    $filename    = 'cert_' . $id . '_' . time() . '.' . $ext;
    $destination = $uploadDir . $filename;

    if (!move_uploaded_file($file['tmp_name'], $destination)) {
        Session::flash('error', 'Error al subir la imagen.');
        $this->redirect('admin/certificado/' . $id);
        return;
    }

    $this->eventoModel->update((int) $id, [
        'cert_plantilla' => 'certificados/' . $filename,
    ]);

    Session::flash('success', 'Plantilla subida. Ahora haz clic en la imagen para posicionar el nombre.');
    $this->redirect('admin/certificado/' . $id);
}

// POST /admin/certificado/config/{id}
public function configCertificado(string $id): void
{
    $this->requireRole('admin', 'manager');

    $evento = $this->eventoModel->find((int) $id);
    if (!$evento) {
        Session::flash('error', 'Evento no encontrado.');
        $this->redirect('admin/events');
        return;
    }

    $this->verificarAcceso($evento);

    $this->eventoModel->update((int) $id, [
        'cert_x'          => (float) $this->input('cert_x', 0),
        'cert_x2' => (float) $this->input('cert_x2', 297),
        'cert_y'          => (float) $this->input('cert_y', 0),
        'cert_font_size'  => (int)   $this->input('cert_font_size', 24),
        'cert_font_color' => $this->input('cert_font_color', '000000'),
    ]);

    Session::flash('success', 'Configuración guardada. Genera un PDF de prueba para verificar.');
    $this->redirect('admin/certificado/' . $id);
}

    private function crearManager(int $idEvento, string $email): void
    {
        require_once ROOT_PATH . '/app/Models/UsuarioModel.php';
        $usuarioModel = new UsuarioModel();

        // Si ya existe ese usuario lo asigna al evento
        $stmt = Database::getInstance()->getConnection()->prepare(
            "SELECT id, name FROM wx25_usu WHERE email = ?"
        );
        $stmt->execute([$email]);
        $usuarioExistente = $stmt->fetch();

        if ($usuarioExistente) {
            // Actualiza el id_admin del evento
            $this->eventoModel->update($idEvento, [
                'id_admin'    => $usuarioExistente['id'],
                'email_admin' => $email,
            ]);
            return;
        }

        // Crea nuevo usuario manager
        $password = substr(str_shuffle('abcdefghjkmnpqrstuvwxyzABCDEFGHJKMNPQRSTUVWXYZ23456789@#$!'), 0, 10);
        $nombre   = 'Manager ' . $email;

        $idUsuario = $usuarioModel->register([
            'tipoU' => 4,
            'name'  => $nombre,
            'email' => $email,
            'nickz' => $email,
            'pazz'  => $password,
        ]);

        // Asigna el manager al evento
        $this->eventoModel->update($idEvento, [
            'id_admin'    => $idUsuario,
            'email_admin' => $email,
        ]);

        // Envía correo con credenciales
        require_once ROOT_PATH . '/core/Mailer.php';
        $mailer = new Mailer();
        $mailer->sendWelcome($email, $nombre, $password);
    }

    // POST /admin/events/eliminar-manager/{id}
    public function eliminarManager(string $id): void
    {
        $this->requireRole('admin');

        $evento = $this->eventoModel->find((int) $id);
        if (!$evento || empty($evento['id_admin'])) {
            Session::flash('error', 'Este evento no tiene manager asignado.');
            $this->redirect('admin/events');
            return;
        }

        $db = Database::getInstance()->getConnection();

        // Elimina completamente el usuario manager
        $stmt = $db->prepare("DELETE FROM wx25_usu WHERE id = ? AND tipoU = 4");
        $stmt->execute([$evento['id_admin']]);

        // Desasigna del evento
        $this->eventoModel->update((int) $id, [
            'id_admin'    => null,
            'email_admin' => null,
        ]);

        Session::flash('success', 'Manager eliminado. El correo queda libre para registrarse como asistente.');
        $this->redirect('admin/events');
    }

    // Verifica que el usuario tenga acceso al evento
    private function verificarAcceso(array $evento): void
    {
        $user = Session::user();
        if ($user['tipoU'] == 4 && $evento['id_admin'] != $user['id']) {
            Session::flash('error', 'No tienes permiso para acceder a este evento.');
            $this->redirect('admin/events');
        }
    }
}
