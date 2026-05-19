<?php
// ============================================================
// CONTROLADOR: ReporteController
// Maneja reportes, estadísticas y PDFs
// ============================================================

class ReporteController extends Controller
{
    private ReporteModel $reporteModel;
    private EventoModel  $eventoModel;

    public function __construct()
    {
        require_once ROOT_PATH . '/app/Models/ReporteModel.php';
        require_once ROOT_PATH . '/app/Models/EventoModel.php';
        $this->reporteModel = new ReporteModel();
        $this->eventoModel  = new EventoModel();
    }

    // ── DASHBOARD DE REPORTES ────────────────────────────────
private function textoParaPDF(string $texto, bool $mayusculas = false): string
{
    if ($mayusculas) $texto = mb_strtoupper($texto, 'UTF-8');

    $tildes = [
        'Á'=>'A','É'=>'E','Í'=>'I','Ó'=>'O','Ú'=>'U',
        'á'=>'a','é'=>'e','í'=>'i','ó'=>'o','ú'=>'u',
        'Ñ'=>'N','ñ'=>'n','Ü'=>'U','ü'=>'u',
        'À'=>'A','È'=>'E','Ì'=>'I','Ò'=>'O','Ù'=>'U',
        'ú'=>'u','ó'=>'o','é'=>'e','á'=>'a','í'=>'i',
    ];
    $texto = strtr($texto, $tildes);
    return iconv('UTF-8', 'ISO-8859-1//TRANSLIT', $texto);
}
    // GET /admin/reportes
    public function index(): void
    {
        $this->requireRole('admin', 'manager');

        $user = Session::user();

        if ($user['tipoU'] == 4) {
            // Manager — solo su evento
            $db   = Database::getInstance()->getConnection();
            $stmt = $db->prepare("SELECT id FROM tbx_eventos WHERE id_admin = ? LIMIT 1");
            $stmt->execute([$user['id']]);
            $ev = $stmt->fetch();

            if (!$ev) {
                Session::flash('error', 'No tienes un evento asignado.');
                $this->redirect('dashboard');
                return;
            }
            $this->redirect('admin/reportes/' . $ev['id']);
            return;
        }

        // Superadmin — resumen de todos los eventos
        $resumen = $this->reporteModel->getResumenGeneral();

        $this->viewWithLayout('admin/reportes/index', 'layouts/main', [
            'title'   => 'Reportes y Estadísticas',
            'resumen' => $resumen,
        ]);
    }

    // GET /admin/reportes/{id_evento}
    public function evento(string $idEvento): void
    {
        $this->requireRole('admin', 'manager');

        $user   = Session::user();
        $evento = $this->eventoModel->find((int) $idEvento);

        if (!$evento) {
            $this->redirect('admin/reportes');
            return;
        }

        // Manager solo puede ver su evento
        if ($user['tipoU'] == 4 && $evento['id_admin'] != $user['id']) {
            Session::flash('error', 'No tienes acceso a este evento.');
            $this->redirect('admin/reportes');
            return;
        }

        $resumen     = $this->reporteModel->getResumenEvento((int) $idEvento);
        $estadoPagos = $this->reporteModel->getEstadoPagos((int) $idEvento);
        $asistencia  = $this->reporteModel->getAsistenciaPorDia((int) $idEvento);
        $porDeleg    = $this->reporteModel->getInscritosPorDelegacion((int) $idEvento);

        $this->viewWithLayout('admin/reportes/evento', 'layouts/main', [
            'title'       => 'Reportes — ' . $evento['nombre_corto'],
            'evento'      => $evento,
            'resumen'     => $resumen,
            'estadoPagos' => $estadoPagos,
            'asistencia'  => $asistencia,
            'porDeleg'    => $porDeleg,
        ]);
    }

    // ── PDFs ─────────────────────────────────────────────────

    // GET /admin/reportes/{id}/pdf/inscritos
 public function pdfInscritos(string $idEvento): void
{
    $this->requireRole('admin', 'manager');

    $evento    = $this->eventoModel->find((int) $idEvento);
    $inscritos = $this->reporteModel->getInscritos((int) $idEvento);

    $this->generarPDFLista(
        $evento,
        $inscritos,
        'Listado de Inscritos',
        ['Nombre', 'Tipo', 'Delegación', 'Estado', 'Pago'],
        ['nombre', 'tipo', 'delegacion', 'estado_inscripcion', 'estado_pago']
    );
}

