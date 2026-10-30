<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <?php
        $rawTitle = $page['props']['title'] ?? $component ?? 'NovaFlow';
        $pageTitle = is_array($rawTitle) ? implode(' · ', $rawTitle) : (string) $rawTitle;
    ?>
    <title><?= htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8') ?></title>
    <!-- Vite / Inertia Assets (Dev HMR or Production Manifest) -->
    <?= vite('resources/js/app.jsx') ?>
</head>
<body class="bg-slate-950 text-slate-100 min-h-screen antialiased selection:bg-indigo-500 selection:text-white">
    <div id="app" data-page='<?= htmlspecialchars(json_encode($page, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE), ENT_QUOTES, 'UTF-8') ?>'></div>
    <noscript>
        <div style="padding: 2rem; text-align: center; color: #f8fafc; background: #0f172a; font-family: sans-serif;">
            <h2>JavaScript Required</h2>
            <p>Please enable JavaScript in your browser to experience this application.</p>
        </div>
    </noscript>
</body>
</html>
