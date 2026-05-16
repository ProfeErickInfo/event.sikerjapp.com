<?php
// ── AUTENTICACIÓN ────────────────────────────────────────────
$router->get( 'auth/login',    'AuthController@loginForm',    ['guest']);
$router->post('auth/login',    'AuthController@login');
$router->get( 'auth/register', 'AuthController@registerForm', ['guest']);
$router->post('auth/register', 'AuthController@register');
$router->get( 'auth/logout',   'AuthController@logout',       ['auth']);

// ── DASHBOARD ────────────────────────────────────────────────
$router->get('dashboard', 'DashboardController@index', ['auth']);
$router->get('',          'EventosController@public');

// ── EVENTOS (públicos) ───────────────────────────────────────
$router->get('events',      'EventosController@public');
$router->get('events/{id}', 'EventosController@show');

// ── EVENTOS (admin) ──────────────────────────────────────────
$router->get( 'admin/events',             'EventosController@index',  ['auth']);
$router->get( 'admin/events/create',      'EventosController@create', ['auth']);
$router->post('admin/events/store',       'EventosController@store',  ['auth']);
$router->get( 'admin/events/edit/{id}',   'EventosController@edit',   ['auth']);
$router->post('admin/events/update/{id}', 'EventosController@update', ['auth']);
$router->post('admin/events/delete/{id}', 'EventosController@delete', ['auth']);

// ── INSCRIPCIONES (usuario) ──────────────────────────────────
$router->get( 'events/{id}/inscribirse',                  'InscripcionController@form',              ['auth']);
$router->post('events/{id}/inscribirse',                  'InscripcionController@store',             ['auth']);
$router->post('inscripciones/cancelar/{id}',              'InscripcionController@cancelarPropia',    ['auth']);
$router->post('inscripciones/cancelar/participante/{id}', 'InscripcionController@cancelarParticipante', ['auth']);

// ── DELEGACIONES ─────────────────────────────────────────────
$router->get( 'events/{id}/delegacion',                  'InscripcionController@delegacion',            ['auth']);
$router->post('delegacion/crear',                        'InscripcionController@crearDelegacion',       ['auth']);
$router->post('delegacion/participante/agregar',         'InscripcionController@agregarParticipante',   ['auth']);
$router->post('delegacion/inscribir/masivo',             'InscripcionController@inscribirMasivo',       ['auth']);
$router->get( 'delegacion/participante/editar/{id}',     'InscripcionController@editarParticipante',    ['auth']);
$router->post('delegacion/participante/actualizar/{id}', 'InscripcionController@actualizarParticipante',['auth']);
$router->post('delegacion/participante/eliminar/{id}',   'InscripcionController@eliminarParticipante',  ['auth']);

// ── INSCRIPCIONES (admin) ────────────────────────────────────
$router->get( 'admin/inscripciones',               'InscripcionController@index',   ['auth']);
$router->post('admin/inscripciones/aprobar/{id}',  'InscripcionController@aprobar', ['auth']);
$router->post('admin/inscripciones/cancelar/{id}', 'InscripcionController@cancelar',['auth']);

// ── PAGOS ────────────────────────────────────────────────────
$router->get( 'events/{id}/pago',         'PagosController@form',    ['auth']);
$router->post('events/{id}/pago/subir',   'PagosController@subir',   ['auth']);
$router->get( 'admin/pagos',              'PagosController@index',   ['auth']);
$router->post('admin/pagos/aprobar/{id}', 'PagosController@aprobar', ['auth']);
$router->post('admin/pagos/rechazar/{id}','PagosController@rechazar',['auth']);

// ── AGENDA ───────────────────────────────────────────────────
$router->get( 'events/{id}/agenda',                 'AgendaController@index');
$router->get( 'admin/agenda/{id}',                  'AgendaController@admin',           ['auth']);
$router->post('admin/agenda/sesion/store',           'AgendaController@storeSesion',     ['auth']);
$router->post('admin/agenda/sesion/update/{id}',     'AgendaController@updateSesion',    ['auth']);
$router->post('admin/agenda/sesion/delete/{id}',     'AgendaController@deleteSesion',    ['auth']);
$router->post('admin/agenda/cronograma/store',       'AgendaController@storeCronograma', ['auth']);
$router->post('admin/agenda/cronograma/delete/{id}', 'AgendaController@deleteCronograma',['auth']);

// ── CREDENCIALES ─────────────────────────────────────────────
$router->get( 'credential/participante/{id}/{id_evento}', 'CredencialController@credencialParticipante', ['auth']);
$router->get( 'credential/{id_user}/{id_evento}',         'CredencialController@credencial',             ['auth']);
$router->get( 'admin/credenciales/{id}',                  'CredencialController@admin',                  ['auth']);
$router->post('admin/credenciales/aprobar',               'CredencialController@aprobar',                ['auth']);
$router->post('admin/credenciales/revocar',               'CredencialController@revocar',                ['auth']);

// ── ASISTENCIA QR ────────────────────────────────────────────
$router->get( 'admin/asistencia/{id}', 'AsistenciaController@index', ['auth']);
$router->post('admin/asistencia/scan', 'AsistenciaController@scan',  ['auth']);

// ── DOCUMENTOS ───────────────────────────────────────────────
$router->get( 'events/{id}/documentos',       'DocumentosController@lista');
$router->get( 'admin/documentos/{id}',        'DocumentosController@admin',  ['auth']);
$router->post('admin/documentos/upload/{id}', 'DocumentosController@upload', ['auth']);
$router->post('admin/documentos/delete/{id}', 'DocumentosController@delete', ['auth']);
// ── PERFIL ───────────────────────────────────────────────────
$router->get( 'perfil',                  'PerfilController@index',          ['auth']);
$router->post('perfil/actualizar',       'PerfilController@actualizar',     ['auth']);
$router->post('perfil/cambiar-password', 'PerfilController@cambiarPassword',['auth']);