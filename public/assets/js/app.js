/* Task Scheduler — frontend */
(function () {
    'use strict';

    // ── Task list page ────────────────────────────────────────
    const tasksContainer = document.getElementById('tasks-container');
    const filterBtns = document.querySelectorAll('.filter-btn');
    const paginationEl = document.getElementById('pagination-container');
    const paginationPrevBtn = document.getElementById('pagination-prev');
    const paginationNextBtn = document.getElementById('pagination-next');
    const paginationInfo = document.getElementById('pagination-info');

    let activeStatus = '';
    let currentPage = 1;
    const perPage = 10;

    if (paginationPrevBtn) {
        paginationPrevBtn.addEventListener('click', () => {
            currentPage--;
            loadTasks();
        });
    }
    if (paginationNextBtn) {
        paginationNextBtn.addEventListener('click', () => {
            currentPage++;
            loadTasks();
        });
    }

    if (tasksContainer) {
        filterBtns.forEach(btn => {
            btn.addEventListener('click', () => {
                filterBtns.forEach(b => b.classList.remove('active'));
                btn.classList.add('active');
                activeStatus = btn.dataset.status || '';
                currentPage = 1;
                loadTasks(activeStatus);
            });
        });

        loadTasks('');
    }

    function loadTasks(status) {
        if (status !== undefined) activeStatus = status;
        tasksContainer.innerHTML = '<div class="loading">Loading tasks&hellip;</div>';

        const params = new URLSearchParams({page: currentPage, per_page: perPage});
        if (activeStatus) params.set('status', activeStatus);

        fetch('/api/tasks?' + params.toString())
            .then(r => r.json())
            .then(json => renderTasks(json.data || [], json.pagination || null))
            .catch(() => {
                tasksContainer.innerHTML = '<div class="alert alert--error">Failed to load tasks.</div>';
                updatePagination(null);
            });
    }

    function renderTasks(tasks, pagination) {
        updatePagination(pagination);

        if (tasks.length === 0) {
            tasksContainer.innerHTML = '<div class="empty-state">No tasks found. <a href="/tasks/create">Create one?</a></div>';
            return;
        }

        const rows = tasks.map(t => {
            const scheduled = formatDate(t.scheduled_at);
            const error = t.error_message
                ? `<div class="col-error">${esc(t.error_message)}</div>`
                : '';
            return `
                <tr>
                    <td class="col-id" title="${esc(t.id)}">${esc(t.id.slice(0, 8))}&hellip;</td>
                    <td class="col-payload">${esc(t.payload)}${error}</td>
                    <td><span class="badge badge--${esc(t.status)}">${esc(t.status)}</span></td>
                    <td>${esc(scheduled)}</td>
                </tr>`;
        }).join('');

        tasksContainer.innerHTML = `
            <div class="task-table-wrap">
                <table class="task-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Payload</th>
                            <th>Status</th>
                            <th>Scheduled At</th>
                        </tr>
                    </thead>
                    <tbody>${rows}</tbody>
                </table>
            </div>`;
    }

    function updatePagination(pagination) {
        if (!paginationEl) return;

        const visible = pagination && pagination.total_pages > 1;
        paginationEl.hidden = !visible;

        if (!visible) {
            paginationPrevBtn.disabled = true;
            paginationNextBtn.disabled = true;
            return;
        }

        paginationInfo.textContent = `Page ${pagination.page} of ${pagination.total_pages}`;
        paginationPrevBtn.disabled = pagination.page <= 1;
        paginationNextBtn.disabled = pagination.page >= pagination.total_pages;
    }

    // ── Create task form ──────────────────────────────────────
    const taskForm = document.getElementById('task-form');
    const formAlert = document.getElementById('form-alert');
    const submitBtn = document.getElementById('submit-btn');

    if (taskForm) {
        taskForm.addEventListener('submit', e => {
            e.preventDefault();
            hideAlert();

            const offset = taskForm.querySelector('#offset').value.trim();
            const payload = taskForm.querySelector('#payload').value.trim();

            if (!offset || !payload) {
                showAlert('Both fields are required.', 'error');
                return;
            }

            submitBtn.disabled = true;
            submitBtn.textContent = 'Scheduling…';

            fetch('/api/tasks', {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({offset, payload}),
            })
                .then(r => r.json().then(json => ({ok: r.ok, json})))
                .then(({ok, json}) => {
                    if (ok) {
                        showAlert('Task scheduled successfully! ID: ' + json.data.id, 'success');
                        taskForm.reset();
                    } else {
                        showAlert(json.error || 'Unknown error.', 'error');
                    }
                })
                .catch(() => showAlert('Network error. Please try again.', 'error'))
                .finally(() => {
                    submitBtn.disabled = false;
                    submitBtn.textContent = 'Schedule Task';
                });
        });
    }

    // ── Helpers ───────────────────────────────────────────────
    function showAlert(msg, type) {
        if (!formAlert) return;
        formAlert.className = 'alert alert--' + type;
        formAlert.textContent = msg;
        formAlert.style.display = 'block';
    }

    function hideAlert() {
        if (!formAlert) return;
        formAlert.style.display = 'none';
    }

    function esc(str) {
        return String(str)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;');
    }

    function formatDate(ts) {
        const d = new Date(ts * 1000);
        return d.toLocaleString();
    }
})();