    // GET /admin/reportes/{id}/pdf/pagos
    public function pdfPagos(string $idEvento): void
    {
        $this->requireRole('admin', 'manager');

        $evento = $this->eventoModel->find((int) $idEvento);
        $pagos  = $this->reporteModel->getPagos((int) $idEvento);

        $this->generarPDFLista(
            $evento,
            $pagos,
            'Listado de Pagos',
            ['Nombre', 'Tipo', 'Valor', 'Estado', 'Fecha'],
            ['nombre', 'tipo', 'valor', 'estado', 'fec_subida']
        );
    }

    // GET /admin/reportes/{id}/pdf/asistencia
    public function pdfAsistencia(string $idEvento): void
    {
        $this->requireRole('admin', 'manager');

        $evento     = $this->eventoModel->find((int) $idEvento);
        $asistencia = $this->reporteModel->getAsistencia((int) $idEvento);

        $this->generarPDFLista(
            $evento,
            $asistencia,
            'Listado de Asistencia',
            ['Nombre', 'Sesión', 'Fecha', 'Hora'],
            ['nombre', 'sesion', 'fecha', 'hora']
        );
    }

    // ── GENERADOR PDF ────────────────────────────────────────
    private function generarPDFLista(array $evento, array $datos, string $titulo,
                                      array $columnas, array $campos): void
    {
                // Evita caché del navegador
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Cache-Control: post-check=0, pre-check=0', false);
header('Pragma: no-cache');
header('Expires: Thu, 01 Jan 1970 00:00:00 GMT');
        require_once ROOT_PATH . '/core/fpdf/FPDFExtended.php';

// Test directo en PDF
/*
$pdf = new FPDFExtended('L', 'mm', 'A4');
$pdf->AddPage();
$pdf->SetFont('Helvetica', '', 10);
$pdf->Cell(0, 10, $this->textoParaPDF('Juan Sebastian Rocha Castano'), 1, 1);
$pdf->Cell(0, 10, $this->textoParaPDF('Luisa Valle Maza'), 1, 1);
$pdf->Output('I', 'test.pdf');
exit;
*/
        $pdf = new FPDFExtended('L', 'mm', 'A4');
        $pdf->AddPage();
        $pdf->SetMargins(10, 10, 10);
        $pdf->SetAutoPageBreak(true, 15);

        // ── Encabezado ──
        $pdf->SetFillColor(26, 32, 53);
        $pdf->Rect(0, 0, 297, 22, 'F');

        $pdf->SetFont('Helvetica', 'B', 13);
        $pdf->SetTextColor(255, 255, 255);
        $pdf->SetXY(10, 6);
        $pdf->Cell(180, 8, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', strtoupper($evento['nombre_corto'])), 0, 0, 'L');

        $pdf->SetFont('Helvetica', '', 8);
        $pdf->SetXY(10, 14);
        $pdf->Cell(180, 5, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', $titulo . ' — Generado: ' . date('d/m/Y H:i')), 0, 0, 'L');

        // ── Tabla ──
        $pdf->SetY(28);
        $colWidth = (277) / count($columnas);

        // Header tabla
        $pdf->SetFillColor(59, 91, 219);
        $pdf->SetTextColor(255, 255, 255);
        $pdf->SetFont('Helvetica', 'B', 8);

        foreach ($columnas as $col) {
            $pdf->Cell($colWidth, 8,
                iconv('UTF-8', 'ISO-8859-1//TRANSLIT', strtoupper($col)),
                1, 0, 'C', true);
        }
        $pdf->Ln();

        // Filas
        $pdf->SetTextColor(30, 30, 30);
        $pdf->SetFont('Helvetica', '', 7.5);
        $fill = false;

    foreach ($datos as $fila) {
    $pdf->SetFillColor(248, 249, 255);

    foreach ($campos as $campo) {
        $valor = $fila[$campo] ?? '-';

        if ($campo === 'valor' && is_numeric($valor)) {
            $valor = '$' . number_format((float)$valor, 0, ',', '.');
        }

        if ($campo === 'fec_subida' && !empty($valor)) {
            $valor = date('d/m/Y', strtotime($valor));
        }

        $pdf->Cell($colWidth, 7,
            $this->textoParaPDF((string)$valor),
            1, 0, 'L', $fill);
    }
    $pdf->Ln();
    $fill = !$fill;
}
        // Total de registros
        $pdf->SetFont('Helvetica', 'B', 8);
        $pdf->SetFillColor(26, 32, 53);
        $pdf->SetTextColor(255, 255, 255);
        $pdf->Cell(277, 7,
            '  Total: ' . count($datos) . ' registros',
            1, 0, 'L', true);

       $pdf->Output('D', slug($titulo) . '_' . $evento['id'] . '.pdf');
        exit;
    }
}
