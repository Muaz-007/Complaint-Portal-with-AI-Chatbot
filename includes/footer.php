<?php
$auth_layout = $auth_layout ?? false;
?>
<?php if (!$auth_layout): ?>
</main>

<footer class="site-footer">
    <div class="container">
        <div class="row g-4">
            <div class="col-lg-4 col-md-6">
                <div class="footer-brand">
                    <span class="brand-mark"><i class="bi bi-mortarboard-fill text-white"></i></span>
                    <span><?= e(APP_NAME) ?></span>
                </div>
                <p class="small mb-0" style="color:#94a3b8;">
                    A modern complaint management portal built for universities — combining AI-powered
                    instant support with transparent, structured ticket resolution.
                </p>
            </div>

            <div class="col-lg-2 col-md-3 col-6">
                <h6>Portal</h6>
                <a href="<?= e(url('public/index.php')) ?>">Home</a><br>
                <a href="<?= e(url('public/login.php')) ?>">Login</a><br>
                <a href="<?= e(url('public/register.php')) ?>">Register</a>
            </div>

            <div class="col-lg-3 col-md-3 col-6">
                <h6>Departments</h6>
                <a href="#">Academics</a><br>
                <a href="#">Hostel</a><br>
                <a href="#">Finance</a><br>
                <a href="#">Examinations</a><br>
                <a href="#">IT Support</a>
            </div>

            <div class="col-lg-3 col-md-12">
                <h6>Need help?</h6>
                <p class="small mb-2" style="color:#94a3b8;">
                    Try our AI assistant for instant answers, or submit a formal complaint that gets routed
                    to the right department.
                </p>
                <a href="<?= e(url('public/login.php')) ?>" class="btn btn-primary">
                    Get Started <i class="bi bi-arrow-right ms-1"></i>
                </a>
            </div>
        </div>

        <div class="footer-bottom d-flex flex-wrap justify-content-between align-items-center gap-2">
            <div>&copy; <?= date('Y') ?> <?= e(APP_NAME) ?> · v<?= e(APP_VERSION) ?></div>
            <div>Built with PHP &amp; MySQL · AI-assisted complaint management</div>
        </div>
    </div>
</footer>
<?php endif; ?>

<!-- jQuery -->
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

<!-- Bootstrap 5 bundle -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<!-- App scripts -->
<script src="<?= e(url('public/assets/js/script.js')) ?>"></script>
</body>
</html>
