<?php
// ============================================================
// CONTROLADOR: AuthController
// Login con email, registro con contraseña generada por sistema
// ============================================================

class AuthController extends Controller
{
    private UsuarioModel $usuarioModel;

    public function __construct()
    {
        require_once ROOT_PATH . '/app/Models/UsuarioModel.php';
        $this->usuarioModel = new UsuarioModel();
    }

    // ── LOGIN ────────────────────────────────────────────────

    // GET /auth/login
    public function loginForm(): void
    {
        if (Session::isLoggedIn()) {
            $this->redirect('dashboard');
        }
        $this->view('auth/login');
    }

    // POST /auth/login
    public function login(): void
    {
        $email = $this->input('nickz');
        $pazz  = $this->input('pazz');

        if (empty($email) || empty($pazz)) {
            Session::flash('error', 'Email y contraseña son obligatorios.');
            $this->redirect('auth/login');
            return;
        }

        $user = $this->usuarioModel->findByCredential($email);

        if (!$user) {
            Session::flash('error', 'Email no encontrado o cuenta inactiva.');
            $this->redirect('auth/login');
            return;
        }

        // Verifica contraseña — soporta texto plano legado y bcrypt
        $passwordOk = false;
        if (str_starts_with($user['pazz'], '$2y$')) {
            $passwordOk = verifyPassword($pazz, $user['pazz']);
        } else {
            if ($user['pazz'] === $pazz) {
                $passwordOk = true;
                // Actualiza a hash seguro automáticamente
                $this->usuarioModel->update($user['id'], ['pazz' => hashPassword($pazz)]);
            }
        }

        if (!$passwordOk) {
            Session::flash('error', 'Contraseña incorrecta.');
            $this->redirect('auth/login');
            return;
        }

        Session::login([
            'id'    => $user['id'],
            'nickz' => $user['nickz'],
            'name'  => $user['name'],
            'email' => $user['email'],
            'tipoU' => $user['tipoU'],
            'role'  => $user['role'],
        ]);

        Session::flash('success', '¡Bienvenido, ' . ($user['name'] ?? $user['email']) . '!');
        $this->redirect('dashboard');
    }

    // ── REGISTRO ─────────────────────────────────────────────

    // GET /auth/register
    public function registerForm(): void
    {
        if (Session::isLoggedIn()) {
            $this->redirect('dashboard');
        }
        $old = Session::get('old_input', []);
        Session::remove('old_input');
        $this->view('auth/register', ['old' => $old]);
    }

    // POST /auth/register
    public function register(): void
    {
        $data = [
            'tipoU' => (int) $this->input('tipoU', 8),
            'name'  => $this->input('name'),
            'email' => $this->input('email'),
        ];

        $errors = [];

        if (empty($data['name']))
            $errors[] = 'El nombre completo es obligatorio.';
        if (empty($data['email']) || !filter_var($data['email'], FILTER_VALIDATE_EMAIL))
            $errors[] = 'El email no es válido.';
        if ($this->usuarioModel->emailExists($data['email']))
            $errors[] = 'Ese email ya está registrado.';

        if (!empty($errors)) {
            Session::flash('error', implode('<br>', $errors));
            Session::set('old_input', $data);
            $this->redirect('auth/register');
            return;
        }

        // Genera contraseña temporal y usa email como usuario
        $password      = $this->generatePassword();
        $data['nickz'] = $data['email'];
        $data['pazz']  = $password;

        $id = $this->usuarioModel->register($data);

        if (!$id) {
            Session::flash('error', 'Error al crear la cuenta. Intenta de nuevo.');
            $this->redirect('auth/register');
            return;
        }

        // Envía correo con credenciales
        $mailer  = new Mailer();
        $enviado = $mailer->sendWelcome($data['email'], $data['name'], $password);

        if ($enviado) {
            Session::flash('success',
                '¡Cuenta creada! Te enviamos tus credenciales a <strong>' .
                htmlspecialchars($data['email']) . '</strong>. Revisa tu bandeja de entrada.'
            );
        } else {
            // Respaldo si falla el correo
            Session::flash('warning',
                '¡Cuenta creada! No pudimos enviar el correo. ' .
                'Tu contraseña temporal es: <strong>' . $password . '</strong> — Guárdala.'
            );
        }

        Session::remove('old_input');
        $this->redirect('auth/login');
    }

    // ── LOGOUT ───────────────────────────────────────────────

    public function logout(): void
    {
        Session::logout();
        Session::flash('success', 'Has cerrado sesión correctamente.');
        $this->redirect('auth/login');
    }

    // ── HELPERS ──────────────────────────────────────────────

    private function generatePassword(int $length = 10): string
    {
        $chars    = 'abcdefghjkmnpqrstuvwxyzABCDEFGHJKMNPQRSTUVWXYZ23456789@#$!';
        $password = '';
        $max      = strlen($chars) - 1;
        for ($i = 0; $i < $length; $i++) {
            $password .= $chars[random_int(0, $max)];
        }
        return $password;
    }
}
