// Función para cargar publicaciones
document.addEventListener('DOMContentLoaded', function() {
    // Verificar si el usuario está autenticado
    const isAuthenticated = document.cookie.includes('usuario_id=');
    
    // Si el usuario no está autenticado, redirigir al login
    if (!isAuthenticated && !window.location.pathname.endsWith('login.html') && 
        !window.location.pathname.endsWith('registro.html')) {
        window.location.href = 'login.html';
        return;
    }

    // Inicializar tooltips
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });

    // Cargar publicaciones al iniciar
    cargarPublicaciones();

    // Configurar el formulario de publicación
    const publicacionForm = document.getElementById('publicacionForm');
    if (publicacionForm) {
        publicacionForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const texto = document.getElementById('publicacionTexto').value;
            const archivo = document.getElementById('imagenInput').files[0];
            
            if (texto.trim() === '' && !archivo) {
                mostrarAlerta('Por favor escribe algo o adjunta una imagen', 'warning');
                return;
            }

            // Mostrar indicador de carga
            const btnSubmit = publicacionForm.querySelector('button[type="submit"]');
            const btnOriginalText = btnSubmit.innerHTML;
            btnSubmit.disabled = true;
            btnSubmit.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Publicando...';

            // Simular envío al servidor
            setTimeout(() => {
                // Aquí iría el código para enviar al servidor
                console.log('Publicación enviada:', { texto, archivo });
                
                // Limpiar el formulario
                publicacionForm.reset();
                
                // Restaurar el botón
                btnSubmit.disabled = false;
                btnSubmit.innerHTML = btnOriginalText;
                
                // Mostrar mensaje de éxito
                mostrarAlerta('Publicación creada correctamente', 'success');
                
                // Recargar publicaciones
                cargarPublicaciones();
            }, 1500);
        });
    }

    // Configurar el botón de like
    document.addEventListener('click', function(e) {
        if (e.target.closest('.btn-like')) {
            const btnLike = e.target.closest('.btn-like');
            const publicacionId = btnLike.dataset.id;
            const icono = btnLike.querySelector('i');
            const contador = btnLike.querySelector('.contador');
            
            // Cambiar el estado del like
            const isLiked = btnLike.classList.toggle('liked');
            
            // Actualizar el ícono
            if (isLiked) {
                icono.className = 'bi bi-heart-fill';
                contador.textContent = parseInt(contador.textContent) + 1;
            } else {
                icono.className = 'bi bi-heart';
                contador.textContent = parseInt(contador.textContent) - 1;
            }
            
            // Aquí iría el código para actualizar el like en el servidor
            console.log(`Like ${isLiked ? 'agregado' : 'eliminado'} en la publicación ${publicacionId}`);
        }
        
        // Configurar el botón de comentario
        if (e.target.closest('.btn-comment')) {
            const publicacionId = e.target.closest('.btn-comment').dataset.id;
            const comentariosContainer = document.getElementById(`comentarios-${publicacionId}`);
            
            if (comentariosContainer) {
                comentariosContainer.style.display = 
                    comentariosContainer.style.display === 'none' ? 'block' : 'none';
                
                if (comentariosContainer.style.display === 'block') {
                    cargarComentarios(publicacionId);
                }
            }
        }
        
        // Configurar el envío de comentarios
        if (e.target.closest('.btn-enviar-comentario')) {
            const btn = e.target.closest('.btn-enviar-comentario');
            const publicacionId = btn.dataset.id;
            const input = document.getElementById(`comentario-${publicacionId}`);
            const texto = input.value.trim();
            
            if (texto) {
                // Aquí iría el código para enviar el comentario al servidor
                console.log(`Nuevo comentario en publicación ${publicacionId}:`, texto);
                
                // Simular envío exitoso
                const comentario = {
                    id: Date.now(),
                    usuario: 'Yo',
                    texto: texto,
                    fecha: 'Ahora mismo',
                    avatar: 'https://via.placeholder.com/40'
                };
                
                // Agregar el comentario a la interfaz
                agregarComentario(publicacionId, comentario);
                
                // Limpiar el input
                input.value = '';
            }
        }
    });

    // Configurar la carga de imágenes
    const imagenInput = document.getElementById('imagenInput');
    if (imagenInput) {
        imagenInput.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const preview = document.getElementById('imagenPreview');
                    if (!preview) {
                        const previewContainer = document.createElement('div');
                        previewContainer.className = 'mt-2 position-relative';
                        previewContainer.innerHTML = `
                            <img id="imagenPreview" src="${e.target.result}" class="img-fluid rounded" style="max-height: 300px; object-fit: cover;">
                            <button type="button" class="btn btn-danger btn-sm position-absolute top-0 end-0 m-2" onclick="this.parentElement.remove()">
                                <i class="bi bi-x-lg"></i>
                            </button>
                        `;
                        publicacionForm.insertBefore(previewContainer, publicacionForm.lastElementChild);
                    } else {
                        preview.src = e.target.result;
                    }
                };
                reader.readAsDataURL(file);
            }
        });
    }
});

