<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Task Scheduler</title>
    <link rel="stylesheet" href="/assets/css/app.css">
</head>
<body>
    <header class="site-header">
        <nav class="nav">
            <a class="nav__brand" href="/">TaskScheduler</a>
            <ul class="nav__links">
                <li><a href="/">Home</a></li>
                <li><a href="/tasks">Tasks</a></li>
                <li><a href="/tasks/create">New Task</a></li>
            </ul>
        </nav>
    </header>

    <main class="main-content">
        <?= $content ?? '' ?>
    </main>

    <footer class="site-footer">
        <p>Task Scheduler &mdash; 2026</p>
    </footer>

    <script src="/assets/js/app.js"></script>
</body>
</html>
