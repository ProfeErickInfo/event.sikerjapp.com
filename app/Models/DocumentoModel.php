<?php
// ============================================================
// MODELO: DocumentoModel
// Maneja documentos descargables de eventos
// ============================================================

class DocumentoModel extends Model
{
    protected string $table = 'trn_archivos_evento';

    // Obtiene documentos de un evento
    public function getByEvento(int $idEvento): array
    {
        return $this->raw(
            "SELECT * FROM trn_archivos_evento
             WHERE id_evento = ?
             ORDER BY fecha_subida DESC",
            [$idEvento]
        );
    }

    // Sube un documento
    public function subir(array $data): int
    {
        return $this->insert([
            'id_evento'    => $data['id_evento'],
            'nombre'       => $data['nombre'],
            'archivo'      => $data['archivo'],
            'fecha_subida' => date('Y-m-d H:i:s'),
        ]);
    }

    // Elimina un documento
    public function eliminar(int $id): array|false
    {
        $doc = $this->find($id);
        if ($doc) {
            $this->delete($id);
        }
        return $doc;
    }
}
