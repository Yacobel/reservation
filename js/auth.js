// Form validation and animations
document.addEventListener("DOMContentLoaded", function () {
  // Password strength indicator for registration
  const passwordInput = document.getElementById("password");
  const strengthIndicator = document.createElement("div");
  strengthIndicator.className = "password-strength";

  if (passwordInput) {
    // Add strength indicator after password field
    passwordInput.parentNode.appendChild(strengthIndicator);

    // Check password strength in real-time
    passwordInput.addEventListener("input", function () {
      const password = this.value;
      const strength = checkPasswordStrength(password);
      updateStrengthIndicator(strength);
    });
  }

  // Form validation with nice animations
  const forms = document.querySelectorAll(".auth-form");
  forms.forEach((form) => {
    const inputs = form.querySelectorAll(".form-input");
    const formId = form.getAttribute('id');

    inputs.forEach((input) => {
      // Check if input already has a value (e.g., from browser autofill)
      if (input.value) {
        input.parentNode.classList.add("focused");
      }
      
      // Add floating label animation
      input.addEventListener("focus", () => {
        input.parentNode.classList.add("focused");
        input.classList.add("active");
      });

      input.addEventListener("blur", () => {
        input.classList.remove("active");
        if (!input.value) {
          input.parentNode.classList.remove("focused");
        }
        validateInput(input);
      });

      // Real-time validation after user starts typing
      input.addEventListener("input", () => {
        if (input.dataset.validating === 'true') {
          validateInput(input);
        }
      });
      
      // Mark input for validation after first interaction
      input.addEventListener("change", () => {
        input.dataset.validating = 'true';
        validateInput(input);
      });
    });

    // Form submission handling with loading animation
    form.addEventListener("submit", function (e) {
      e.preventDefault();
      
      // Mark all inputs for validation
      inputs.forEach(input => {
        input.dataset.validating = 'true';
      });
      
      if (validateForm(this)) {
        const resetLoading = showLoadingState(this);
        
        // Simulate API call with loading state
        setTimeout(() => {
          this.submit();
        }, 800);
      } else {
        // Shake effect for invalid form
        form.classList.add('shake');
        setTimeout(() => {
          form.classList.remove('shake');
        }, 650);
        
        // Scroll to first error
        const firstError = form.querySelector('.invalid');
        if (firstError) {
          firstError.focus();
        }
      }
    });
  });
  
  // Add subtle animation to auth box
  const authBox = document.querySelector('.auth-box');
  if (authBox) {
    setTimeout(() => {
      authBox.classList.add('loaded');
    }, 100);
  }
});

// Password strength checker
function checkPasswordStrength(password) {
  let strength = 0;

  if (password.length >= 8) strength++;
  if (password.match(/[a-z]+/)) strength++;
  if (password.match(/[A-Z]+/)) strength++;
  if (password.match(/[0-9]+/)) strength++;
  if (password.match(/[!@#$%^&*(),.?":{}|<>]+/)) strength++;

  return strength;
}

function updateStrengthIndicator(strength) {
  const indicator = document.querySelector(".password-strength");
  const messages = ["Very Weak", "Weak", "Medium", "Strong", "Very Strong"];
  const colors = ["#ff4444", "#ffbb33", "#ffeb3b", "#00C851", "#007E33"];

  indicator.textContent = messages[strength - 1] || "";
  indicator.style.color = colors[strength - 1] || "";
  indicator.style.opacity = strength ? 1 : 0;
}

// Input validation
function validateInput(input) {
  const value = input.value.trim();
  let isValid = true;
  let errorMessage = "";

  switch (input.type) {
    case "email":
      const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
      isValid = emailRegex.test(value);
      errorMessage = "Please enter a valid email address";
      break;
    case "password":
      isValid = value.length >= 8;
      errorMessage = "Password must be at least 8 characters";
      break;
    case "text":
      isValid = value.length >= 2;
      errorMessage = "This field is required";
      break;
  }

  toggleError(input, isValid, errorMessage);
  return isValid;
}

function toggleError(input, isValid, message) {
  const errorDiv =
    input.parentNode.querySelector(".error-message") ||
    document.createElement("div");
  errorDiv.className = "error-message";
  errorDiv.textContent = isValid ? "" : message;

  if (!input.parentNode.querySelector(".error-message")) {
    input.parentNode.appendChild(errorDiv);
  }

  input.classList.toggle("invalid", !isValid);
}

function validateForm(form) {
  const inputs = form.querySelectorAll(".form-input");
  let isValid = true;

  inputs.forEach((input) => {
    if (!validateInput(input)) {
      isValid = false;
    }
  });

  return isValid;
}

function showLoadingState(form) {
  const button = form.querySelector('button[type="submit"]');
  const originalText = button.textContent;

  button.disabled = true;
  button.innerHTML = '<span class="spinner"></span> Processing...';

  return () => {
    button.disabled = false;
    button.textContent = originalText;
  };
}