// Función para cargar publicaciones
function cargarPublicaciones() {
    const publicacionesContainer = document.getElementById('publicaciones');
    if (!publicacionesContainer) return;
    
    // Mostrar indicador de carga
    publicacionesContainer.innerHTML = `
        <div class="text-center my-5">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Cargando...</span>
            </div>
            <p class="mt-2">Cargando publicaciones...</p>
        </div>
    `;
    
    // Simular carga de publicaciones desde el servidor
    setTimeout(() => {
        // Datos de ejemplo (en un caso real, esto vendría de una API)
        const publicaciones = [
            {
                id: 1,
                usuario: 'Juan Pérez',
                avatar: 'https://via.placeholder.com/40',
                texto: '¡Hola a todos! Esta es mi primera publicación en la red social. ¡Espero que les guste!',
                imagen: 'https://via.placeholder.com/600x400',
                fecha: 'Hace 2 horas',
                meGusta: false,
                numMeGusta: 5,
                numComentarios: 3
            },
            {
                id: 2,
                usuario: 'Ana García',
                avatar: 'https://via.placeholder.com/40/cccccc/ffffff',
                texto: 'Compartiendo un hermoso día en el parque. ¡El clima está perfecto!',
                imagen: 'https://via.placeholder.com/600x400/cccccc/ffffff',
                fecha: 'Hace 5 horas',
                meGusta: true,
                numMeGusta: 12,
                numComentarios: 4
            },
            {
                id: 3,
                usuario: 'Carlos López',
                avatar: 'https://via.placeholder.com/40/999999/ffffff',
                texto: 'Acabo de terminar mi último proyecto. ¡Estoy muy emocionado de compartirlo con todos ustedes!',
                fecha: 'Ayer',
                meGusta: false,
                numMeGusta: 8,
                numComentarios: 2
            }
        ];
        
        // Generar el HTML de las publicaciones
        let html = '';
        publicaciones.forEach(publicacion => {
            html += `
                <div class="card mb-4">
                    <div class="card-body">
                        <div class="d-flex mb-3">
                            <img src="${publicacion.avatar}" class="rounded-circle me-2" width="40" height="40" alt="${publicacion.usuario}">
                            <div>
                                <h6 class="mb-0">${publicacion.usuario}</h6>
                                <small class="text-muted">${publicacion.fecha}</small>
                            </div>
                        </div>
                        <p class="card-text">${publicacion.texto}</p>
                        ${publicacion.imagen ? `<img src="${publicacion.imagen}" class="img-fluid rounded mb-3" alt="Publicación" style="cursor: pointer;" onclick="verImagen('${publicacion.imagen}')">` : ''}
                        
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <div class="interacciones">
                                <button class="btn-like ${publicacion.meGusta ? 'liked' : ''}" data-id="${publicacion.id}">
                                    <i class="bi ${publicacion.meGusta ? 'bi-heart-fill text-danger' : 'bi-heart'}"></i> 
                                    <span class="contador">${publicacion.numMeGusta}</span>
                                </button>
                                <button class="btn-comment ms-3" data-id="${publicacion.id}">
                                    <i class="bi bi-chat"></i> 
                                    <span>${publicacion.numComentarios}</span>
                                </button>
                            </div>
                            <button class="btn btn-link text-muted p-0" data-bs-toggle="dropdown">
                                <i class="bi bi-three-dots"></i>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li><a class="dropdown-item" href="#"><i class="bi bi-bookmark me-2"></i>Guardar</a></li>
                                <li><a class="dropdown-item" href="#"><i class="bi bi-share me-2"></i>Compartir</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item text-danger" href="#"><i class="bi bi-flag me-2"></i>Reportar</a></li>
                            </ul>
                        </div>
                        
                        <div class="comentarios" id="comentarios-${publicacion.id}" style="display: none;">
                            <div id="lista-comentarios-${publicacion.id}">
                                <!-- Los comentarios se cargarán aquí -->
                            </div>
                            <div class="d-flex mt-2">
                                <input type="text" class="form-control form-control-sm me-2" id="comentario-${publicacion.id}" placeholder="Escribe un comentario...">
                                <button class="btn btn-primary btn-sm btn-enviar-comentario" data-id="${publicacion.id}">Enviar</button>
                            </div>
                        </div>
                    </div>
                </div>
            `;
        });
        
        // Insertar las publicaciones en el contenedor
        publicacionesContainer.innerHTML = html || `
            <div class="text-center py-5">
                <i class="bi bi-newspaper" style="font-size: 3rem; color: #dee2e6;"></i>
                <p class="mt-3 text-muted">No hay publicaciones para mostrar. ¡Sé el primero en publicar algo!</p>
            </div>
        `;
    }, 1000);
}

