<?php
// ============================================================
// CONTROLADOR: CertificadoController
// Genera certificados PDF con plantilla personalizada
// ============================================================

class CertificadoController extends Controller
{
    private EventoModel     $eventoModel;
    private CredencialModel $credencialModel;

    public function __construct()
    {
        require_once ROOT_PATH . '/app/Models/EventoModel.php';
        require_once ROOT_PATH . '/app/Models/CredencialModel.php';
        $this->eventoModel     = new EventoModel();
        $this->credencialModel = new CredencialModel();
    }

    // GET /certificate/{id_usuario}/{id_evento} — Certificado individual
    public function individual(string $idUsuario, string $idEvento): void
    {
        $this->requireAuth();

        $evento = $this->eventoModel->find((int) $idEvento);
        if (!$evento || empty($evento['cert_plantilla'])) {
            Session::flash('error', 'El certificado no está disponible para este evento.');
            $this->redirect('events/' . $idEvento);
            return;
        }

        // Obtiene datos del usuario
        $db   = Database::getInstance()->getConnection();
        $stmt = $db->prepare("SELECT id, name FROM wx25_usu WHERE id = ?");
        $stmt->execute([$idUsuario]);
        $usuario = $stmt->fetch();

        if (!$usuario) {
            Session::flash('error', 'Usuario no encontrado.');
            $this->redirect('events/' . $idEvento);
            return;
        }

        $this->generarPDF($usuario['name'], $evento);
    }

    // GET /certificate/participante/{id_participante}/{id_evento}
    public function participante(string $idParticipante, string $idEvento): void
    {
        $this->requireAuth();

        $evento = $this->eventoModel->find((int) $idEvento);
        if (!$evento || empty($evento['cert_plantilla'])) {
            Session::flash('error', 'El certificado no está disponible.');
            $this->redirect('events/' . $idEvento);
            return;
        }

        $db   = Database::getInstance()->getConnection();
        $stmt = $db->prepare("SELECT id, nombre as name FROM tbx_participantes WHERE id = ?");
        $stmt->execute([$idParticipante]);
        $participante = $stmt->fetch();

        if (!$participante) {
            Session::flash('error', 'Participante no encontrado.');
            $this->redirect('events/' . $idEvento);
            return;
        }

        $this->generarPDF($participante['name'], $evento);
    }

    // GET /certificate/preview/{id_evento} — Vista previa para el admin
   public function preview(string $idEvento): void
{
    $this->requireRole('admin', 'manager');

    $evento = $this->eventoModel->find((int) $idEvento);
    if (!$evento || empty($evento['cert_plantilla'])) {
        Session::flash('error', 'No hay plantilla configurada.');
        $this->redirect('admin/certificado/' . $idEvento);
        return;
    }

    $this->generarPDF('Nombre del Asistente (Preview)', $evento);
}

    // ── GENERADOR PDF ────────────────────────────────────────
    private function generarPDF(string $nombre, array $evento): void
    {
        require_once ROOT_PATH . '/core/fpdf/FPDFExtended.php';

        $plantillaPath = UPLOADS_PATH . '/' . $evento['cert_plantilla'];

        if (!file_exists($plantillaPath)) {
            die('Plantilla no encontrada.');
        }

        // Detecta extensión para FPDF
        $ext = strtolower(pathinfo($plantillaPath, PATHINFO_EXTENSION));
        $imgType = ($ext === 'png') ? 'PNG' : 'JPEG';

        // Obtiene dimensiones de la imagen
        [$imgW, $imgH] = getimagesize($plantillaPath);
        $ratio = $imgW / $imgH;

        // PDF A4 horizontal
        $pdf = new FPDFExtended('L', 'mm', 'A4');
        $pdf->AddPage();
        $pdf->SetMargins(0, 0, 0);
        $pdf->SetAutoPageBreak(false);

        // Coloca la imagen como fondo completo
        $pdf->Image($plantillaPath, 0, 0, 297, 210, $imgType);

        // Configura el texto del nombre
        $x         = (float) ($evento['cert_x']         ?? 148.5); // centro por defecto
        $y         = (float) ($evento['cert_y']         ?? 105);
        $fontSize  = (int)   ($evento['cert_font_size'] ?? 24);
        $fontColor = $evento['cert_font_color']          ?? '000000';

        // Convierte color hex a RGB
        $r = hexdec(substr($fontColor, 0, 2));
        $g = hexdec(substr($fontColor, 2, 2));
        $b = hexdec(substr($fontColor, 4, 2));

        $pdf->SetFont('Helvetica', 'B', $fontSize);
        $pdf->SetTextColor($r, $g, $b);

        // Convierte nombre a ISO para FPDF
       $nombreIso = $this->textoParaPDF($nombre);

       // Centra el texto entre X inicio y X fin
$x2    = (float) ($evento['cert_x2'] ?? 297);
$ancho = $x2 - $x;
$pdf->SetXY($x, $y);
$pdf->Cell($ancho, 0, $nombreIso, 0, 0, 'C');

        $pdf->Output('I', 'certificado_' . slug($nombre) . '.pdf');
        exit;
    }

    // ── HELPER ───────────────────────────────────────────────
    /**
     * Convierte texto UTF-8 a ISO-8859-1 para compatibilidad con FPDF
     */
    private function textoParaPDF(string $texto): string
    {
        return iconv('UTF-8', 'ISO-8859-1//TRANSLIT', $texto);
    }
}
