// js/user.js
// Handles Delete Account button logic for user.php

document.addEventListener('DOMContentLoaded', function() {
  const deleteBtn = document.getElementById('delete-account-btn');
  if (deleteBtn) {
    deleteBtn.addEventListener('click', function() {
      if (!confirm('Are you sure you want to delete your account? This action cannot be undone.')) return;
      fetch('Persistence/UserRepository/delete-account.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' }
      })
        .then(res => res.json())
        .then(data => {
          if (data.success) {
            alert('Account deleted successfully.');
            window.location.href = 'logout.php';
          } else {
            alert(data.message || 'Failed to delete account.');
          }
        })
        .catch(() => alert('Server error.'));
    });
  }
});
