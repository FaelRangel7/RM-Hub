<?php
date_default_timezone_set('America/Sao_Paulo');

$json_file = 'apps.json';

if (!file_exists($json_file) || !filesize($json_file)) {
    $default_apps = [
        ['title' => 'Apache Info',     'desc' => 'Servidor HTTP',                  'url' => 'http://192.168.100.240/apache.html'   ],
        ['title' => 'Cockpit',         'desc' => 'Painel de gerenciamento',        'url' => 'https://192.168.100.240:9090'         ],
        ['title' => 'DWService',       'desc' => 'Acesso remoto',                  'url' => 'https://access.dwservice.net/login.dw'],
        ['title' => 'GLPI',            'desc' => 'Gestão de ativos e chamados',    'url' => 'http://192.168.100.240:8080'          ],
        ['title' => 'PHP My Admin',    'desc' => 'Gerenciamento de banco de dados','url' => 'http://192.168.100.240/phpmyadmin'    ],
        ['title' => 'PHP Info',        'desc' => 'Informações sobre o PHP',        'url' => 'http://192.168.100.240/info.php'      ],
        ['title' => 'Zabbix',          'desc' => 'Monitoramento de infraestrutura','url' => 'http://192.168.100.240:8081'          ]
    ];
    file_put_contents($json_file, json_encode($default_apps, JSON_PRETTY_PRINT));
    chmod($json_file, 0664);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $content_type = $_SERVER['CONTENT_TYPE'] ?? '';

    if (str_contains($content_type, 'application/json')) {
        $input = json_decode(file_get_contents('php://input'), true);
        if (!empty($input['overwrite']) && isset($input['apps'])) {
            $success = file_put_contents($json_file, json_encode($input['apps'], JSON_PRETTY_PRINT));
            echo json_encode(['success' => $success !== false]);
            exit;
        }
    } elseif (isset($_POST['title'], $_POST['desc'], $_POST['url'])) {
        $apps = json_decode(file_get_contents($json_file), true);
        $apps[] = [
            'title' => $_POST['title'],
            'desc'  => $_POST['desc'],
            'url'   => $_POST['url']
        ];
        file_put_contents($json_file, json_encode($apps, JSON_PRETTY_PRINT));
        exit;
    }
}

