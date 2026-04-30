<section class="hero">
    <h1>Task Scheduler</h1>
    <p class="hero__sub">Schedule and monitor background tasks with ease.</p>
    <a class="btn btn--primary" href="/tasks/create">+ New Task</a>
</section>

<section class="stats">
    <h2>Quick Stats</h2>
    <div class="stats__grid">
        <div class="stat-card stat-card--pending">
            <span class="stat-card__value"><?= (int) ($stats['pending'] ?? 0) ?></span>
            <span class="stat-card__label">Pending</span>
        </div>
        <div class="stat-card stat-card--running">
            <span class="stat-card__value"><?= (int) ($stats['running'] ?? 0) ?></span>
            <span class="stat-card__label">Running</span>
        </div>
        <div class="stat-card stat-card--done">
            <span class="stat-card__value"><?= (int) ($stats['done'] ?? 0) ?></span>
            <span class="stat-card__label">Done</span>
        </div>
        <div class="stat-card stat-card--error">
            <span class="stat-card__value"><?= (int) ($stats['error'] ?? 0) ?></span>
            <span class="stat-card__label">Error</span>
        </div>
    </div>
    <p class="stats__link"><a href="/tasks">View all tasks &rarr;</a></p>
</section>
