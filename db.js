// Front-end database helper for new PHP/MySQL API
// included after auth-guard.js in every page

const DB = {
    _map: {
        'epm_records_v1': 'api/maintenance.php',
        'epm_load_balancing_v1': 'api/loadbalancing.php',
        'megger_test_files_v1': 'api/megger.php',
        'epm_pm_checklist_v1': 'api/pmchecklist.php',
        'epm_store_list_v1': 'api/storelist.php'
    },

    async _fetch(url, opts={}) {
        const res = await fetch(url, opts);
        if (!res.ok) {
            // Try to extract a JSON error message from the body, fallback to HTTP status
            let msg = `HTTP ${res.status}`;
            try {
                const body = await res.json();
                if (body && body.error) msg = body.error;
            } catch (_) {}
            throw new Error(msg);
        }
        return res.json();
    },

    list(col) {
        const ep = this._map[col];
        if (!ep) throw new Error('Unknown collection: ' + col);
        return this._fetch(ep);
    },

    get(col, id) {
        const ep = this._map[col];
        return this._fetch(`${ep}?id=${encodeURIComponent(id)}`);
    },

    create(col, obj) {
        const ep = this._map[col];
        return this._fetch(ep, {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify(obj)
        });
    },

    update(col, id, obj) {
        const ep = this._map[col];
        return this._fetch(`${ep}?id=${encodeURIComponent(id)}`, {
            method: 'PUT',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify(obj)
        });
    },

    delete(col, id) {
        const ep = this._map[col];
        return this._fetch(`${ep}?id=${encodeURIComponent(id)}`, {
            method: 'DELETE'
        });
    }
};
