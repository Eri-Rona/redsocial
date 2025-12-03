<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: redsocial_login.html');
    exit();
}

// Get user data from session
$userName = $_SESSION['user_name'];
$userEmail = $_SESSION['user_email'];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Red Social</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .sidebar {
            min-height: 100vh;
            background-color: #343a40;
            color: white;
            padding-top: 20px;
        }
        .main-content {
            padding: 20px;
        }
        .user-info {
            padding: 20px;
            border-bottom: 1px solid #4b545c;
            text-align: center;
        }
        .nav-link {
            color: rgba(255, 255, 255, 0.8);
            margin: 5px 0;
            border-radius: 5px;
        }
        .nav-link:hover {
            color: white;
            background-color: rgba(255, 255, 255, 0.1);
        }
        .nav-link i {
            margin-right: 10px;
            width: 20px;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-3 col-lg-2 d-md-block sidebar">
                <div class="user-info">
                    <div class="mb-3">
                        <i class="fas fa-user-circle fa-4x"></i>
                    </div>
                    <h5><?php echo htmlspecialchars($userName); ?></h5>
                    <small class="text-muted"><?php echo htmlspecialchars($userEmail); ?></small>
                </div>
                <ul class="nav flex-column mt-4">
                    <li class="nav-item">
                        <a class="nav-link active" href="#">
                            <i class="fas fa-home"></i> Inicio
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#">
                            <i class="fas fa-user"></i> Perfil
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#">
                            <i class="fas fa-users"></i> Amigos
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#">
                            <i class="fas fa-cog"></i> Configuración
                        </a>
                    </li>
                    <li class="nav-item mt-4">
                        <a class="nav-link text-danger" href="redsocial_logout.php">
                            <i class="fas fa-sign-out-alt"></i> Cerrar Sesión
                        </a>
                    </li>
                </ul>
            </div>

            <!-- Main content -->
            <div class="col-md-9 ms-sm-auto col-lg-10 px-md-4 main-content">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">Bienvenido, <?php echo htmlspecialchars(explode(' ', $userName)[0]); ?>!</h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <div class="btn-group me-2">
                            <button type="button" class="btn btn-sm btn-outline-secondary">Compartir</button>
                            <button type="button" class="btn btn-sm btn-outline-secondary">Exportar</button>
                        </div>
                        <button type="button" class="btn btn-sm btn-outline-secondary dropdown-toggle">
                            <i class="fas fa-calendar-alt"></i> Esta semana
                        </button>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-4 mb-4">
                        <div class="card text-white bg-primary">
                            <div class="card-body">
                                <h5 class="card-title">Amigos</h5>
                                <h2 class="card-text">1,234</h2>
                                <p class="card-text"><small>+15% desde la semana pasada</small></p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 mb-4">
                        <div class="card text-white bg-success">
                            <div class="card-body">
                                <h5 class="card-title">Publicaciones</h5>
                                <h2 class="card-text">89</h2>
                                <p class="card-text"><small>+5 esta semana</small></p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 mb-4">
                        <div class="card text-white bg-warning">
                            <div class="card-body">
                                <h5 class="card-title">Notificaciones</h5>
                                <h2 class="card-text">12</h2>
                                <p class="card-text"><small>3 sin leer</small></p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">Actividad Reciente</h5>
                    </div>
                    <div class="card-body">
                        <div class="d-flex mb-3">
                            <div class="flex-shrink-0">
                                <i class="fas fa-user-plus fa-2x text-primary"></i>
                            </div>
                            <div class="ms-3">
                                <h6 class="mb-0">Nuevo amigo</h6>
                                <p class="mb-0 text-muted">María García te ha enviado una solicitud de amistad</p>
                                <small class="text-muted">Hace 2 horas</small>
                            </div>
                        </div>
                        <div class="d-flex mb-3">
                            <div class="flex-shrink-0">
                                <i class="fas fa-thumbs-up fa-2x text-primary"></i>
                            </div>
                            <div class="ms-3">
                                <h6 class="mb-0">Nuevo me gusta</h6>
                                <p class="mb-0 text-muted">A Juan Pérez le gustó tu publicación</p>
                                <small class="text-muted">Hace 5 horas</small>
                            </div>
                        </div>
                        <div class="d-flex">
                            <div class="flex-shrink-0">
                                <i class="fas fa-comment fa-2x text-primary"></i>
                            </div>
                            <div class="ms-3">
                                <h6 class="mb-0">Nuevo comentario</h6>
                                <p class="mb-0 text-muted">Ana Martínez ha comentado en tu publicación</p>
                                <small class="text-muted">Ayer a las 14:30</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
