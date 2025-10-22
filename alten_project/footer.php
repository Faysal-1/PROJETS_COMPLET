    </main>

    <!-- Footer -->
    <footer class="bg-dark text-light py-4 mt-5">
        <div class="container">
            <div class="row">
                <div class="col-md-6">
                    <h5><i class="fas fa-building me-2"></i>ALTEN Maroc</h5>
                    <p class="mb-0">Système de gestion des clients, projets et documents</p>
                    <small class="text-muted">Version 1.0 - Développé par Fayssal Rajouani</small>
                </div>
                <div class="col-md-6 text-md-end">
                    <p class="mb-0">
                        <i class="fas fa-envelope me-2"></i>contact@alten.ma<br>
                        <i class="fas fa-phone me-2"></i>+212 522 123 456
                    </p>
                    <small class="text-muted">© <?php echo date('Y'); ?> ALTEN Maroc. Tous droits réservés.</small>
                </div>
            </div>
        </div>
    </footer>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Custom JS -->
    <script src="<?php echo $base_url ?? ''; ?>assets/js/script.js"></script>
    
    <!-- Notifications Toast Container -->
    <div class="toast-container position-fixed bottom-0 end-0 p-3">
        <div id="liveToast" class="toast" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="toast-header">
                <i class="fas fa-info-circle text-primary me-2"></i>
                <strong class="me-auto">Notification</strong>
                <button type="button" class="btn-close" data-bs-dismiss="toast"></button>
            </div>
            <div class="toast-body"></div>
        </div>
    </div>
</body>
</html>