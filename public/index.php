<?php
declare(strict_types=1);

$pages = [
    'login'      => ['auth' => false, 'js' => 'auth'],
    'register'   => ['auth' => false, 'js' => 'auth'],
    'dashboard'  => ['auth' => true,  'js' => 'dashboard'],
    'portfolio'  => ['auth' => true,  'js' => 'portfolio'],
    'loans-new'  => ['auth' => true,  'js' => 'loans'],
    'feed'       => ['auth' => true,  'js' => 'feed'],
    'messages'   => ['auth' => true,  'js' => 'messages'],
    'chat'       => ['auth' => true,  'js' => 'chat'],
    'profile'    => ['auth' => true,  'js' => 'dashboard'],
];

$p = $_GET['p'] ?? 'dashboard';
if (!array_key_exists($p, $pages)) {
    $p = 'dashboard';
}

$page     = $pages[$p];
$isAuth   = $page['auth'];
$loggedIn = isset($_COOKIE['calcifer_auth']);

if ($isAuth && !$loggedIn) {
    header('Location: /?p=login');
    exit;
}
if (!$isAuth && $loggedIn) {
    header('Location: /?p=dashboard');
    exit;
}

require 'components/head.php';

if ($isAuth) {
    require 'components/nav.php';
    echo '<div id="main">';
    require 'components/topbar.php';
    echo '<div id="page" class="page-enter">';
    require "pages/{$p}.php";
    echo '</div></div>';
} else {
    require "pages/{$p}.php";
}
?>

    <script type="module" src="/js/app.js"></script>
    <script type="module" src="/js/pages/<?= htmlspecialchars($page['js']) ?>.js"></script>
    </body>
</html>