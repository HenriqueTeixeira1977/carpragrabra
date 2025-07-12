<?php
session_start();
require_once 'config.php';

// Verificar autenticação
if (!isset($_SESSION['admin_id'])) {
    header('Location: /login/login.php');
    exit;
}

// Gerar token CSRF
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Filtros
$dataInicial = $_GET['data_inicial'] ?? '';
$dataFinal = $_GET['data_final'] ?? '';
$nome = $_GET['nome'] ?? '';
$origem = $_GET['origem'] ?? '';
$destino = $_GET['destino'] ?? '';

// Query para contagem total de leads
$sqlCount = "SELECT COUNT(*) as total FROM leads_carretos WHERE 1=1";
$paramsCount = [];

if (!empty($dataInicial)) {
    $sqlCount .= " AND data_envio >= ?";
    $paramsCount[] = $dataInicial;
}
if (!empty($dataFinal)) {
    $sqlCount .= " AND data_envio <= ?";
    $paramsCount[] = $dataFinal;
}
if (!empty($nome)) {
    $sqlCount .= " AND nome LIKE ?";
    $paramsCount[] = "%$nome%";
}
if (!empty($origem)) {
    $sqlCount .= " AND origem LIKE ?";
    $paramsCount[] = "%$origem%";
}
if (!empty($destino)) {
    $sqlCount .= " AND destino LIKE ?";
    $paramsCount[] = "%$destino%";
}

$stmtCount = $pdo->prepare($sqlCount);
$stmtCount->execute($paramsCount);
$totalLeads = $stmtCount->fetch()['total'];

// Paginação
$porPagina = 10;
$paginaAtual = isset($_GET['pagina']) && is_numeric($_GET['pagina']) && $_GET['pagina'] > 0 ? (int)$_GET['pagina'] : 1;
$offset = ($paginaAtual - 1) * $porPagina;
$totalPaginas = ceil($totalLeads / $porPagina);

// Query principal com paginação
$sql = "SELECT * FROM leads_carretos WHERE 1=1";
$params = [];

if (!empty($dataInicial)) {
    $sql .= " AND data_envio >= ?";
    $params[] = $dataInicial;
}
if (!empty($dataFinal)) {
    $sql .= " AND data_envio <= ?";
    $params[] = $dataFinal;
}
if (!empty($nome)) {
    $sql .= " AND nome LIKE ?";
    $params[] = "%$nome%";
}
if (!empty($origem)) {
    $sql .= " AND origem LIKE ?";
    $params[] = "%$origem%";
}
if (!empty($destino)) {
    $sql .= " AND destino LIKE ?";
    $params[] = "%$destino%";
}

$sql .= " ORDER BY data_envio DESC LIMIT ? OFFSET ?";
$params[] = $porPagina;
$params[] = $offset;

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$leadsPaginados = $stmt->fetchAll();

// Leads no último mês
$sqlLeadsMes = "SELECT COUNT(*) as total FROM leads_carretos WHERE data_envio >= DATE_SUB(CURDATE(), INTERVAL 1 MONTH)";
$stmtLeadsMes = $pdo->query($sqlLeadsMes);
$leadsMes = $stmtLeadsMes->fetch()['total'];

// Contar por cidade de origem e destino com cache
$cacheKeyOrigem = md5("origem_" . $dataInicial . $dataFinal);
$cacheKeyDestino = md5("destino_" . $dataInicial . $dataFinal);

if (!isset($_SESSION[$cacheKeyOrigem])) {
    $sqlOrigem = "SELECT origem, COUNT(*) as total FROM leads_carretos WHERE 1=1";
    $paramsOrigem = [];
    if (!empty($dataInicial)) {
        $sqlOrigem .= " AND data_envio >= ?";
        $paramsOrigem[] = $dataInicial;
    }
    if (!empty($dataFinal)) {
        $sqlOrigem .= " AND data_envio <= ?";
        $paramsOrigem[] = $dataFinal;
    }
    $sqlOrigem .= " GROUP BY origem ORDER BY total DESC LIMIT 5";
    $stmtOrigem = $pdo->prepare($sqlOrigem);
    $stmtOrigem->execute($paramsOrigem);
    $_SESSION[$cacheKeyOrigem] = $stmtOrigem->fetchAll();
}
$origens = $_SESSION[$cacheKeyOrigem];

