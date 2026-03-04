<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gerenciador de Favoritos</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="app" id="app">
        <aside class="sidebar" id="sidebar">
            <div class="sidebar-header">
                <h1>Favoritos</h1>
                <button class="ghost-btn" id="toggleSidebar" aria-label="Ocultar barra lateral">☰</button>
            </div>

            <div class="sidebar-section">
                <h2>Categorias</h2>
                <form id="categoryForm" class="stack">
                    <input type="text" name="name" placeholder="Nova categoria" required>
                    <button type="submit">Adicionar</button>
                </form>
                <ul id="categoryList" class="pill-list"></ul>
            </div>

            <div class="sidebar-section">
                <h2>Tags</h2>
                <form id="tagForm" class="stack">
                    <input type="text" name="name" placeholder="Nova tag" required>
                    <button type="submit">Adicionar</button>
                </form>
                <ul id="tagList" class="pill-list"></ul>
            </div>
        </aside>

        <main class="content">
            <header class="content-header">
                <button class="ghost-btn show-sidebar" id="showSidebar">☰</button>
                <h2>Meus Links</h2>
            </header>

            <section class="panel">
                <h3>Novo Link</h3>
                <form id="linkForm" class="grid-form">
                    <input type="text" name="title" placeholder="Título" required>
                    <input type="url" name="url" placeholder="https://exemplo.com" required>
                    <textarea name="description" rows="3" placeholder="Descrição opcional"></textarea>
                    <select name="category_id" id="categorySelect">
                        <option value="">Sem categoria</option>
                    </select>
                    <label for="tagSelect">Tags</label>
                    <select name="tag_ids[]" id="tagSelect" multiple></select>
                    <button type="submit" class="full">Salvar link</button>
                </form>
            </section>

            <section>
                <div id="linksGrid" class="cards"></div>
            </section>
        </main>
    </div>

    <script src="assets/js/app.js"></script>
</body>
</html>
