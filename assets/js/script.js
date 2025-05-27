// Date validation for booking form
document.addEventListener("DOMContentLoaded", function () {
  const checkInInput = document.getElementById("check_in");
  const checkOutInput = document.getElementById("check_out");

  if (checkInInput && checkOutInput) {
    // Set minimum date as today
    const today = new Date().toISOString().split("T")[0];
    checkInInput.min = today;

    checkInInput.addEventListener("change", function () {
      checkOutInput.min = checkInInput.value;
      if (checkOutInput.value && checkOutInput.value <= checkInInput.value) {
        checkOutInput.value = "";
      }
    });
  }
});

// Card number formatting
document.addEventListener("DOMContentLoaded", function () {
  const cardInput = document.getElementById("card_number");
  if (cardInput) {
    cardInput.addEventListener("input", function (e) {
      let value = e.target.value.replace(/\D/g, "");
      value = value.replace(/(\d{4})(?=\d)/g, "$1 ");
      e.target.value = value;
    });
  }
});

// Expiry date formatting
document.addEventListener("DOMContentLoaded", function () {
  const expiryInput = document.getElementById("expiry_date");
  if (expiryInput) {
    expiryInput.addEventListener("input", function (e) {
      let value = e.target.value.replace(/\D/g, "");
      if (value.length > 2) {
        value = value.substring(0, 2) + "/" + value.substring(2, 4);
      }
      e.target.value = value;
    });
  }
});

// Room image preview
function previewImage(input) {
  if (input.files && input.files[0]) {
    const reader = new FileReader();
    reader.onload = function (e) {
      document.getElementById("room-image-preview").src = e.target.result;
    };
    reader.readAsDataURL(input.files[0]);
  }
}

// Confirm deletion
function confirmDelete(id, type) {
  return confirm(`Are you sure you want to delete this ${type}?`);
}
