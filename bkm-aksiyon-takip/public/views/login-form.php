<?php
/**
 * Login Formu View - Frontend
 * Bu dosya kullanıcı giriş formunu görüntüler
 */

if (!defined('ABSPATH')) {
    exit;
}

// Parametreler
$redirect_url = $atts['redirect_url'] ?: '';
$show_register = $atts['show_register'] === 'true';
$title = $atts['title'] ?: 'Giriş Yap';
?>

<div class="bkm-frontend bkm-login-page">
    <div class="bkm-login-container">
        
        <!-- Logo/Başlık -->
        <div class="bkm-login-header">
            <div class="bkm-login-logo">
                <i class="dashicons dashicons-admin-network"></i>
            </div>
            <h3><?php echo esc_html($title); ?></h3>
            <p>BKM Aksiyon Takip Sistemi</p>
        </div>

        <!-- Login Form -->
        <div class="bkm-login-form">
            <form method="post" data-redirect-url="<?php echo esc_attr($redirect_url); ?>">
                <?php wp_nonce_field('bkm_frontend_login', 'bkm_nonce'); ?>
                
                <div class="bkm-form-group">
                    <label for="bkm_username">
                        <i class="dashicons dashicons-admin-users"></i>
                        Kullanıcı Adı veya E-posta
                    </label>
                    <input type="text" id="bkm_username" name="username" class="bkm-form-control" 
                           placeholder="Kullanıcı adınızı girin..." required autocomplete="username">
                </div>
                
                <div class="bkm-form-group">
                    <label for="bkm_password">
                        <i class="dashicons dashicons-lock"></i>
                        Şifre
                    </label>
                    <div class="bkm-password-field">
                        <input type="password" id="bkm_password" name="password" class="bkm-form-control" 
                               placeholder="Şifrenizi girin..." required autocomplete="current-password">
                        <button type="button" class="bkm-password-toggle" onclick="togglePassword()">
                            <i class="dashicons dashicons-visibility"></i>
                        </button>
                    </div>
                </div>
                
                <div class="bkm-form-group bkm-form-options">
                    <label class="bkm-checkbox-label">
                        <input type="checkbox" name="remember_me" value="1">
                        <span class="bkm-checkbox-custom"></span>
                        Beni hatırla
                    </label>
                    
                    <a href="<?php echo wp_lostpassword_url(); ?>" class="bkm-forgot-password">
                        Şifremi unuttum
                    </a>
                </div>
                
                <div class="bkm-form-group">
                    <input type="submit" value="Giriş Yap" class="bkm-btn bkm-btn-primary bkm-btn-full bkm-btn-large">
                </div>
                
            </form>
        </div>

        <?php if ($show_register): ?>
        <!-- Register Link -->
        <div class="bkm-register-link">
            <p>Hesabınız yok mu? <a href="<?php echo wp_registration_url(); ?>">Kayıt olun</a></p>
        </div>
        <?php endif; ?>

        <!-- Footer Info -->
        <div class="bkm-login-footer">
            <div class="bkm-login-features">
                <div class="bkm-feature">
                    <i class="dashicons dashicons-yes-alt"></i>
                    <span>Aksiyon Takibi</span>
                </div>
                <div class="bkm-feature">
                    <i class="dashicons dashicons-chart-bar"></i>
                    <span>Performans Raporları</span>
                </div>
                <div class="bkm-feature">
                    <i class="dashicons dashicons-groups"></i>
                    <span>Takım Yönetimi</span>
                </div>
            </div>
            
            <div class="bkm-login-help">
                <p><strong>Demo Hesapları:</strong></p>
                <small>
                    <strong>Yönetici:</strong> admin / admin123<br>
                    <strong>Editör:</strong> editor / editor123
                </small>
            </div>
        </div>
        
    </div>
</div>

