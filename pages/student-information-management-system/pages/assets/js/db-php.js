/**
 * Server-backed student store (PHP session + MySQL).
 * Expects window.__APP_BOOT__ from page_init (studentCount, students, activeStudentId, ...).
 */
(function () {
    let boot = window.__APP_BOOT__ || {
        studentCount: 0,
        students: [],
        activeStudentId: null,
        explicitLogout: false,
        registrar: false,
        gradeUnlock: false
    };

    function syncBoot(newBoot) {
        boot = Object.assign({}, boot, newBoot);
        window.__APP_BOOT__ = boot;
    }

    function api(path, options) {
        const url = path.startsWith('http') ? path : ((window.__API_BASE__ || '') + path);
        return fetch(url, Object.assign({
            credentials: 'same-origin',
            headers: { 'Content-Type': 'application/json' }
        }, options || {})).then(function (r) {
            return r.json().then(function (j) {
                if (!r.ok || j.ok === false) {
                    const err = new Error(j.error || ('HTTP ' + r.status));
                    err.payload = j;
                    throw err;
                }
                return j;
            });
        });
    }

    function findInCache(id) {
        const sid = String(id);
        return boot.students.find(function (s) { return String(s.id) === sid; }) || null;
    }

    function getById(id) {
        let s = findInCache(id);
        if (s) {
            return JSON.parse(JSON.stringify(s));
        }
        return null;
    }

    const DB = {
        init() { },

        isEmpty() {
            return (boot.studentCount || 0) === 0;
        },

        requiresRegistration() {
            return this.isEmpty();
        },

        getAll() {
            return JSON.parse(JSON.stringify(boot.students || []));
        },

        getById(id) {
            return getById(id);
        },

        save(student) {
            throw new Error('Use add() for new students');
        },

        update(id, updatedFields) {
            return api('api/students.php', {
                method: 'PATCH',
                body: JSON.stringify({ id: id, fields: updatedFields })
            }).then(function (res) {
                if (res.boot) {
                    syncBoot(res.boot);
                }
                return res.student ? JSON.parse(JSON.stringify(res.student)) : null;
            });
        },

        add(student) {
            return api('api/students.php', {
                method: 'POST',
                body: JSON.stringify(student)
            }).then(function (res) {
                if (res.boot) {
                    syncBoot(res.boot);
                }
                return res.student ? JSON.parse(JSON.stringify(res.student)) : null;
            });
        },

        setActive(id) {
            return api('api/active_student.php', {
                method: 'POST',
                body: JSON.stringify({ studentId: id })
            }).then(function (res) {
                if (res.boot) {
                    syncBoot(res.boot);
                }
            });
        },

        clearActive() {
            return api('api/active_student.php', { method: 'DELETE' }).then(function (res) {
                if (res.boot) {
                    syncBoot(res.boot);
                }
            });
        },

        getActive() {
            const id = boot.activeStudentId;
            if (!id) {
                return null;
            }
            const s = getById(id);
            return s;
        }
    };

    window.StudentDB = DB;
    window.__syncBoot = syncBoot;

    window.refreshAppBoot = function () {
        return api('api/bootstrap.php').then(function (res) {
            if (res.boot) {
                syncBoot(res.boot);
            }
            return boot;
        });
    };
})();