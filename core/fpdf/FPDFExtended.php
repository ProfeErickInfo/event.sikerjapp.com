<?php
require_once __DIR__ . '/fpdf.php';

// ============================================================
// CLASE FPDFExtended
// Extiende FPDF con métodos adicionales: Ellipse, RoundedRect
// ============================================================

class FPDFExtended extends FPDF
{
    // Dibuja una elipse
    public function Ellipse(float $x, float $y, float $rx, float $ry, string $style = ''): void
    {
        $lx = (4/3) * (M_SQRT2 - 1) * $rx;
        $ly = (4/3) * (M_SQRT2 - 1) * $ry;
        $k  = $this->k;
        $h  = $this->h;

        $this->_out(sprintf(
            '%.2F %.2F m %.2F %.2F %.2F %.2F %.2F %.2F c %.2F %.2F %.2F %.2F %.2F %.2F c %.2F %.2F %.2F %.2F %.2F %.2F c %.2F %.2F %.2F %.2F %.2F %.2F c %s',
            ($x+$rx)*$k, ($h-$y)*$k,
            ($x+$rx)*$k, ($h-($y-$ly))*$k, ($x+$lx)*$k, ($h-($y-$ry))*$k, $x*$k, ($h-($y-$ry))*$k,
            ($x-$lx)*$k, ($h-($y-$ry))*$k, ($x-$rx)*$k, ($h-($y-$ly))*$k, ($x-$rx)*$k, ($h-$y)*$k,
            ($x-$rx)*$k, ($h-($y+$ly))*$k, ($x-$lx)*$k, ($h-($y+$ry))*$k, $x*$k, ($h-($y+$ry))*$k,
            ($x+$lx)*$k, ($h-($y+$ry))*$k, ($x+$rx)*$k, ($h-($y+$ly))*$k, ($x+$rx)*$k, ($h-$y)*$k,
            $style === 'F' ? 'f' : ($style === 'FD' || $style === 'DF' ? 'b' : 'S')
        ));
    }

    // Dibuja un rectángulo con esquinas redondeadas
    public function RoundedRect(float $x, float $y, float $w, float $h, float $r, string $style = ''): void
    {
        $MyArc = 4/3 * (sqrt(2) - 1);
        $hp    = $this->h;
        $k     = $this->k;

        $this->_out(sprintf('%.2F %.2F m', ($x+$r)*$k, ($hp-$y)*$k));

        $xc = $x+$w-$r; $yc = $y+$r;
        $this->_out(sprintf('%.2F %.2F l', $xc*$k, ($hp-$y)*$k));
        $this->_Arc($xc+$r*$MyArc, $yc-$r, $xc+$r, $yc-$r*$MyArc, $xc+$r, $yc);

        $xc = $x+$w-$r; $yc = $y+$h-$r;
        $this->_out(sprintf('%.2F %.2F l', ($x+$w)*$k, ($hp-$yc)*$k));
        $this->_Arc($xc+$r, $yc+$r*$MyArc, $xc+$r*$MyArc, $yc+$r, $xc, $yc+$r);

        $xc = $x+$r; $yc = $y+$h-$r;
        $this->_out(sprintf('%.2F %.2F l', $xc*$k, ($hp-($y+$h))*$k));
        $this->_Arc($xc-$r*$MyArc, $yc+$r, $xc-$r, $yc+$r*$MyArc, $xc-$r, $yc);

        $xc = $x+$r; $yc = $y+$r;
        $this->_out(sprintf('%.2F %.2F l', $x*$k, ($hp-$yc)*$k));
        $this->_Arc($xc-$r, $yc-$r*$MyArc, $xc-$r*$MyArc, $yc-$r, $xc, $yc-$r);

        $op = $style === 'F' ? 'f' : ($style === 'FD' || $style === 'DF' ? 'b' : 'S');
        $this->_out($op);
    }

    // Método auxiliar para arcos (requerido por RoundedRect)
    public function _Arc(float $x1, float $y1, float $x2, float $y2, float $x3, float $y3): void
    {
        $h = $this->h;
        $this->_out(sprintf(
            '%.2F %.2F %.2F %.2F %.2F %.2F c',
            $x1 * $this->k, ($h-$y1) * $this->k,
            $x2 * $this->k, ($h-$y2) * $this->k,
            $x3 * $this->k, ($h-$y3) * $this->k
        ));
    }
}