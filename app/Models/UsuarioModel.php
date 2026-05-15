<?php
// ============================================================
// MODELO: UsuarioModel
// Maneja todo lo relacionado con la tabla wx25_usu
// ============================================================

class UsuarioModel extends Model
{
    protected string $table = 'wx25_usu';

    // Busca un usuario por su nick o email para el login
    public function findByCredential(string $credential): array|false
    {
        $sql  = "SELECT * FROM {$this->table} WHERE (nickz = ? OR email = ?) AND estado = 1 LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$credential, $credential]);
        return $stmt->fetch();
    }

    // Verifica si un nick ya existe
    public function nickExists(string $nickz): bool
    {
        $stmt = $this->db->prepare("SELECT id FROM {$this->table} WHERE nickz = ?");
        $stmt->execute([$nickz]);
        return (bool) $stmt->fetch();
    }

    // Verifica si un email ya existe
    public function emailExists(string $email): bool
    {
        $stmt = $this->db->prepare("SELECT id FROM {$this->table} WHERE email = ?");
        $stmt->execute([$email]);
        return (bool) $stmt->fetch();
    }

    // Registra un nuevo usuario
    public function register(array $data): int
    {
        // Determina el role en texto según tipoU
        $roles = [
            0 => 'admin',
            1 => 'club',
            2 => 'club_invitado',
            3 => 'admin_torneo',
            4 => 'juez_bascula',
            5 => 'juez_revision',
            6 => 'juez_mesa',
            7 => 'juez_poomsae',
            8 => 'invitado',
        ];

        return $this->insert([
            'tipoU'    => $data['tipoU'],
            'role'     => $roles[$data['tipoU']] ?? 'invitado',
            'id_asocc' => 0,
            'nickz'    => $data['nickz'],
            'name'     => $data['name'],
            'email'    => $data['email'],
            'pazz'     => hashPassword($data['pazz']),
            'fec_reg'  => date('Y-m-d'),
            'estado'   => 1,
        ]);
    }

    // Obtiene todos los usuarios con paginación
    public function paginate(int $page = 1, int $perPage = 20): array
    {
        $offset = ($page - 1) * $perPage;
        return $this->raw(
            "SELECT * FROM {$this->table} ORDER BY id DESC LIMIT ? OFFSET ?",
            [$perPage, $offset]
        );
    }
}
