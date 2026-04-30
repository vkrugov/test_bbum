<section class="page-section">
    <h1>Create Task</h1>

    <div id="form-alert" class="alert" style="display:none"></div>

    <form id="task-form" class="task-form" novalidate>
        <div class="form-group">
            <label for="offset">Schedule Offset</label>
            <input
                type="text"
                id="offset"
                name="offset"
                class="form-control"
                placeholder="+15m / +3h / +1d"
                required
            >
            <small class="form-hint">Format: +15m (minutes), +3h (hours), +1d (days)</small>
        </div>

        <div class="form-group">
            <label for="payload">Payload (command)</label>
            <textarea
                id="payload"
                name="payload"
                class="form-control"
                rows="4"
                placeholder="echo Hello World"
                required
            ></textarea>
        </div>

        <button type="submit" class="btn btn--primary" id="submit-btn">Schedule Task</button>
        <a href="/tasks" class="btn btn--secondary">View All Tasks</a>
    </form>
</section>