// Función para cargar comentarios
function cargarComentarios(publicacionId) {
    const listaComentarios = document.getElementById(`lista-comentarios-${publicacionId}`);
    if (!listaComentarios) return;
    
    // Mostrar indicador de carga
    listaComentarios.innerHTML = `
        <div class="text-center py-2">
            <div class="spinner-border spinner-border-sm" role="status">
                <span class="visually-hidden">Cargando...</span>
            </div>
        </div>
    `;
    
    // Simular carga de comentarios desde el servidor
    setTimeout(() => {
        // Datos de ejemplo (en un caso real, esto vendría de una API)
        const comentarios = [
            {
                id: 1,
                usuario: 'María González',
                avatar: 'https://via.placeholder.com/40/999999/ffffff',
                texto: '¡Excelente publicación! Me encantó mucho.',
                fecha: 'Hace 1 hora'
            },
            {
                id: 2,
                usuario: 'Pedro Sánchez',
                avatar: 'https://via.placeholder.com/40/666666/ffffff',
                texto: 'Muy interesante, gracias por compartir.',
                fecha: 'Hace 30 minutos'
            }
        ];
        
        // Generar el HTML de los comentarios
        let html = '';
        comentarios.forEach(comentario => {
            html += `
                <div class="d-flex mb-2">
                    <img src="${comentario.avatar}" class="rounded-circle me-2" width="32" height="32" alt="${comentario.usuario}">
                    <div class="bg-light rounded-3 p-2 flex-grow-1">
                        <div class="d-flex justify-content-between align-items-center">
                            <strong class="small">${comentario.usuario}</strong>
                            <small class="text-muted">${comentario.fecha}</small>
                        </div>
                        <p class="mb-0 small">${comentario.texto}</p>
                    </div>
                </div>
            `;
        });
        
        // Insertar los comentarios en el contenedor
        listaComentarios.innerHTML = html || `
            <div class="text-center py-2 text-muted small">
                No hay comentarios aún. ¡Sé el primero en comentar!
            </div>
        `;
    }, 800);
}

// Función para agregar un comentario
function agregarComentario(publicacionId, comentario) {
    const listaComentarios = document.getElementById(`lista-comentarios-${publicacionId}`);
    if (!listaComentarios) return;
    
    // Crear el elemento del comentario
    const comentarioElement = document.createElement('div');
    comentarioElement.className = 'd-flex mb-2 fade-in';
    comentarioElement.innerHTML = `
        <img src="${comentario.avatar}" class="rounded-circle me-2" width="32" height="32" alt="${comentario.usuario}">
        <div class="bg-light rounded-3 p-2 flex-grow-1">
            <div class="d-flex justify-content-between align-items-center">
                <strong class="small">${comentario.usuario}</strong>
                <small class="text-muted">${comentario.fecha}</small>
            </div>
            <p class="mb-0 small">${comentario.texto}</p>
        </div>
    `;
    
    // Agregar el comentario al principio de la lista
    if (listaComentarios.firstChild) {
        listaComentarios.insertBefore(comentarioElement, listaComentarios.firstChild);
    } else {
        listaComentarios.appendChild(comentarioElement);
    }
    
    // Actualizar el contador de comentarios
    const btnComment = document.querySelector(`.btn-comment[data-id="${publicacionId}"]`);
    if (btnComment) {
        const contador = btnComment.querySelector('span:last-child');
        if (contador) {
            contador.textContent = parseInt(contador.textContent || '0') + 1;
        }
    }
}

// Función para mostrar una imagen en un modal
function verImagen(src) {
    const modal = new bootstrap.Modal(document.getElementById('imagenModal'));
    const img = document.getElementById('imagenAmpliada');
    if (img) {
        img.src = src;
        modal.show();
    }
}

// Función para mostrar una alerta
function mostrarAlerta(mensaje, tipo = 'info') {
    // Crear el elemento de la alerta
    const alerta = document.createElement('div');
    alerta.className = `alert alert-${tipo} alert-dismissible fade show position-fixed top-0 end-0 m-3`;
    alerta.role = 'alert';
    alerta.style.zIndex = '1100';
    alerta.innerHTML = `
        ${mensaje}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
    `;
    
    // Agregar la alerta al cuerpo del documento
    document.body.appendChild(alerta);
    
    // Eliminar la alerta después de 5 segundos
    setTimeout(() => {
        const bsAlert = new bootstrap.Alert(alerta);
        bsAlert.close();
    }, 5000);
}

// Hacer las funciones accesibles globalmente
window.verImagen = verImagen;
