<?php
// Autenticación: recibe usuario/contraseña, genera token, lo guarda en la tabla usuarios y lo regresa al cliente (JSON).
require_once __DIR__ . "/config.php";

$input = json_decode(file_get_contents("php://input"), true);

$usuario = $input["usuario"] ?? "";
$password = $input["password"] ?? "";

if ($usuario === "" || $password === "") {
    http_response_code(400);
    echo json_encode(["error" => "Usuario y contraseña son requeridos."]);
    exit;
}

$stmt = $pdo->prepare("SELECT id, usuario, password_hash FROM usuarios WHERE usuario = :usuario");
$stmt->execute(["usuario" => $usuario]);
$row = $stmt->fetch();

if (!$row || !password_verify($password, $row["password_hash"])) {
    http_response_code(401);
    echo json_encode(["error" => "Usuario o contraseña incorrectos."]);
    exit;
}

// Genera un token aleatorio seguro y define expiración (p.ej. 2 horas)
$token = bin2hex(random_bytes(32));
$expira = date("Y-m-d H:i:s", strtotime("+2 hours"));

$update = $pdo->prepare("UPDATE usuarios SET token = :token, token_expira = :expira WHERE id = :id");
$update->execute([
    "token" => $token,
    "expira" => $expira,
    "id" => $row["id"],
]);

echo json_encode([
    "ok" => true,
    "token" => $token,
    "usuario" => $row["usuario"],
    "expira" => $expira,
]);
