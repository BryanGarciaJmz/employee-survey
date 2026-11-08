<?php
// Endpoint CRUD de empleados + listado compatible con DataTables (server-side processing: búsqueda, orden, paginación)
require_once __DIR__ . "/config.php";

$usuario = validarToken($pdo);

$metodo = $_SERVER["REQUEST_METHOD"];

switch ($metodo) {
    case "GET":
        listarEmpleados($pdo);
        break;

    case "POST":
        crearEmpleado($pdo);
        break;

    case "PUT":
        actualizarEmpleado($pdo);
        break;

    case "DELETE":
        eliminarEmpleado($pdo);
        break;

    default:
        http_response_code(405);
        echo json_encode(["error" => "Método no permitido."]);
}

// LISTAR (con soporte de parámetros estilo DataTables)
function listarEmpleados(PDO $pdo)
{
    $columnas = ["id", "nombre", "departamento", "puesto", "salario"];

    $draw   = isset($_GET["draw"]) ? (int) $_GET["draw"] : 1;
    $start  = isset($_GET["start"]) ? (int) $_GET["start"] : 0;
    $length = isset($_GET["length"]) ? (int) $_GET["length"] : 10;
    $buscar = isset($_GET["search"]["value"]) ? trim($_GET["search"]["value"]) : "";

    $ordenCol = 0;
    $ordenDir = "ASC";
    if (isset($_GET["order"][0]["column"])) {
        $ordenCol = (int) $_GET["order"][0]["column"];
        $ordenDir = strtoupper($_GET["order"][0]["dir"]) === "DESC" ? "DESC" : "ASC";
    }
    $columnaOrden = $columnas[$ordenCol] ?? "id";

    // Total de registros sin filtro
    $totalRegistros = (int) $pdo->query("SELECT COUNT(*) FROM empleados")->fetchColumn();

    // Construcción de filtro de búsqueda
    $where  = "";
    $params = [];
    if ($buscar !== "") {
        // LOWER() para que la búsqueda no distinga mayúsculas (igual en MySQL y PostgreSQL)
        $where = "WHERE LOWER(nombre) LIKE :b1 OR LOWER(departamento) LIKE :b2 OR LOWER(puesto) LIKE :b3";
        $patron = "%" . mb_strtolower($buscar) . "%";
        $params = ["b1" => $patron, "b2" => $patron, "b3" => $patron];
    }

    // Total de registros filtrados
    $sqlFiltrado = "SELECT COUNT(*) FROM empleados {$where}";
    $stmtFiltrado = $pdo->prepare($sqlFiltrado);
    $stmtFiltrado->execute($params);
    $totalFiltrado = (int) $stmtFiltrado->fetchColumn();

    // Consulta de datos paginados
    $sql = "SELECT id, nombre, departamento, puesto, salario
            FROM empleados
            {$where}
            ORDER BY {$columnaOrden} {$ordenDir}
            LIMIT :length OFFSET :start";

    $stmt = $pdo->prepare($sql);
    foreach ($params as $key => $value) {
        $stmt->bindValue(":{$key}", $value);
    }
    $stmt->bindValue(":start", $start, PDO::PARAM_INT);
    $stmt->bindValue(":length", $length, PDO::PARAM_INT);
    $stmt->execute();
    $datos = $stmt->fetchAll();

    echo json_encode([
        "draw"            => $draw,
        "recordsTotal"    => $totalRegistros,
        "recordsFiltered" => $totalFiltrado,
        "data"            => $datos,
    ]);
}

// Crear
function crearEmpleado(PDO $pdo)
{
    $input = json_decode(file_get_contents("php://input"), true);

    $nombre       = trim($input["nombre"] ?? "");
    $departamento = trim($input["departamento"] ?? "");
    $puesto       = trim($input["puesto"] ?? "");
    $salario      = $input["salario"] ?? null;

    if ($nombre === "" || $departamento === "" || $puesto === "" || $salario === null) {
        http_response_code(400);
        echo json_encode(["error" => "Todos los campos son requeridos."]);
        return;
    }

    $stmt = $pdo->prepare(
        "INSERT INTO empleados (nombre, departamento, puesto, salario)
         VALUES (:nombre, :departamento, :puesto, :salario)"
    );
    $stmt->execute([
        "nombre"       => $nombre,
        "departamento" => $departamento,
        "puesto"       => $puesto,
        "salario"      => $salario,
    ]);

    echo json_encode(["ok" => true, "id" => $pdo->lastInsertId()]);
}

// Actualizar
function actualizarEmpleado(PDO $pdo)
{
    $input = json_decode(file_get_contents("php://input"), true);

    $id           = (int) ($input["id"] ?? 0);
    $nombre       = trim($input["nombre"] ?? "");
    $departamento = trim($input["departamento"] ?? "");
    $puesto       = trim($input["puesto"] ?? "");
    $salario      = $input["salario"] ?? null;

    if ($id <= 0 || $nombre === "" || $departamento === "" || $puesto === "" || $salario === null) {
        http_response_code(400);
        echo json_encode(["error" => "Datos inválidos para actualizar."]);
        return;
    }

    $stmt = $pdo->prepare(
        "UPDATE empleados
         SET nombre = :nombre, departamento = :departamento, puesto = :puesto, salario = :salario
         WHERE id = :id"
    );
    $stmt->execute([
        "nombre"       => $nombre,
        "departamento" => $departamento,
        "puesto"       => $puesto,
        "salario"      => $salario,
        "id"           => $id,
    ]);

    echo json_encode(["ok" => true]);
}

// Eliminar
function eliminarEmpleado(PDO $pdo)
{
    parse_str(file_get_contents("php://input"), $deleteVars);
    $id = (int) ($_GET["id"] ?? $deleteVars["id"] ?? 0);

    if ($id <= 0) {
        http_response_code(400);
        echo json_encode(["error" => "ID inválido."]);
        return;
    }

    $stmt = $pdo->prepare("DELETE FROM empleados WHERE id = :id");
    $stmt->execute(["id" => $id]);

    echo json_encode(["ok" => true]);
}
