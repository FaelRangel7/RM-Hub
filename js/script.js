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