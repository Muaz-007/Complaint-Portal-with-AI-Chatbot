</main>

<footer class="bg-dark text-light mt-5 py-4">
    <div class="container text-center small">
        <div class="mb-1">
            &copy; <?= date('Y') ?> <?= e(APP_NAME) ?> &middot; v<?= e(APP_VERSION) ?>
        </div>
        <div class="text-muted">
            Built with PHP &amp; MySQL &middot; AI-assisted complaint management
        </div>
    </div>
</footer>

<!-- jQuery (required by some Bootstrap plugins / future AJAX) -->
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

<!-- Bootstrap 5 bundle -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<!-- App scripts -->
<script src="<?= e(url('public/assets/js/script.js')) ?>"></script>
</body>
</html>
