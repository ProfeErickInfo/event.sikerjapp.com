<?php
// ============================================================
// CONTROLADOR: DocumentosController
// Maneja subida y descarga de documentos del evento
// ============================================================

class DocumentosController extends Controller
{
    private DocumentoModel $documentoModel;
    private EventoModel    $eventoModel;

    public function __construct()
    {
        require_once ROOT_PATH . '/app/Models/DocumentoModel.php';
        require_once ROOT_PATH . '/app/Models/EventoModel.php';
        $this->documentoModel = new DocumentoModel();
        $this->eventoModel    = new EventoModel();
    }

    // ── PÚBLICO ──────────────────────────────────────────────

    // GET /events/{id}/documentos
    public function lista(string $id): void
    {
        $evento = $this->eventoModel->getConCategoria((int) $id);
        if (!$evento) {
            $this->redirect('events');
            return;
        }

        $documentos = $this->documentoModel->getByEvento((int) $id);

        $this->viewWithLayout('documentos/lista', 'layouts/main', [
            'title'      => 'Documentos — ' . $evento['nombre_corto'],
            'evento'     => $evento,
            'documentos' => $documentos,
        ]);
    }

    // ── ADMIN ────────────────────────────────────────────────

    // GET /admin/documentos/{id_evento}
    public function admin(string $id): void
    {
        $this->requireRole('admin', 'manager');

        $evento = $this->eventoModel->getConCategoria((int) $id);
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

        $documentos = $this->documentoModel->getByEvento((int) $id);

        $this->viewWithLayout('admin/documentos/index', 'layouts/main', [
            'title'      => 'Documentos — ' . $evento['nombre_corto'],
            'evento'     => $evento,
            'documentos' => $documentos,
        ]);
    }

    // POST /admin/documentos/upload/{id_evento}
    public function upload(string $id): void
    {
        $this->requireRole('admin', 'manager');

        // Verifica acceso del manager
        $user = Session::user();
        if ($user['tipoU'] == 4) {
            $db   = Database::getInstance()->getConnection();
            $stmt = $db->prepare("SELECT id_admin FROM tbx_eventos WHERE id = ?");
            $stmt->execute([$id]);
            $ev = $stmt->fetch();
            if (!$ev || $ev['id_admin'] != $user['id']) {
                Session::flash('error', 'No tienes permiso para acceder a este evento.');
                $this->redirect('admin/events');
                return;
            }
        }

        $nombre = $this->input('nombre');

        if (empty($nombre)) {
            Session::flash('error', 'El nombre del documento es obligatorio.');
            $this->redirect('admin/documentos/' . $id);
            return;
        }

        if (empty($_FILES['archivo']['name'])) {
            Session::flash('error', 'Debes seleccionar un archivo.');
            $this->redirect('admin/documentos/' . $id);
            return;
        }

        $file    = $_FILES['archivo'];
        $ext     = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $allowed = ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'jpg', 'jpeg', 'png', 'zip'];

        if (!in_array($ext, $allowed)) {
            Session::flash('error', 'Formato no permitido. Usa PDF, Word, Excel, PowerPoint, imagen o ZIP.');
            $this->redirect('admin/documentos/' . $id);
            return;
        }

        if ($file['size'] > 20 * 1024 * 1024) {
            Session::flash('error', 'El archivo no puede superar 20MB.');
            $this->redirect('admin/documentos/' . $id);
            return;
        }

        // Guarda el archivo
        $uploadDir = UPLOADS_PATH . '/documentos/';
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

        $filename    = 'doc_' . $id . '_' . time() . '.' . $ext;
        $destination = $uploadDir . $filename;

        if (!move_uploaded_file($file['tmp_name'], $destination)) {
            Session::flash('error', 'Error al subir el archivo.');
            $this->redirect('admin/documentos/' . $id);
            return;
        }

        $this->documentoModel->subir([
            'id_evento' => (int) $id,
            'nombre'    => $nombre,
            'archivo'   => 'documentos/' . $filename,
        ]);

        Session::flash('success', 'Documento subido correctamente.');
        $this->redirect('admin/documentos/' . $id);
    }

    // POST /admin/documentos/delete/{id}
    public function delete(string $id): void
    {
        $this->requireRole('admin', 'manager');

        $idEvento = (int) $this->input('id_evento');

        // Verifica acceso del manager
        $user = Session::user();
        if ($user['tipoU'] == 4) {
            $db   = Database::getInstance()->getConnection();
            $stmt = $db->prepare("SELECT id_admin FROM tbx_eventos WHERE id = ?");
            $stmt->execute([$idEvento]);
            $ev = $stmt->fetch();
            if (!$ev || $ev['id_admin'] != $user['id']) {
                Session::flash('error', 'No tienes permiso para acceder a este evento.');
                $this->redirect('admin/events');
                return;
            }
        }

        $doc = $this->documentoModel->eliminar((int) $id);

        // Elimina el archivo físico
        if ($doc && !empty($doc['archivo'])) {
            $filePath = UPLOADS_PATH . '/' . $doc['archivo'];
            if (file_exists($filePath)) {
                unlink($filePath);
            }
        }

        $idEvento = (int) $this->input('id_evento');
        Session::flash('success', 'Documento eliminado.');
        $this->redirect('admin/documentos/' . $idEvento);
    }
}
