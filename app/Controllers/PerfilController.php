<?php
// ============================================================
// CONTROLADOR: PerfilController
// Maneja perfil y cambio de contraseña del usuario
// ============================================================

class PerfilController extends Controller
{
    private UsuarioModel $usuarioModel;

    public function __construct()
    {
        require_once ROOT_PATH . '/app/Models/UsuarioModel.php';
        $this->usuarioModel = new UsuarioModel();
    }

    // GET /perfil
    public function index(): void
    {
        $this->requireAuth();

        $user    = Session::user();
        $usuario = $this->usuarioModel->find($user['id']);

        $this->viewWithLayout('perfil/index', 'layouts/main', [
            'title'   => 'Mi Perfil',
            'usuario' => $usuario,
        ]);
    }

    // POST /perfil/actualizar
    public function actualizar(): void
    {
        $this->requireAuth();

        $user  = Session::user();
        $name  = trim($this->input('name'));
        $email = trim($this->input('email'));

        $errors = [];

        if (empty($name))
            $errors[] = 'El nombre es obligatorio.';
        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL))
            $errors[] = 'El email no es válido.';

        // Verifica que el email no lo use otro usuario
        if (!empty($email)) {
            $stmt = Database::getInstance()->getConnection()->prepare(
                "SELECT id FROM wx25_usu WHERE email = ? AND id != ?"
            );
            $stmt->execute([$email, $user['id']]);
            if ($stmt->fetch()) {
                $errors[] = 'Ese email ya está en uso por otro usuario.';
            }
        }

        if (!empty($errors)) {
            Session::flash('error', implode('<br>', $errors));
            $this->redirect('perfil');
            return;
        }

        $this->usuarioModel->update($user['id'], [
            'name'  => $name,
            'email' => $email,
            'nickz' => $email, // El nick es el email
        ]);

        // Actualiza la sesión con los nuevos datos
        $userActualizado = $this->usuarioModel->find($user['id']);
        Session::login([
            'id'    => $userActualizado['id'],
            'nickz' => $userActualizado['nickz'],
            'name'  => $userActualizado['name'],
            'email' => $userActualizado['email'],
            'tipoU' => $userActualizado['tipoU'],
            'role'  => $userActualizado['role'],
        ]);

        Session::flash('success', 'Perfil actualizado correctamente.');
        $this->redirect('perfil');
    }

    // POST /perfil/cambiar-password
    public function cambiarPassword(): void
    {
        $this->requireAuth();

        $user        = Session::user();
        $actual      = $this->input('password_actual');
        $nueva       = $this->input('password_nueva');
        $confirmar   = $this->input('password_confirmar');

        $errors = [];

        if (empty($actual))
            $errors[] = 'La contraseña actual es obligatoria.';
        if (strlen($nueva) < 8)
            $errors[] = 'La nueva contraseña debe tener al menos 8 caracteres.';
        if ($nueva !== $confirmar)
            $errors[] = 'Las contraseñas no coinciden.';

        if (!empty($errors)) {
            Session::flash('error', implode('<br>', $errors));
            $this->redirect('perfil');
            return;
        }

        // Verifica contraseña actual
        $usuario = $this->usuarioModel->find($user['id']);
        $passwordOk = false;

        if (str_starts_with($usuario['pazz'], '$2y$')) {
            $passwordOk = verifyPassword($actual, $usuario['pazz']);
        } else {
            $passwordOk = ($usuario['pazz'] === $actual);
        }

        if (!$passwordOk) {
            Session::flash('error', 'La contraseña actual es incorrecta.');
            $this->redirect('perfil');
            return;
        }

        $this->usuarioModel->update($user['id'], [
            'pazz' => hashPassword($nueva),
        ]);

        Session::flash('success', '¡Contraseña actualizada correctamente!');
        $this->redirect('perfil');
    }
}
