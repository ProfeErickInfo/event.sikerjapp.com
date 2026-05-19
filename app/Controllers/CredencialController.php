<?php
// ============================================================
// CONTROLADOR: CredencialController
// Genera credenciales PDF con QR
// ============================================================

class CredencialController extends Controller
{
    private CredencialModel $credencialModel;
    private EventoModel     $eventoModel;

    public function __construct()
    {
        require_once ROOT_PATH . '/app/Models/CredencialModel.php';
        require_once ROOT_PATH . '/app/Models/EventoModel.php';
        $this->credencialModel = new CredencialModel();
        $this->eventoModel     = new EventoModel();
    }
private function textoParaPDF(string $texto, bool $mayusculas = true): string
{
    if ($mayusculas) $texto = mb_strtoupper($texto, 'UTF-8');
    
    $tildes = [
        'Á'=>'A','É'=>'E','Í'=>'I','Ó'=>'O','Ú'=>'U',
        'á'=>'a','é'=>'e','í'=>'i','ó'=>'o','ú'=>'u',
        'Ñ'=>'N','ñ'=>'n','Ü'=>'U','ü'=>'u',
        'À'=>'A','È'=>'E','Ì'=>'I','Ò'=>'O','Ù'=>'U',
    ];
    $texto = strtr($texto, $tildes);
    return iconv('UTF-8', 'ISO-8859-1//TRANSLIT', $texto);
}
    // ── CREDENCIAL INDIVIDUAL ────────────────────────────────

    // GET /credential/{id_usuario}/{id_evento}
    public function credencial(string $idUsuario, string $idEvento): void
    {
        $this->requireAuth();

        $datos = $this->credencialModel->getDatosIndividual((int) $idUsuario, (int) $idEvento);

        if (!$datos) {
            Session::flash('error', 'No se encontró una inscripción aprobada.');
            $this->redirect('dashboard');
            return;
        }

        // Genera el QR
        $qrData = $idUsuario . '|' . $idEvento . '|individual';
        $qrPath = $this->generarQR($qrData, 'qr_' . $idUsuario . '_' . $idEvento);

        // Genera el PDF
        $this->generarPDFCredencial($datos, $qrPath);
    }

    // ── CREDENCIAL PARTICIPANTE DE DELEGACIÓN ────────────────

    // GET /credential/participante/{id_participante}/{id_evento}
    public function credencialParticipante(string $idParticipante, string $idEvento): void
    {
        $this->requireAuth();

        $datos = $this->credencialModel->getDatosParticipante((int) $idParticipante, (int) $idEvento);

        if (!$datos) {
            Session::flash('error', 'No se encontró una inscripción aprobada.');
            $this->redirect('dashboard');
            return;
        }

        // Genera el QR
        $qrData = $idParticipante . '|' . $idEvento . '|participante';
        $qrPath = $this->generarQR($qrData, 'qr_p' . $idParticipante . '_' . $idEvento);

        // Genera el PDF
        $this->generarPDFCredencial($datos, $qrPath);
    }

    // ── ADMIN: LISTA DE CREDENCIALES ─────────────────────────

    // GET /admin/credenciales/{id_evento}
    public function admin(string $idEvento): void
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

        $lista = $this->credencialModel->getListaCredenciales((int) $idEvento);

        $this->viewWithLayout('admin/credenciales/index', 'layouts/main', [
            'title'   => 'Credenciales — ' . $evento['nombre_corto'],
            'evento'  => $evento,
            'lista'   => $lista,
        ]);
    }

    // POST /admin/credenciales/aprobar
    public function aprobar(): void
    {
        $this->requireRole('admin', 'manager');

        $user         = Session::user();
        $idEvento     = (int) $this->input('id_evento');
        $idUsuario    = (int) $this->input('id_usuario');
        $tipo         = (int) $this->input('tipo', 1); // 1=individual, 2=participante

        $this->credencialModel->aprobarCredencial($idUsuario, $idEvento, $user['id'], $tipo);

        Session::flash('success', 'Credencial aprobada.');
        $this->redirect('admin/credenciales/' . $idEvento);
    }

    // ── HELPERS ──────────────────────────────────────────────

    // Genera imagen QR y devuelve la ruta
    private function generarQR(string $data, string $nombre): string
    {
        require_once ROOT_PATH . '/core/qr/qrlib.php';

        $dir  = UPLOADS_PATH . '/qr/';
        if (!is_dir($dir)) mkdir($dir, 0755, true);

        $path = $dir . $nombre . '.png';

        // Genera QR solo si no existe o es diferente
        if (!file_exists($path)) {
            QRcode::png($data, $path, QR_ECLEVEL_M, 6, 2);
        }

        return $path;
    }

    // Genera el PDF de la credencial
   
