<?php
class TestController extends Controller
{
    public function index(): void
    {
        // Prueba 1: ¿Funciona el framework?
        echo "<h2>✅ Router funcionando</h2>";

        // Prueba 2: ¿Conecta a la BD?
        try {
            $db = Database::getInstance()->getConnection();
            $stmt = $db->query("SELECT COUNT(*) as total FROM tbx_eventos");
            $row = $stmt->fetch();
            echo "<h2>✅ Base de datos conectada — {$row['total']} eventos encontrados</h2>";
        } catch (Exception $e) {
            echo "<h2>❌ Error BD: " . $e->getMessage() . "</h2>";
        }

        // Prueba 3: ¿Funciona la sesión?
        Session::set('test', 'ok');
        echo Session::get('test') === 'ok' 
            ? "<h2>✅ Sesiones funcionando</h2>" 
            : "<h2>❌ Error en sesiones</h2>";

        echo "<hr><p>Todo listo para construir 🚀</p>";
    }
}