function selectRole(role) {
  if (role === 'Resident') {
    window.location.href = 'resident_portal.php';
  } else if (role === 'Barangay Official') {
    window.location.href = 'official_dashboard.html';
  } else if (role === 'Admin') {
    window.location.href = 'admin_portal.php';
  }
}