private function generarPDFCredencial(array $datos, string $qrPath): void
{
   require_once ROOT_PATH . '/core/fpdf/FPDFExtended.php';
   $pdf = new FPDFExtended('P', 'mm', [54, 90]);

   
    $pdf->AddPage();
    $pdf->SetMargins(0, 0, 0);
    $pdf->SetAutoPageBreak(false);

    // ── Fondo completo ──
    $pdf->SetFillColor(240, 242, 248);
    $pdf->Rect(0, 0, 54, 90, 'F');

    // ── Franja superior degradada simulada ──
    $pdf->SetFillColor(26, 32, 53);
    $pdf->Rect(0, 0, 54, 28, 'F');

    // Detalle decorativo — línea de acento
    $pdf->SetFillColor(78, 110, 210);
    $pdf->Rect(0, 26, 54, 2, 'F');

    // Círculos decorativos fondo (simulan diseño moderno)
    $pdf->SetFillColor(45, 58, 94);
    $pdf->Ellipse(42, 5, 14, 14, 'F');
    $pdf->SetFillColor(35, 45, 80);
    $pdf->Ellipse(-2, 20, 10, 10, 'F');

    // ── APP NAME pequeño arriba ──
    $pdf->SetFont('Helvetica', '', 5);
    $pdf->SetTextColor(120, 145, 200);
    $pdf->SetXY(2, 3);
    $pdf->Cell(50, 3, 'SIKERJAPP EVENTOS', 0, 0, 'L');

    // ── Nombre del evento ──
    $pdf->SetFont('Helvetica', 'B', 8);
    $pdf->SetTextColor(255, 255, 255);
    $pdf->SetXY(2, 8);
    $pdf->MultiCell(45, 5,
        iconv('UTF-8', 'ISO-8859-1//TRANSLIT', strtoupper($datos['evento_nombre'])),
        0, 'L');

    // ── Fechas ──
    $pdf->SetFont('Helvetica', '', 5.5);
    $pdf->SetTextColor(150, 175, 220);
    $fechaStr = date('d/m/Y', strtotime($datos['evento_fecha']));
    if ($datos['evento_fecha'] !== $datos['evento_fecha2']) {
      $fechaStr .= ' - ' . date('d/m/Y', strtotime($datos['evento_fecha2']));
    }
    $pdf->SetXY(2, 22);
    $pdf->Cell(50, 3, $fechaStr, 0, 0, 'L');

    // ── QR centrado sobre fondo blanco ──
    $pdf->SetFillColor(255, 255, 255);
    $pdf->RoundedRect( 14, 31, 26, 26, 2, 'F');

    if (file_exists($qrPath)) {
        $pdf->Image($qrPath, 16, 33, 22, 22);
    }

    // ── Línea separadora ──
    $pdf->SetFillColor(78, 110, 210);
    $pdf->Rect(2, 60, 50, 0.8, 'F');

    // ── Nombre del asistente ──
    $pdf->SetFont('Helvetica', 'B', 9);
    $pdf->SetTextColor(26, 32, 53);
   
   $nombre = $this->textoParaPDF($datos['nombre']);
    $pdf->SetXY(2, 63);
    $pdf->MultiCell(50, 5, $nombre, 0, 'C');

    // ── Delegación ──
    if (!empty($datos['delegacion_nombre'])) {
        $pdf->SetFont('Helvetica', 'I', 6);
        $pdf->SetTextColor(78, 110, 210);

       $delegacion = $this->textoParaPDF($datos['delegacion_nombre'], false);

        $pdf->SetXY(2, 73);
        $pdf->MultiCell(50, 4, $delegacion, 0, 'C');
    }

    // ── Documento ──
    if (!empty($datos['documento'])) {
        $pdf->SetFont('Helvetica', '', 6);
        $pdf->SetTextColor(100, 110, 130);
        $yDoc = empty($datos['delegacion_nombre']) ? 73 : 79;
        $pdf->SetXY(2, $yDoc);
        $pdf->Cell(50, 3, 'Doc: ' . $datos['documento'], 0, 0, 'C');
    }

    // ── Franja inferior ──
    $pdf->SetFillColor(26, 32, 53);
    $pdf->Rect(0, 84, 54, 6, 'F');
    $pdf->SetFillColor(78, 110, 210);
    $pdf->Rect(0, 84, 54, 1, 'F');
    $pdf->SetFont('Helvetica', '', 5);
    $pdf->SetTextColor(150, 170, 210);
    $pdf->SetXY(2, 86);
    $pdf->Cell(50, 3, 'SikerJapp - Gestion de Eventos', 0, 0, 'C');

    $pdf->Output('I', 'credencial_' . slug($datos['nombre']) . '.pdf');
    exit;
}
    // POST /admin/credenciales/revocar
public function revocar(): void
{
    $this->requireRole('admin', 'manager');

    $idEvento  = (int) $this->input('id_evento');
    $idUsuario = (int) $this->input('id_usuario');
    $tipo      = (int) $this->input('tipo', 1);

    $this->credencialModel->revocarCredencial($idUsuario, $idEvento, $tipo);

    Session::flash('success', 'Credencial revocada.');
    $this->redirect('admin/credenciales/' . $idEvento);
}
// GET /admin/certificados/{id_evento}
public function adminCertificados(string $idEvento): void
{
    $this->requireRole('admin', 'manager');

    $evento = $this->eventoModel->getConCategoria((int) $idEvento);
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

    $lista = $this->credencialModel->getListaCertificados((int) $idEvento);

    $this->viewWithLayout('admin/credenciales/certificados', 'layouts/main', [
        'title'  => 'Certificados — ' . $evento['nombre_corto'],
        'evento' => $evento,
        'lista'  => $lista,
    ]);
}

// POST /admin/certificados/aprobar
public function aprobarCertificado(): void
{
    $this->requireRole('admin', 'manager');

    $user      = Session::user();
    $idEvento  = (int) $this->input('id_evento');
    $idUsuario = (int) $this->input('id_usuario');
    $tipo      = (int) $this->input('tipo', 1);

    $this->credencialModel->aprobarCredencial($idUsuario, $idEvento, $user['id'], 2);

    Session::flash('success', 'Certificado aprobado.');
    $this->redirect('admin/certificados/' . $idEvento);
}

// POST /admin/certificados/revocar
public function revocarCertificado(): void
{
    $this->requireRole('admin', 'manager');

    $idEvento  = (int) $this->input('id_evento');
    $idUsuario = (int) $this->input('id_usuario');

    $this->credencialModel->revocarCredencial($idUsuario, $idEvento, 2);

    Session::flash('success', 'Certificado revocado.');
    $this->redirect('admin/certificados/' . $idEvento);
}

}
