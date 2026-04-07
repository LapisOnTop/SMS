/* ============================================================
   FILE: assets/js/api.js
   Centralized AJAX wrapper for all PHP API endpoints
   NO localStorage fallback — database-driven only
   ============================================================ */

const Api = {
    _base: 'api',

    async _req(file, method='GET', params={}, body=null) {
        try {
            const qs  = new URLSearchParams(params).toString();
            const url = `${Api._base}/${file}${qs?'?'+qs:''}`;
            const opt = {
                method,
                credentials: 'same-origin',
                headers: { 'Content-Type': 'application/json' }
            };
            if (body) opt.body = JSON.stringify(body);
            const res = await fetch(url, opt);
            const text = await res.text();
            let data;
            try {
                data = text ? JSON.parse(text) : null;
            } catch (e) {
                throw new Error(`Invalid JSON (HTTP ${res.status})`);
            }
            return data;
        } catch(e) {
            console.error(`[API] ${file}`, e.message);
            return { success:false, data:null, message: e.message };
        }
    },

    /* curriculum_db (READ-ONLY) */
    curriculum: {
        programs:  ()                     => Api._req('fetch_subjects.php','GET',{action:'programs'}),
        subjects:  (program,year,sem)     => Api._req('fetch_subjects.php','GET',{action:'subjects',program,year_level:year,semester:sem}),
    },

    /* sections */
    sections: {
        terms:          ()                      => Api._req('sections_api.php','GET',{action:'terms'}),
        list:           (p={})                  => Api._req('sections_api.php','GET',{action:'list',...p}),
        detail:         (id)                    => Api._req('sections_api.php','GET',{action:'detail',id}),
        students:       (section_id)            => Api._req('sections_api.php','GET',{action:'students',id:section_id}),
        searchStudents: (section_id, query)     => Api._req('sections_api.php','GET',{action:'search_students',section_id,query}),
        enroll:         (body)                  => Api._req('sections_api.php','POST',{action:'enroll'},body),
        create:         (body)                  => Api._req('create_section.php','POST',{},body),
        delete:         (id)                    => Api._req('sections_api.php','DELETE',{id}),
    },

    /* scheduling */
    schedule: {
        suggest: (section_id)             => Api._req('assign_schedule.php','GET',{section_id}),
        assign:  (body)                   => Api._req('assign_schedule.php','POST',{},body),
    },

    /* faculty */
    faculty: {
        list:           ()                => Api._req('faculty_load.php','GET',{action:'list_faculty'}),
        summary:        ()                => Api._req('faculty_load.php','GET',{action:'summary'}),
        sectionSubjects:(id)              => Api._req('faculty_load.php','GET',{action:'section_subjects',id}),
        assign:         (body)            => Api._req('faculty_load.php','POST',{},body),
        remove:         (load_id)         => Api._req('faculty_load.php','DELETE',{load_id}),
    },

    /* conflicts */
    conflicts: {
        list:    ()                       => Api._req('conflicts_api.php','GET',{action:'list'}),
        detect:  ()                       => Api._req('conflicts_api.php','GET',{action:'detect'}),
        resolve: (id)                     => Api._req('conflicts_api.php','PUT',{id}),
        delete:  (id)                     => Api._req('conflicts_api.php','DELETE',{id}),
    },

    /* terms */
    terms: {
        list:    ()                       => Api._req('terms_api.php','GET'),
        create:  (body)                   => Api._req('terms_api.php','POST',{},body),
        activate:(id)                     => Api._req('terms_api.php','PUT',{id}),
    },
};

window.Api = Api;
