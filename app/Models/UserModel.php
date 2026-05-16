<?php
// ============================================================
// USER MODEL
// Si ya tienes este archivo, AGREGA solo los métodos que
// te falten. Si no existe, usa este archivo completo.
// ============================================================

class UserModel extends Model
{
    protected string $table = 'wx25_usu';

    // Buscar usuario por ID
    public function findById(int $id): ?array
    {
        return $this->db->fetch(
            "SELECT id, tipoU, role, id_asocc, nickz, name, email, estado, fec_reg
             FROM {$this->table} WHERE id = ?",
            [$id]
        );
    }

    // Buscar usuario por email
    public function findByEmail(string $email): ?array
    {
        return $this->db->fetch(
            "SELECT * FROM {$this->table} WHERE email = ?",
            [$email]
        );
    }

    // Buscar usuario por nick
    public function findByNick(string $nickz): ?array
    {
        return $this->db->fetch(
            "SELECT * FROM {$this->table} WHERE nickz = ?",
            [$nickz]
        );
    }

    // Actualizar datos del perfil
    public function updateProfile(int $id, array $data): bool
    {
        return $this->db->execute(
            "UPDATE {$this->table}
             SET name = ?, email = ?, nickz = ?
             WHERE id = ?",
            [$data['name'], $data['email'], $data['nickz'], $id]
        );
    }

    // Actualizar contraseña
    public function updatePassword(int $id, string $passwordHash): bool
    {
        return $this->db->execute(
            "UPDATE {$this->table} SET pazz = ? WHERE id = ?",
            [$passwordHash, $id]
        );
    }

    // Para el login (usado en AuthController)
    public function findForLogin(string $email): ?array
    {
        return $this->db->fetch(
            "SELECT * FROM {$this->table} WHERE email = ? AND estado = 1",
            [$email]
        );
    }
}
