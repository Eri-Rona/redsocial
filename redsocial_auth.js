/**
 * Funcionalidad de autenticación para la red social
 */
document.addEventListener('DOMContentLoaded', function() {
    // Mostrar/ocultar contraseña
    const togglePassword = document.querySelector('#togglePassword');
    if (togglePassword) {
        togglePassword.addEventListener('click', function() {
            const password = document.querySelector('#password');
            const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
            password.setAttribute('type', type);
            const icon = this.querySelector('i');
            if (icon) {
                icon.classList.toggle('fa-eye');
                icon.classList.toggle('fa-eye-slash');
            }
        });
    }

    // Mostrar mensaje en el formulario
    function showMessage(message, type = 'error') {
        const messageDiv = document.getElementById('message');
        if (messageDiv) {
            messageDiv.className = `alert alert-${type === 'error' ? 'danger' : 'success'}`;
            messageDiv.textContent = message;
            messageDiv.classList.remove('d-none');
            
            // Desplazar la vista al mensaje
            messageDiv.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }
    }

    // Manejar envío del formulario de inicio de sesión
    const loginForm = document.getElementById('loginForm');
    if (loginForm) {
        loginForm.addEventListener('submit', async function(e) {
            e.preventDefault();
            
            // Obtener elementos del formulario
            const form = e.target;
            const formData = new FormData(form);
            const submitBtn = form.querySelector('button[type="submit"]');
            const originalBtnText = submitBtn.innerHTML;
            
            // Mostrar estado de carga
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Iniciando sesión...';
            
            // Ocultar mensajes anteriores
            const messageDiv = document.getElementById('message');
            if (messageDiv) {
                messageDiv.classList.add('d-none');
            }
            
            try {
                // Enviar solicitud al servidor
                const response = await fetch('redsocial_login.php', {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });
                
                // Verificar si la respuesta es JSON válido
                let data;
                try {
                    const text = await response.text();
                    data = text ? JSON.parse(text) : {};
                } catch (error) {
                    console.error('Error al analizar la respuesta JSON:', error);
                    throw new Error('La respuesta del servidor no es válida');
                }
                
                // Mostrar mensaje de respuesta
                showMessage(data.message || 'Operación completada', data.status || 'error');
                
                // Manejar redirección en caso de éxito
                if (data.status === 'success' && data.redirect) {
                    setTimeout(() => {
                        window.location.href = data.redirect;
                    }, 1500);
                    return;
                }
                
            } catch (error) {
                console.error('Error en la solicitud:', error);
                showMessage(error.message || 'Ocurrió un error al procesar la solicitud');
            } finally {
                // Restaurar el botón
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalBtnText;
            }
        });
    }

    // Handle registration form submission
    const registerForm = document.getElementById('registerForm');
    if (registerForm) {
        registerForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            // Validate password match
            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('confirm_password').value;
            
            if (password !== confirmPassword) {
                showMessage('Las contraseñas no coinciden', 'error');
                return;
            }
            
            // Validate password strength
            if (password.length < 8) {
                showMessage('La contraseña debe tener al menos 8 caracteres', 'error');
                return;
            }
            
            const formData = new FormData(this);
            
            // Show loading state
            const submitBtn = registerForm.querySelector('button[type="submit"]');
            const originalBtnText = submitBtn.innerHTML;
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Registrando...';
            
            fetch('redsocial_register.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                showMessage(data.message, data.status);
                
                if (data.status === 'success') {
                    // Reset form on success
                    registerForm.reset();
                    // Redirect to login after a short delay
                    if (data.redirect) {
                        setTimeout(() => {
                            window.location.href = data.redirect;
                        }, 2000);
                    }
                } else {
                    // Re-enable button on error
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = originalBtnText;
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showMessage('Ocurrió un error al procesar el registro', 'error');
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalBtnText;
            });
        });
    }
});

// Helper function to show messages
function showMessage(message, type = 'error') {
    const messageDiv = document.getElementById('message');
    if (messageDiv) {
        messageDiv.className = `alert alert-${type === 'error' ? 'danger' : 'success'}`;
        messageDiv.textContent = message;
        messageDiv.classList.remove('d-none');
        
        // Auto-hide after 5 seconds
        setTimeout(() => {
            messageDiv.classList.add('d-none');
        }, 5000);
    }
}
