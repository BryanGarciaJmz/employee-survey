$(document).ready(function () {
  const API_AUTH = "api/auth.php";
  const API_EMPLEADOS = "api/empleados.php";

  let tabla = null;

  // Manejo de sesión/token (guardado en sessionStorage)
  function guardarToken(token) {
    sessionStorage.setItem("token", token);
  }

  function obtenerToken() {
    return sessionStorage.getItem("token");
  }

  function borrarToken() {
    sessionStorage.removeItem("token");
  }

  function mostrarApp() {
    $("#contenedorLogin").hide();
    $("#contenedorEmpleados").show();
    inicializarTabla();
  }

  function mostrarLogin() {
    $("#contenedorEmpleados").hide();
    $("#contenedorLogin").show();
  }

  // Login
  $("#formLogin").on("submit", function (e) {
    e.preventDefault();
    $("#loginError").text("");

    $.ajax({
      url: API_AUTH,
      type: "POST",
      contentType: "application/json",
      data: JSON.stringify({
        usuario: $("#loginUsuario").val(),
        password: $("#loginPassword").val(),
      }),
      success: function (resp) {
        guardarToken(resp.token);
        $("#formLogin")[0].reset();
        mostrarApp();
      },
      error: function (xhr) {
        const msg = (xhr.responseJSON && xhr.responseJSON.error) || "Error al iniciar sesión.";
        $("#loginError").text(msg);
      },
    });
  });

  $("#btnLogout").on("click", function () {
    borrarToken();
    if (tabla) {
      tabla.destroy();
      $("#tablaEmpleados tbody").empty();
      tabla = null;
    }
    mostrarLogin();
  });

  // Tabla con DataTables (server-side processing -> AJAX a PHP)
  function inicializarTabla() {
    tabla = $("#tablaEmpleados").DataTable({
      serverSide: true,
      processing: true,
      ajax: {
        url: API_EMPLEADOS,
        type: "GET",
        beforeSend: function (xhr) {
          xhr.setRequestHeader("Authorization", "Bearer " + obtenerToken());
        },
        error: function (xhr) {
          if (xhr.status === 401) {
            borrarToken();
            mostrarLogin();
            alert("Tu sesión ha expirado, inicia sesión de nuevo.");
          } else {
            alert("Error al cargar los empleados.");
          }
        },
        dataSrc: function (json) {
          $("#totalRegistros").text(json.recordsTotal);
          return json.data;
        },
      },
      columns: [
        { data: "id" },
        { data: "nombre" },
        { data: "departamento" },
        { data: "puesto" },
        {
          data: "salario",
          render: function (data, type) {
            if (type === "display") {
              return "$" + Number(data).toLocaleString("es-MX");
            }
            return data;
          },
        },
        {
          data: null,
          orderable: false,
          searchable: false,
          render: function (row) {
            return (
              '<button class="btn-editar" data-id="' + row.id + '">Editar</button> ' +
              '<button class="btn-eliminar" data-id="' + row.id + '">Eliminar</button>'
            );
          },
        },
      ],

      paging: true,
      searching: true,
      ordering: true,
      info: true,
      pageLength: 5,
      lengthMenu: [5, 10, 25, 50],
      order: [[0, "asc"]],
      language: {
        lengthMenu: "Mostrar _MENU_ registros",
        zeroRecords: "No se encontraron resultados",
        info: "Mostrando _START_ a _END_ de _TOTAL_ registros",
        infoEmpty: "No hay registros disponibles",
        infoFiltered: "(filtrados de _MAX_ registros totales)",
        processing: "Cargando...",
        search: "Buscar:",
        paginate: {
          first: "Primero",
          last: "Último",
          next: "Siguiente",
          previous: "Anterior",
        },
      },
    });
  }

  // Peticiones con token (helper)
  function peticionAutenticada(opciones) {
    return $.ajax(
      Object.assign({}, opciones, {
        beforeSend: function (xhr) {
          xhr.setRequestHeader("Authorization", "Bearer " + obtenerToken());
        },
      })
    );
  }

  // Alta / edición de empleados (modal con jQuery UI dialog)
  const dialogoForm = $("#dialogoForm").dialog({
    autoOpen: false,
    modal: true,
    width: 400,
    resizable: false,
    buttons: {
      Guardar: function () {
        guardarEmpleado();
      },
      Cancelar: function () {
        dialogoForm.dialog("close");
      },
    },
  });

  $("#btnNuevo").on("click", function () {
    $("#formEmpleado")[0].reset();
    $("#empId").val("");
    dialogoForm.dialog("option", "title", "Nuevo empleado");
    dialogoForm.dialog("open");
  });

  $("#tablaEmpleados").on("click", ".btn-editar", function () {
    const fila = tabla.row($(this).closest("tr")).data();
    $("#empId").val(fila.id);
    $("#empNombre").val(fila.nombre);
    $("#empDepartamento").val(fila.departamento);
    $("#empPuesto").val(fila.puesto);
    $("#empSalario").val(fila.salario);
    dialogoForm.dialog("option", "title", "Editar empleado");
    dialogoForm.dialog("open");
  });

  function guardarEmpleado() {
    const id = $("#empId").val();
    const datos = {
      nombre: $("#empNombre").val().trim(),
      departamento: $("#empDepartamento").val().trim(),
      puesto: $("#empPuesto").val().trim(),
      salario: parseFloat($("#empSalario").val()),
    };

    if (!datos.nombre || !datos.departamento || !datos.puesto || isNaN(datos.salario)) {
      alert("Completa todos los campos correctamente.");
      return;
    }

    const esEdicion = !!id;
    if (esEdicion) datos.id = Number(id);

    peticionAutenticada({
      url: API_EMPLEADOS,
      type: esEdicion ? "PUT" : "POST",
      contentType: "application/json",
      data: JSON.stringify(datos),
      success: function () {
        dialogoForm.dialog("close");
        tabla.ajax.reload(null, false);
      },
      error: function (xhr) {
        alert((xhr.responseJSON && xhr.responseJSON.error) || "Error al guardar el empleado.");
      },
    });
  }

  $("#tablaEmpleados").on("click", ".btn-eliminar", function () {
    const id = $(this).data("id");
    if (!confirm("¿Seguro que deseas eliminar este empleado?")) return;

    peticionAutenticada({
      url: API_EMPLEADOS + "?id=" + id,
      type: "DELETE",
      success: function () {
        tabla.ajax.reload(null, false);
      },
      error: function (xhr) {
        alert((xhr.responseJSON && xhr.responseJSON.error) || "Error al eliminar el empleado.");
      },
    });
  });

  // Información del sistema
  $("#btnInfo").button();
  $("#btnInfo").on("click", function () {
    $("#dialogo").dialog({
      modal: true,
      width: 400,
      resizable: false,
    });
  });

  // Estado inicial: si ya hay token, entra directo a la app
  if (obtenerToken()) {
    mostrarApp();
  } else {
    mostrarLogin();
  }
});