if (!isset($_SESSION[$cacheKeyDestino])) {
    $sqlDestino = "SELECT destino, COUNT(*) as total FROM leads_carretos WHERE 1=1";
    $paramsDestino = [];
    if (!empty($dataInicial)) {
        $sqlDestino .= " AND data_envio >= ?";
        $paramsDestino[] = $dataInicial;
    }
    if (!empty($dataFinal)) {
        $sqlDestino .= " AND data_envio <= ?";
        $paramsDestino[] = $dataFinal;
    }
    $sqlDestino .= " GROUP BY destino ORDER BY total DESC LIMIT 5";
    $stmtDestino = $pdo->prepare($sqlDestino);
    $stmtDestino->execute($paramsDestino);
    $_SESSION[$cacheKeyDestino] = $stmtDestino->fetchAll();
}
$destinos = $_SESSION[$cacheKeyDestino];

// Leads por dia
$sqlLeadsPorDia = "SELECT DATE(data_envio) as data, COUNT(*) as total FROM leads_carretos WHERE 1=1";
$paramsLeadsPorDia = [];
if (!empty($dataInicial)) {
    $sqlLeadsPorDia .= " AND data_envio >= ?";
    $paramsLeadsPorDia[] = $dataInicial;
}
if (!empty($dataFinal)) {
    $sqlLeadsPorDia .= " AND data_envio <= ?";
    $paramsLeadsPorDia[] = $dataFinal;
}
$sqlLeadsPorDia .= " GROUP BY DATE(data_envio) ORDER BY data_envio";
$stmtLeadsPorDia = $pdo->prepare($sqlLeadsPorDia);
$stmtLeadsPorDia->execute($paramsLeadsPorDia);
$leadsPorDia = $stmtLeadsPorDia->fetchAll();
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Painel Administrativo</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/toastify-js/src/toastify.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/toastify-js"></script>
    <style>
        body { background-color: #f8f9fa; }
        .card { border-radius: 12px; box-shadow: 0 0 15px rgba(0,0,0,0.1); }
        .chart-container { height: 300px; }
        .sidebar { 
            height: 100vh; 
            background-color: #343a40; 
            color: white; 
            padding: 20px; 
            width: 250px; 
            position: fixed;
            transition: transform 0.3s ease;
        }
        .sidebar a { 
            color: white; 
            text-decoration: none; 
            display: block; 
            margin: 10px 0; 
        }
        .sidebar a:hover { text-decoration: underline; }
        .content { margin-left: 270px; padding: 20px; }
        @media (max-width: 768px) {
            .sidebar { 
                transform: translateX(-100%); 
                position: fixed; 
                width: 250px; 
                z-index: 1000; 
            }
            .sidebar.show { transform: translateX(0); }
            .content { margin-left: 0; }
        }
    </style>
</head>
<body>

<!-- Botão para abrir sidebar em telas menores -->
<button class="btn btn-primary d-md-none mb-3" type="button" onclick="document.querySelector('.sidebar').classList.toggle('show')">
    <i class="fas fa-bars"></i> Menu
</button>

<div class="d-flex">
    <div class="sidebar">
        <h4>Painel</h4>
        <a href="#"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
        <a href="adicionar_lead.php"><i class="fas fa-plus"></i> Adicionar Lead</a>
        <a href="../login/logout.php"><i class="fas fa-sign-out-alt"></i> Sair</a>
    </div>

    <div class="content">
        <h2>Bem-vindo, <?= htmlspecialchars($_SESSION['admin_nome']) ?></h2>

        <form method="GET" class="row g-2 mb-4">
            <div class="col-md-2">
                <input type="date" name="data_inicial" class="form-control" value="<?= htmlspecialchars($dataInicial) ?>">
            </div>
            <div class="col-md-2">
                <input type="date" name="data_final" class="form-control" value="<?= htmlspecialchars($dataFinal) ?>">
            </div>
            <div class="col-md-2">
                <input type="text" name="nome" class="form-control" placeholder="Nome" value="<?= htmlspecialchars($nome) ?>">
            </div>
            <div class="col-md-2">
                <input type="text" name="origem" class="form-control" placeholder="Origem" value="<?= htmlspecialchars($origem) ?>">
            </div>
            <div class="col-md-2">
                <input type="text" name="destino" class="form-control" placeholder="Destino" value="<?= htmlspecialchars($destino) ?>">
            </div>
            <div class="col-md-1">
                <button class="btn btn-primary w-100" type="submit"><i class="fas fa-filter"></i> Filtrar</button>
            </div>
            <div class="col-md-1">
                <a href="?" class="btn btn-secondary w-100"><i class="fas fa-eraser"></i> Limpar</a>
            </div>
            <div class="col-md-2">
                <a href="exportar_csv.php?data_inicial=<?= urlencode($dataInicial) ?>&data_final=<?= urlencode($dataFinal) ?>&nome=<?= urlencode($nome) ?>&origem=<?= urlencode($origem) ?>&destino=<?= urlencode($destino) ?>" class="btn btn-success w-100"><i class="fas fa-file-csv"></i> Exportar CSV</a>
            </div>
        </form>

        <div class="row">
            <div class="col-md-4">
                <div class="card text-white bg-success mb-3">
                    <div class="card-body">
                        <h5 class="card-title">Total de Leads</h5>
                        <p class="card-text display-4"><?= $totalLeads ?></p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card text-white bg-info mb-3">
                    <div class="card-body">
                        <h5 class="card-title">Leads Último Mês</h5>
                        <p class="card-text display-4"><?= $leadsMes ?></p>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mb-5">
            <div class="col-md-6 chart-container">
                <canvas id="origensChart"></canvas>
            </div>
            <div class="col-md-6 chart-container">
                <canvas id="destinosChart"></canvas>
            </div>
        </div>

        <div class="row mb-5">
            <div class="col-md-12 chart-container">
                <canvas id="leadsPorDiaChart"></canvas>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-bordered table-striped">
                <thead class="table-dark">
                    <tr>
                        <th>Data</th>
                        <th>Nome</th>
                        <th>WhatsApp</th>
                        <th>Email</th>
                        <th>Origem</th>
                        <th>Destino</th>
                        <th>Mensagem</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($leadsPaginados as $lead): ?>
                    <tr>
                        <td><?= date('d/m/Y H:i', strtotime($lead['data_envio'])) ?></td>
                        <td><?= htmlspecialchars($lead['nome']) ?></td>
                        <td><?= htmlspecialchars($lead['whatsapp']) ?></td>
                        <td><?= htmlspecialchars($lead['email']) ?></td>
                        <td><?= htmlspecialchars($lead['origem']) ?></td>
                        <td><?= htmlspecialchars($lead['destino']) ?></td>
                        <td><?= htmlspecialchars($lead['mensagem']) ?></td>
                        <td>
                            <a href="editar_lead.php?id=<?= $lead['id'] ?>" class="btn btn-sm btn-warning"><i class="fas fa-edit"></i> Editar</a>
                            <a href="excluir_lead.php?id=<?= $lead['id'] ?>&csrf_token=<?= $_SESSION['csrf_token'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Tem certeza que deseja excluir este lead?')"><i class="fas fa-trash"></i> Excluir</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <nav>
                <ul class="pagination">
                    <?php for ($i = 1; $i <= $totalPaginas; $i++): ?>
                        <li class="page-item <?= $i == $paginaAtual ? 'active' : '' ?>">
                            <a class="page-link" href="?pagina=<?= $i ?>&data_inicial=<?= urlencode($dataInicial) ?>&data_final=<?= urlencode($dataFinal) ?>&nome=<?= urlencode($nome) ?>&origem=<?= urlencode($origem) ?>&destino=<?= urlencode($destino) ?>"><?= $i ?></a>
                        </li>
                    <?php endfor; ?>
                </ul>
            </nav>
        </div>
    </div>
</div>

<script>
const ctx1 = document.getElementById('origensChart').getContext('2d');
const ctx2 = document.getElementById('destinosChart').getContext('2d');
const ctx3 = document.getElementById('leadsPorDiaChart').getContext('2d');

const origemData = {
    labels: <?= json_encode(array_column($origens, 'origem')) ?>,
    datasets: [{
        label: 'Top 5 Cidades de Origem',
        data: <?= json_encode(array_column($origens, 'total')) ?>,
        backgroundColor: [
            'rgba(54, 162, 235, 0.5)',
            'rgba(255, 99, 132, 0.5)',
            'rgba(75, 192, 192, 0.5)',
            'rgba(255, 206, 86, 0.5)',
            'rgba(153, 102, 255, 0.5)'
        ],
        borderColor: [
            'rgba(54, 162, 235, 1)',
            'rgba(255, 99, 132, 1)',
            'rgba(75, 192, 192, 1)',
            'rgba(255, 206, 86, 1)',
            'rgba(153, 102, 255, 1)'
        ],
        borderWidth: 1
    }]
};

const destinoData = {
    labels: <?= json_encode(array_column($destinos, 'destino')) ?>,
    datasets: [{
        label: 'Top 5 Cidades de Destino',
        data: <?= json_encode(array_column($destinos, 'total')) ?>,
        backgroundColor: [
            'rgba(54, 162, 235, 0.5)',
            'rgba(255, 99, 132, 0.5)',
            'rgba(75, 192, 192, 0.5)',
            'rgba(255, 206, 86, 0.5)',
            'rgba(153, 102, 255, 0.5)'
        ],
        borderColor: [
            'rgba(54, 162, 235, 1)',
            'rgba(255, 99, 132, 1)',
            'rgba(75, 192, 192, 1)',
            'rgba(255, 206, 86, 1)',
            'rgba(153, 102, 255, 1)'
        ],
        borderWidth: 1
    }]
};

const leadsPorDiaData = {
    labels: <?= json_encode(array_column($leadsPorDia, 'data')) ?>,
    datasets: [{
        label: 'Leads por Dia',
        data: <?= json_encode(array_column($leadsPorDia, 'total')) ?>,
        borderColor: 'rgba(75, 192, 192, 1)',
        backgroundColor: 'rgba(75, 192, 192, 0.2)',
        fill: true,
        tension: 0.4
    }]
};

new Chart(ctx1, {
    type: 'bar',
    data: origemData,
    options: {
        plugins: { tooltip: { enabled: true } },
        scales: {
            y: { beginAtZero: true, title: { display: true, text: 'Total' } }
        }
    }
});

new Chart(ctx2, {
    type: 'bar',
    data: destinoData,
    options: {
        plugins: { tooltip: { enabled: true } },
        scales: {
            y: { beginAtZero: true, title: { display: true, text: 'Total' } }
        }
    }
});

new Chart(ctx3, {
    type: 'line',
    data: leadsPorDiaData,
    options: {
        plugins: {
            tooltip: { enabled: true },
            legend: { display: true }
        },
        scales: {
            x: { title: { display: true, text: 'Data' } },
            y: { title: { display: true, text: 'Número de Leads' } }
        }
    }
});

// Notificações em tempo real
setInterval(() => {
    fetch('get_novos_leads.php')
        .then(response => response.json())
        .then(data => {
            if (data.novos_leads > 0) {
                Toastify({
                    text: `${data.novos_leads} novos leads!`,
                    duration: 3000,
                    gravity: "top",
                    position: "right",
                    backgroundColor: "#28a745"
                }).showToast();
            }
        });
}, 60000);

// Exibir mensagens de feedback
<?php if (isset($_SESSION['mensagem'])): ?>
    Toastify({
        text: "<?= htmlspecialchars($_SESSION['mensagem']) ?>",
        duration: 3000,
        gravity: "top",
        position: "right",
        backgroundColor: "<?= $_SESSION['mensagem_erro'] ? '#dc3545' : '#28a745' ?>"
    }).showToast();
    <?php unset($_SESSION['mensagem'], $_SESSION['mensagem_erro']); ?>
<?php endif; ?>
</script>

</body>
</html>