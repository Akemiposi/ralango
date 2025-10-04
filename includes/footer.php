<?php
// includes/footer.php

// JSパスを正しく生成
function getJSFooterPath() {
    $current_dir = dirname($_SERVER['SCRIPT_NAME']);
    if ($current_dir === '/') {
        return '/assets/js/app.js';
    }
    
    if (strpos($current_dir, '/auth') !== false || 
        strpos($current_dir, '/lessons') !== false || 
        strpos($current_dir, '/progress') !== false) {
        return '../assets/js/app.js';
    }
    
    return 'assets/js/app.js';
}
?>

</div><!-- .container -->

<footer class="main-footer">
    <div class="footer-content">
        <p>&copy; nihongonote all rights reserved.</p>
    </div>
</footer>

<script src="<?= getJSFooterPath() ?>"></script>
<?php if (isset($additional_js)): ?>
    <?= $additional_js ?>
<?php endif; ?>

<?php if (isset($user_data_json)): ?>
<script id="userData" type="application/json"><?= $user_data_json ?></script>
<?php endif; ?>

</body>
</html>