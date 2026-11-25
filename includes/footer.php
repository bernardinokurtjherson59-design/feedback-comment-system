<?php /* File: includes/footer.php | Purpose: Render the shared closing container, About modal,
and browser scripts. Access: Guest, user, and admin pages. Tables: None. */ ?></div>

<!-- About the website (opened from the Menu) -->
<div class="modal fade" id="aboutModal" tabindex="-1" aria-labelledby="aboutTitle" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="aboutTitle">About this website</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <p>The <strong>Online Feedback and Comment System</strong> is a place where registered users share feedback, read what others have posted, and discuss it through comments.</p>
        <p class="mb-0">Admins review every submission and mark it <em>New</em>, <em>Reviewed</em> or <em>Resolved</em>. Built with PHP, MySQL and Bootstrap 5 for the Web Systems Technologies final project.</p>
      </div>
      <div class="modal-footer"><button type="button" class="btn btn-primary" data-bs-dismiss="modal">Got it</button></div>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="assets/app.js"></script>
</body>
</html>
