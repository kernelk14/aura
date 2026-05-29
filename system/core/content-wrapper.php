<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($title ?? 'AuraPHP') ?></title>
    <?php ownstrap_css(); ?>
</head>
<body class="theme-light">

<?php template('navbar'); ?>
<script>
(function(){var t=localStorage.getItem("theme"),b=document.body;if(t&&t!=="theme-light"){b.classList.replace("theme-light","theme-dark");var i=document.getElementById("themeToggle");i&&(i.innerHTML="&#9788;")}})();function toggleTheme(){var e=document.body,i=document.getElementById("themeToggle");e.classList.toggle("theme-dark"),e.classList.toggle("theme-light");var n=e.classList.contains("theme-dark");localStorage.setItem("theme",n?"theme-dark":"theme-light"),i&&(i.innerHTML=n?"&#9788;":"&#9790;")}
</script>

<?= $viewContent ?? '' ?>

<?php ownstrap_js(); ?>
</body>
</html>