$apps = json_decode(file_get_contents($json_file), true);
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>SRV-RR01</title>
    <link rel="icon" type="image/png" href="images/RMLogo.png">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { font-family: 'Segoe UI', Tahoma; background: #f8f9fa; color: #212529; }
        .dark-mode { background: #121212; color: #e0e0e0; }
        .custom-header { background: #1a1a1a; padding: 30px 0; color: white; }
        .logo-img { max-width: 225px; }
        .dark-mode .custom-header { background: #1f1f1f; }
        .dark-mode .card { background: #1e1e1e; color: #e0e0e0; border: 1px solid #333; }
        .mode-toggle, .delete-btn { position: absolute; }
        .mode-toggle { top: 20px; right: 20px; }
        .delete-btn { top: 5px; right: 5px; }
        .edit-btn { top: 5px; right: 48px; position: absolute; }
        .alert-box { position: fixed; top: 10px; right: 20px; z-index: 9999; min-width: 200px; }
    </style>
</head>
<body>
<header class="custom-header">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-md-8">
                <h1>SRV-RR01</h1>
                <h2 class="h5">Servidor de Aplicação</h2>
                <p>by Rangel</p>
            </div>
            <div class="col-md-4 text-md-end text-center">
                <img src="images/RMLogo.png" alt="Logo RM Technologies" class="img-fluid logo-img">
            </div>
        </div>
    </div>
</header>

<main class="container mt-5">
    <div class="row mb-4">
        <div class="col-md-4">
            <h4>Informações do Servidor</h4>
            <p>Data e Hora: <strong><?= date('d/m/Y H:i:s') ?></strong></p>
            <p>Nome do Servidor: <strong>SRV-RR01</strong></p>
            <p>Endereço IP: <strong>192.168.100.240</strong></p>
        </div>
        <div class="col-md-8">
            <h3>Bem-vindo ao SRV-RR01</h3>
            <p>Servidor de aplicação para ferramentas administrativas.</p>
        </div>
    </div>

    <h4 class="mb-3">Aplicações Disponíveis</h4>
    <div class="row row-cols-1 row-cols-md-3 g-4" id="appContainer">
        <?php foreach ($apps as $app): ?>
        <div class="col">
            <div class="card h-100 position-relative">
                <div class="card-body">
                    <h5 class="card-title"><?= htmlspecialchars($app['title']) ?></h5>
                    <p class="card-text"><?= htmlspecialchars($app['desc']) ?></p>
                    <a href="<?= htmlspecialchars($app['url']) ?>" target="_blank" class="btn btn-primary">Acessar</a>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</main>

<div class="modal fade" id="addServiceModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <form id="addServiceForm">
        <div class="modal-header">
          <h5 class="modal-title">Novo Serviço</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
            <input type="text" class="form-control mb-2" id="serviceTitle" placeholder="Título" required>
            <input type="text" class="form-control mb-2" id="serviceDesc" placeholder="Descrição" required>
            <input type="url" class="form-control" id="serviceURL" placeholder="URL" required>
        </div>
        <div class="modal-footer">
          <button type="submit" class="btn btn-success">Adicionar</button>
        </div>
      </form>
    </div>
  </div>
</div>

<div class="modal fade" id="editServiceModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Editar Serviço</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <input type="text" class="form-control mb-2" id="editTitle" placeholder="Título" required>
        <input type="text" class="form-control mb-2" id="editDesc" placeholder="Descrição" required>
        <input type="url" class="form-control" id="editURL" placeholder="URL" required>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-primary" id="saveEditBtn">Salvar Alterações</button>
      </div>
    </div>
  </div>
</div>

<footer class="text-center mt-5">
    <p>&copy; 2025 RM Technologies® - Todos os direitos reservados.</p>
    <a href="http://whatsapp.com">Whatsapp</a>
    <a href="http://instagram.com">Instagram</a>
    <a href="http://github.com">GitHub</a>
</footer>

<div id="alertBox" class="alert-box" style="display:none;"></div>
<button id="addServiceBtn" class="btn btn-success position-fixed d-none" style="bottom: 155px; right: 20px;">➕</button>
<button id="saveBtn" class="btn btn-primary position-fixed d-none" style="bottom: 110px; right: 20px;">💾</button>
<button id="editToggleBtn" class="btn btn-warning position-fixed" style="bottom: 65px; right: 20px;">✏️</button>
<button id="DarkModeBtn" class="btn btn-dark position-fixed" style="bottom: 20px; right: 20px;">💡</button>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/js/bootstrap.bundle.min.js"></script>
<script>
    const alertBox = document.getElementById('alertBox');
    const addBtn = document.getElementById('addServiceBtn');
    const saveBtn = document.getElementById('saveBtn');
    const editBtn = document.getElementById('editToggleBtn');
    const darkModeBtn = document.getElementById('DarkModeBtn');
    const editModal = new bootstrap.Modal(document.getElementById('editServiceModal'));
    const editTitle = document.getElementById('editTitle');
    const editDesc = document.getElementById('editDesc');
    const editURL = document.getElementById('editURL');
    let currentCard = null;
    let editMode = false;

    darkModeBtn.onclick = toggleDarkMode;

    function toggleDarkMode() {
        document.body.classList.toggle('dark-mode');
        localStorage.setItem('darkMode', document.body.classList.contains('dark-mode'));
    }

    window.onload = () => {
        if (localStorage.getItem('darkMode') === 'true') {
            document.body.classList.add('dark-mode');
        }
    }

    function showAlert(message, success = true) {
        alertBox.className = `alert alert-${success ? 'success' : 'danger'} alert-box`;
        alertBox.innerText = message;
        alertBox.style.display = 'block';
        setTimeout(() => alertBox.style.display = 'none', 3000);
    }

    editBtn.onclick = () => {
        editMode = !editMode;
        addBtn.classList.toggle('d-none', !editMode);
        saveBtn.classList.toggle('d-none', !editMode);

        document.querySelectorAll('.card').forEach(card => {
            card.style.position = 'relative';
            card.querySelector('.delete-btn')?.remove();
            card.querySelector('.edit-card-btn')?.remove();

            if (editMode) {
                const del = document.createElement('button');
                del.className = 'btn btn-sm btn-danger delete-btn';
                del.innerText = '🗑️';
                del.onclick = () => card.closest('.col').remove();
                card.appendChild(del);

                const edit = document.createElement('button');
                edit.className = 'btn btn-sm btn-warning edit-btn edit-card-btn';
                edit.innerText = '✏️';
                edit.onclick = () => {
                    currentCard = card;
                    editTitle.value = card.querySelector('.card-title').innerText;
                    editDesc.value = card.querySelector('.card-text').innerText;
                    editURL.value = card.querySelector('a').href;
                    editModal.show();
                };
                card.appendChild(edit);
            }
        });
    };

    document.getElementById('saveEditBtn').onclick = () => {
        if (currentCard) {
            currentCard.querySelector('.card-title').innerText = editTitle.value;
            currentCard.querySelector('.card-text').innerText = editDesc.value;
            currentCard.querySelector('a').href = editURL.value;
            editModal.hide();
        }
    };

    addBtn.onclick = () => new bootstrap.Modal('#addServiceModal').show();

    document.getElementById('addServiceForm').onsubmit = e => {
        e.preventDefault();
        const title = serviceTitle.value.trim();
        const desc  = serviceDesc.value.trim();
        const url   = serviceURL.value.trim();

        fetch('', {
            method: 'POST',
            body: new URLSearchParams({ title, desc, url })
        }).then(() => location.reload());
    }

    saveBtn.onclick = () => {
        const apps = [...document.querySelectorAll('#appContainer .card')].map(card => ({
            title: card.querySelector('.card-title').innerText.trim(),
            desc:  card.querySelector('.card-text').innerText.trim(),
            url:   card.querySelector('a').href.trim()
        }));

        fetch('', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ overwrite: true, apps })
        })
        .then(res => res.json())
        .then(r => showAlert(r.success ? 'Alterações salvas com sucesso!' : 'Erro ao salvar alterações!', r.success))
        .catch(() => showAlert('Erro de conexão ao salvar!', false));
    }
</script>
</body>
</html>
