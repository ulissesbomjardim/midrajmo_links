(function () {
    var apiBase = 'api.php';
    var state = {
        categories: [],
        tags: [],
        links: []
    };

    var categoryForm = document.getElementById('categoryForm');
    var tagForm = document.getElementById('tagForm');
    var linkForm = document.getElementById('linkForm');

    var categoryList = document.getElementById('categoryList');
    var tagList = document.getElementById('tagList');
    var categorySelect = document.getElementById('categorySelect');
    var tagSelect = document.getElementById('tagSelect');
    var linksGrid = document.getElementById('linksGrid');

    var sidebar = document.getElementById('sidebar');
    var toggleSidebar = document.getElementById('toggleSidebar');
    var showSidebar = document.getElementById('showSidebar');

    function fetchJson(url, options) {
        return fetch(url, options).then(function (response) {
            return response.json();
        });
    }

    function loadData() {
        return fetchJson(apiBase + '?action=dashboard_data')
            .then(function (result) {
                if (!result.success) {
                    throw new Error(result.message || 'Erro ao carregar dados');
                }

                state.categories = result.data.categories || [];
                state.tags = result.data.tags || [];
                state.links = result.data.links || [];
                renderAll();
            })
            .catch(function (error) {
                showToast(error.message, false);
            });
    }

    function renderAll() {
        renderCategories();
        renderTags();
        renderSelects();
        renderLinks();
    }

    function renderCategories() {
        categoryList.innerHTML = state.categories.map(function (item) {
            return '<li>' + escapeHtml(item.name) + '</li>';
        }).join('');
    }

    function renderTags() {
        tagList.innerHTML = state.tags.map(function (item) {
            return '<li>' + escapeHtml(item.name) + '</li>';
        }).join('');
    }

    function renderSelects() {
        categorySelect.innerHTML = '<option value="">Sem categoria</option>' + state.categories.map(function (item) {
            return '<option value="' + item.id + '">' + escapeHtml(item.name) + '</option>';
        }).join('');

        tagSelect.innerHTML = state.tags.map(function (item) {
            return '<option value="' + item.id + '">' + escapeHtml(item.name) + '</option>';
        }).join('');
    }

    function renderLinks() {
        if (!state.links.length) {
            linksGrid.innerHTML = '<div class="panel">Nenhum link salvo ainda.</div>';
            return;
        }

        linksGrid.innerHTML = state.links.map(function (link) {
            var tags = (link.tags || []).map(function (tag) {
                return '<span>#' + escapeHtml(tag.name) + '</span>';
            }).join('');

            return [
                '<article class="card">',
                '<h4>' + escapeHtml(link.title) + '</h4>',
                '<a href="' + escapeAttribute(link.url) + '" target="_blank" rel="noopener noreferrer">' + escapeHtml(link.url) + '</a>',
                link.description ? '<p>' + escapeHtml(link.description) + '</p>' : '',
                '<div class="meta">Categoria: ' + escapeHtml(link.category_name || 'Sem categoria') + '</div>',
                '<div class="tags">' + tags + '</div>',
                '</article>'
            ].join('');
        }).join('');
    }

    function serializeForm(form) {
        var data = {};
        var formData = new FormData(form);

        formData.forEach(function (value, key) {
            if (key === 'tag_ids[]') {
                if (!data.tag_ids) {
                    data.tag_ids = [];
                }
                data.tag_ids.push(value);
                return;
            }

            data[key] = value;
        });

        return data;
    }

    function postAction(action, payload) {
        return fetchJson(apiBase + '?action=' + encodeURIComponent(action), {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload)
        });
    }

    categoryForm.addEventListener('submit', function (event) {
        event.preventDefault();
        var payload = serializeForm(categoryForm);

        postAction('create_category', payload).then(function (result) {
            if (!result.success) {
                throw new Error(result.message || 'Erro ao criar categoria');
            }

            categoryForm.reset();
            showToast('Categoria criada com sucesso.', true);
            return loadData();
        }).catch(function (error) {
            showToast(error.message, false);
        });
    });

    tagForm.addEventListener('submit', function (event) {
        event.preventDefault();
        var payload = serializeForm(tagForm);

        postAction('create_tag', payload).then(function (result) {
            if (!result.success) {
                throw new Error(result.message || 'Erro ao criar tag');
            }

            tagForm.reset();
            showToast('Tag criada com sucesso.', true);
            return loadData();
        }).catch(function (error) {
            showToast(error.message, false);
        });
    });

    linkForm.addEventListener('submit', function (event) {
        event.preventDefault();
        var payload = serializeForm(linkForm);

        postAction('create_link', payload).then(function (result) {
            if (!result.success) {
                throw new Error(result.message || 'Erro ao salvar link');
            }

            linkForm.reset();
            showToast('Link salvo com sucesso.', true);
            return loadData();
        }).catch(function (error) {
            showToast(error.message, false);
        });
    });

    toggleSidebar.addEventListener('click', function () {
        if (window.innerWidth <= 960) {
            sidebar.classList.remove('open');
            return;
        }

        sidebar.classList.toggle('hidden');
    });

    showSidebar.addEventListener('click', function () {
        sidebar.classList.toggle('open');
    });

    function showToast(message, success) {
        var toast = document.createElement('div');
        toast.className = 'toast' + (success ? ' success' : '');
        toast.textContent = message;
        document.body.appendChild(toast);

        setTimeout(function () {
            toast.remove();
        }, 2200);
    }

    function escapeHtml(value) {
        return String(value || '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#39;');
    }

    function escapeAttribute(value) {
        return escapeHtml(value).replace(/`/g, '&#96;');
    }

    loadData();
})();
