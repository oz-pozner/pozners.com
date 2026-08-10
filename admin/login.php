<?php
require_once __DIR__ . '/../includes/auth.php';

admin_session_start();
if (admin_is_logged_in()) {
    header('Location: index.php');
    exit;
}

$error = '';
if (!admin_credentials_configured()) {
    $error = 'Admin login is not configured. Set ADMIN_USERNAME and ADMIN_PASSWORD_HASH in .env (see .env.example).';
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!admin_verify_csrf($_POST['csrf_token'] ?? null)) {
        $error = 'Session expired, please try again. | הפעולה פגה, נסו שוב.';
    } elseif (admin_attempt_login($_POST['username'] ?? '', $_POST['password'] ?? '')) {
        header('Location: index.php');
        exit;
    } else {
        $error = 'Invalid username or password. | שם משתמש או סיסמה שגויים.';
    }
}

$csrfToken = admin_csrf_token();
?>
<!DOCTYPE html>
<html lang="en" dir="ltr">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Admin Login | Pozner Family</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="flex min-h-screen items-center justify-center bg-slate-950 text-slate-100">
  <main class="w-full max-w-sm rounded-3xl border border-white/10 bg-slate-900/80 p-8 shadow-2xl shadow-black/20">
    <p class="text-sm font-semibold uppercase tracking-[0.3em] text-teal-300">Pozner Family</p>
    <h1 class="mt-2 text-2xl font-bold text-white">Admin Login</h1>

    <?php if ($error !== ''): ?>
    <p class="mt-4 rounded-xl border border-red-400/30 bg-red-400/10 px-4 py-3 text-sm text-red-300"><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>

    <form method="post" class="mt-6 space-y-4">
      <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>" />
      <div>
        <label for="username" class="block text-sm text-slate-400">Username</label>
        <input type="text" id="username" name="username" required autofocus
          class="mt-1 w-full rounded-xl border border-white/10 bg-white/5 px-4 py-2 text-white focus:border-teal-400 focus:outline-none" />
      </div>
      <div>
        <label for="password" class="block text-sm text-slate-400">Password</label>
        <input type="password" id="password" name="password" required
          class="mt-1 w-full rounded-xl border border-white/10 bg-white/5 px-4 py-2 text-white focus:border-teal-400 focus:outline-none" />
      </div>
      <button type="submit"
        class="w-full rounded-full bg-teal-400 px-5 py-3 font-medium text-slate-950 transition hover:bg-teal-300">Log
        in</button>
    </form>

    <a href="../index.php" class="mt-6 inline-block text-sm text-slate-500 transition hover:text-teal-300">← Back to
      site</a>
  </main>
</body>

</html>
