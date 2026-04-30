<section class="page-section">
    <div class="page-header">
        <h1>Tasks</h1>
        <a href="/tasks/create" class="btn btn--primary">+ New Task</a>
    </div>

    <div class="filters">
        <button class="filter-btn active" data-status="">All</button>
        <button class="filter-btn" data-status="pending">Pending</button>
        <button class="filter-btn" data-status="running">Running</button>
        <button class="filter-btn" data-status="done">Done</button>
        <button class="filter-btn" data-status="error">Error</button>
    </div>

    <div id="tasks-container">
        <div class="loading">Loading tasks&hellip;</div>
    </div>

    <div id="pagination-container" class="pagination" hidden>
        <button class="btn btn--secondary" id="pagination-prev">&larr; Prev</button>
        <span class="pagination__info" id="pagination-info"></span>
        <button class="btn btn--secondary" id="pagination-next">Next &rarr;</button>
    </div>
</section>
