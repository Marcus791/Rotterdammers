<?php
$user = current_user();
?>
<!doctype html>
<html lang="nl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title><?= e(isset($title) ? "$title | Kookboek" : 'Kookboek') ?></title>
    <link rel="stylesheet" href="style.css">
</head>

<body>
    <header class="site-header">
        <div class="wrap nav">
            <a class="logo" href="index.php"><span class="logo-box"></span>KOOKBOEK</a>
            <nav>
                <a href="index.php">Home</a>
                <a href="recepten.php">Recepten</a>
                <a href="themas.php">Thema's</a>
                <a href="weekmenu.php">Weekmenu</a>
                <a href="bestellen.php">Bestellen</a>
                <?php if ($user): ?>
                    <?php if (is_admin()): ?>
                        <a class="admin-nav" href="admin.php">Admin</a>
                    <?php endif ?>
                    <a class="nav-logout" href="logout.php">Uitloggen</a>
                <?php else: ?>
                    <a href="login.php">Inloggen</a>
                <?php endif ?>
            </nav>
        </div>
    </header>