<style>
/* Login sayfası özel stiller */
.bkm-login-page {
    min-height: 100vh;
    background: linear-gradient(135deg, #007cba 0%, #00a0d2 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 20px;
}

.bkm-login-container {
    background: #fff;
    border-radius: 15px;
    box-shadow: 0 10px 40px rgba(0,0,0,0.15);
    overflow: hidden;
    width: 100%;
    max-width: 450px;
    animation: fadeInUp 0.6s ease-out;
}

@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(30px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.bkm-login-header {
    background: linear-gradient(135deg, #2c3e50, #3498db);
    color: #fff;
    text-align: center;
    padding: 40px 30px;
}

.bkm-login-logo {
    font-size: 48px;
    margin-bottom: 15px;
    opacity: 0.9;
}

.bkm-login-header h3 {
    margin: 0 0 8px 0;
    font-size: 24px;
    font-weight: 600;
}

.bkm-login-header p {
    margin: 0;
    opacity: 0.8;
    font-size: 14px;
}

.bkm-login-form {
    padding: 40px 30px;
}

.bkm-form-group {
    margin-bottom: 25px;
}

.bkm-form-group label {
    display: flex;
    align-items: center;
    gap: 8px;
    margin-bottom: 8px;
    font-weight: 600;
    color: #2c3e50;
    font-size: 14px;
}

.bkm-form-control {
    width: 100%;
    padding: 14px 16px;
    border: 2px solid #e1e5e9;
    border-radius: 8px;
    font-size: 16px;
    transition: all 0.3s ease;
    background: #fff;
    box-sizing: border-box;
}

.bkm-form-control:focus {
    outline: none;
    border-color: #007cba;
    box-shadow: 0 0 0 3px rgba(0, 124, 186, 0.1);
}

.bkm-password-field {
    position: relative;
}

.bkm-password-toggle {
    position: absolute;
    right: 12px;
    top: 50%;
    transform: translateY(-50%);
    background: none;
    border: none;
    color: #666;
    cursor: pointer;
    padding: 8px;
    border-radius: 4px;
    transition: color 0.3s ease;
}

.bkm-password-toggle:hover {
    color: #007cba;
}

.bkm-form-options {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 30px;
}

.bkm-checkbox-label {
    display: flex;
    align-items: center;
    gap: 8px;
    cursor: pointer;
    font-size: 14px;
    color: #555;
}

.bkm-checkbox-label input[type="checkbox"] {
    display: none;
}

.bkm-checkbox-custom {
    width: 18px;
    height: 18px;
    border: 2px solid #ddd;
    border-radius: 3px;
    position: relative;
    transition: all 0.3s ease;
}

.bkm-checkbox-label input[type="checkbox"]:checked + .bkm-checkbox-custom {
    background: #007cba;
    border-color: #007cba;
}

.bkm-checkbox-label input[type="checkbox"]:checked + .bkm-checkbox-custom:after {
    content: "✓";
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    color: #fff;
    font-size: 12px;
    font-weight: bold;
}

.bkm-forgot-password {
    color: #007cba;
    text-decoration: none;
    font-size: 14px;
    transition: color 0.3s ease;
}

.bkm-forgot-password:hover {
    color: #005a87;
    text-decoration: underline;
}

.bkm-btn-large {
    padding: 16px 24px;
    font-size: 16px;
    font-weight: 600;
}

.bkm-register-link {
    text-align: center;
    padding: 20px 30px;
    background: #f8f9fa;
    border-top: 1px solid #e1e5e9;
}

.bkm-register-link p {
    margin: 0;
    color: #666;
    font-size: 14px;
}

.bkm-register-link a {
    color: #007cba;
    text-decoration: none;
    font-weight: 600;
}

.bkm-register-link a:hover {
    text-decoration: underline;
}

.bkm-login-footer {
    background: #f8f9fa;
    padding: 25px 30px;
    border-top: 1px solid #e1e5e9;
}

.bkm-login-features {
    display: flex;
    justify-content: space-around;
    margin-bottom: 20px;
}

.bkm-feature {
    display: flex;
    flex-direction: column;
    align-items: center;
    text-align: center;
    color: #666;
    font-size: 12px;
}

.bkm-feature i {
    font-size: 20px;
    color: #007cba;
    margin-bottom: 5px;
}

.bkm-login-help {
    text-align: center;
    padding-top: 15px;
    border-top: 1px solid #e1e5e9;
}

.bkm-login-help p {
    margin: 0 0 8px 0;
    color: #666;
    font-size: 12px;
}

.bkm-login-help small {
    color: #888;
    line-height: 1.4;
}

/* Loading state */
.bkm-login-form.loading .bkm-btn-primary {
    opacity: 0.7;
    cursor: not-allowed;
    position: relative;
}

.bkm-login-form.loading .bkm-btn-primary:after {
    content: "";
    position: absolute;
    right: 15px;
    top: 50%;
    transform: translateY(-50%);
    width: 16px;
    height: 16px;
    border: 2px solid #fff;
    border-top: 2px solid transparent;
    border-radius: 50%;
    animation: bkm-spin 1s linear infinite;
}

/* Responsive */
@media (max-width: 480px) {
    .bkm-login-page {
        padding: 10px;
    }
    
    .bkm-login-container {
        border-radius: 10px;
    }
    
    .bkm-login-header {
        padding: 30px 20px;
    }
    
    .bkm-login-form {
        padding: 30px 20px;
    }
    
    .bkm-login-footer {
        padding: 20px;
    }
    
    .bkm-login-features {
        flex-direction: column;
        gap: 15px;
    }
    
    .bkm-feature {
        flex-direction: row;
        gap: 10px;
    }
    
    .bkm-form-options {
        flex-direction: column;
        gap: 15px;
        align-items: flex-start;
    }
}

/* Error states */
.bkm-form-control.error {
    border-color: #dc3545;
    box-shadow: 0 0 0 3px rgba(220, 53, 69, 0.1);
}

.bkm-error-message {
    color: #dc3545;
    font-size: 12px;
    margin-top: 5px;
    display: flex;
    align-items: center;
    gap: 5px;
}
</style>

<script>
function togglePassword() {
    const passwordField = document.getElementById('bkm_password');
    const toggleBtn = document.querySelector('.bkm-password-toggle i');
    
    if (passwordField.type === 'password') {
        passwordField.type = 'text';
        toggleBtn.className = 'dashicons dashicons-hidden';
    } else {
        passwordField.type = 'password';
        toggleBtn.className = 'dashicons dashicons-visibility';
    }
}

jQuery(document).ready(function($) {
    // Form submission handling
    $('.bkm-login-form form').on('submit', function(e) {
        const $form = $(this);
        const $submitBtn = $form.find('input[type="submit"]');
        
        // Clear previous errors
        $('.bkm-form-control').removeClass('error');
        $('.bkm-error-message').remove();
        
        // Basic validation
        let isValid = true;
        $form.find('[required]').each(function() {
            if (!$(this).val().trim()) {
                $(this).addClass('error');
                $(this).after('<div class="bkm-error-message"><i class="dashicons dashicons-warning"></i>Bu alan zorunludur.</div>');
                isValid = false;
            }
        });
        
        if (!isValid) {
            e.preventDefault();
            return false;
        }
        
        // Show loading state
        $form.addClass('loading');
        $submitBtn.prop('disabled', true).val('Giriş yapılıyor...');
    });
    
    // Clear error on focus
    $('.bkm-form-control').on('focus', function() {
        $(this).removeClass('error');
        $(this).next('.bkm-error-message').remove();
    });
    
    // Enter key support
    $('.bkm-form-control').on('keypress', function(e) {
        if (e.which === 13) {
            $(this).closest('form').submit();
        }
    });
});
</script>