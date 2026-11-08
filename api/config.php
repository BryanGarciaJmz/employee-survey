<?php
// Configuración de conexión a la base de datos
// - En Render: usa la variable de entorno DATABASE_URL (PostgreSQL).
// - En tu compu (XAMPP): si no existe DATABASE_URL, usa MySQL local.
header("Content-Type: application/json; charset=UTF-8");

$databaseUrl = getenv("DATABASE_URL");

try {
    if ($databaseUrl) {
        // Formato: postgresql://usuario:password@host:puerto/basedatos
        $url = parse_url($databaseUrl);
        $driver = "pgsql";
        $dsn = sprintf(
            "pgsql:host=%s;port=%d;dbname=%s",
            $url["host"],
            $url["port"] ?? 5432,
            ltrim($url["path"], "/")
        );
        $DB_USER = urldecode($url["user"]);
        $DB_PASS = urldecode($url["pass"] ?? "");
    } else {
        $driver = "mysql";
        $dsn = "mysql:host=localhost;dbname=empleados_db;charset=utf8mb4";
        $DB_USER = "root";
        $DB_PASS = "";
    }

    $pdo = new PDO($dsn, $DB_USER, $DB_PASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["error" => "Error de conexión a la base de datos: " . $e->getMessage()]);
    exit;
}

// Si la base de datos de Render está vacía, crea las tablas y datos de ejemplo automáticamente
if ($driver === "pgsql") {
    $existe = $pdo->query("SELECT to_regclass('public.usuarios')")->fetchColumn();
    if (!$existe) {
        $pdo->exec(file_get_contents(__DIR__ . "/../bd/empleados_postgres.sql"));
    }
}

function validarToken(PDO $pdo)
{
    $headers = function_exists('getallheaders') ? getallheaders() : [];
    $authHeader = $headers['Authorization'] ?? ($_SERVER['HTTP_AUTHORIZATION'] ?? '');

    if (!$authHeader || stripos($authHeader, 'Bearer ') !== 0) {
        http_response_code(401);
        echo json_encode(["error" => "Token no proporcionado."]);
        exit;
    }

    $token = trim(substr($authHeader, 7));

    $stmt = $pdo->prepare(
        "SELECT id, usuario FROM usuarios WHERE token = :token AND token_expira > NOW()"
    );
    $stmt->execute(["token" => $token]);
    $usuario = $stmt->fetch();

    if (!$usuario) {
        http_response_code(401);
        echo json_encode(["error" => "Token inválido o expirado."]);
        exit;
    }

    return $usuario;
}
