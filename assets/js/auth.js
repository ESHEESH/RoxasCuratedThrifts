/**
 * Authentication JavaScript
 * 
 * Handles password toggle, strength indicator, and form validation
 * for authentication pages.
 * 
 * @author Thrift Store Team
 * @version 1.0
 */

document.addEventListener('DOMContentLoaded', function() {
    
    // =====================================================
    // PASSWORD TOGGLE
    // =====================================================
    
    /**
     * Toggle password visibility
     * @param {HTMLElement} button - The toggle button
     */
    function togglePassword(button) {
        const targetId = button.dataset.target;
        const input = document.getElementById(targetId);
        
        if (input) {
            const isPassword = input.type === 'password';
            input.type = isPassword ? 'text' : 'password';
            button.classList.toggle('showing', isPassword);
            button.setAttribute('aria-label', isPassword ? 'Hide password' : 'Show password');
        }
    }
    
    // Attach toggle handlers
    document.querySelectorAll('.toggle-password').forEach(button => {
        button.addEventListener('click', () => togglePassword(button));
    });
    
    // =====================================================
    // PASSWORD STRENGTH INDICATOR
    // =====================================================
    
    const passwordInput = document.getElementById('password');
    
    if (passwordInput) {
        const strengthFill = document.getElementById('strengthFill');
        const strengthText = document.getElementById('strengthText');
        const requirements = document.getElementById('passwordRequirements');
        
        /**
         * Check password strength and update UI
         * @param {string} password - The password to check
         */
        function checkPasswordStrength(password) {
            const checks = {
                length: password.length >= 8,
                uppercase: /[A-Z]/.test(password),
                lowercase: /[a-z]/.test(password),
                number: /[0-9]/.test(password),
                special: /[!@#$%^&*()_+\-=\[\]{};':"\\|,.<>\/?]/.test(password)
            };
            
            // Update requirement indicators
            if (requirements) {
                requirements.querySelectorAll('li').forEach(li => {
                    const requirement = li.dataset.requirement;
                    if (requirement && checks[requirement]) {
                        li.classList.add('met');
                    } else {
                        li.classList.remove('met');
                    }
                });
            }
            
            // Calculate strength
            const metCount = Object.values(checks).filter(Boolean).length;
            let strength = '';
            let text = '';
            
            if (password.length === 0) {
                strength = '';
                text = 'Enter a password';
            } else if (metCount <= 2) {
                strength = 'weak';
                text = 'Weak password';
            } else if (metCount <= 3) {
                strength = 'fair';
                text = 'Fair password';
            } else if (metCount <= 4) {
                strength = 'good';
                text = 'Good password';
            } else {
                strength = 'strong';
                text = 'Strong password';
            }
            
            // Update strength indicator
            if (strengthFill) {
                strengthFill.className = 'strength-fill ' + strength;
            }
            if (strengthText) {
                strengthText.textContent = text;
            }
            
            return metCount === 5;
        }
        
        // Attach strength checker
        passwordInput.addEventListener('input', (e) => {
            checkPasswordStrength(e.target.value);
        });
        
        // Initial check
        checkPasswordStrength(passwordInput.value);
    }
    
    // =====================================================
    // FORM VALIDATION
    // =====================================================
    
    const registerForm = document.getElementById('registerForm');
    
    if (registerForm) {
        registerForm.addEventListener('submit', function(e) {
            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('confirm_password').value;
            const terms = document.getElementById('terms');
            
            // Check password match
            if (password !== confirmPassword) {
                e.preventDefault();
                alert('Passwords do not match!');
                return;
            }
            
            // Check terms
            if (!terms.checked) {
                e.preventDefault();
                alert('Please agree to the Terms of Service and Privacy Policy.');
                return;
            }
            
            // Check password strength
            const checks = {
                length: password.length >= 8,
                uppercase: /[A-Z]/.test(password),
                lowercase: /[a-z]/.test(password),
                number: /[0-9]/.test(password),
                special: /[!@#$%^&*()_+\-=\[\]{};':"\\|,.<>\/?]/.test(password)
            };
            
            const metCount = Object.values(checks).filter(Boolean).length;
            if (metCount < 5) {
                e.preventDefault();
                alert('Please meet all password requirements.');
                return;
            }
        });
    }
    
    // =====================================================
    // BIRTHDATE VALIDATION
    // =====================================================
    
    const birthdateInput = document.getElementById('birthdate');
    
    if (birthdateInput) {
        // Set max date to 13 years ago
        const today = new Date();
        const maxDate = new Date(today.getFullYear() - 13, today.getMonth(), today.getDate());
        birthdateInput.max = maxDate.toISOString().split('T')[0];
    }
    
});
