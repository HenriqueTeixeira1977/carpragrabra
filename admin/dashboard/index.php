<?php
session_start();
require_once '../config.php';

// Filtros por data
$dataInicial = $_GET['data_inicial'] ?? '';
$dataFinal   = $_GET['data_final'] ?? '';
$busca = $_GET['busca'] ?? '';

// Query base
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
if (!empty($busca)) {
    $sql .= " AND (nome LIKE ? OR whatsapp LIKE ? OR email LIKE ? OR origem LIKE ? OR destino LIKE ?)";
    for ($i = 0; $i < 5; $i++) {
        $params[] = "%$busca%";
    }
}

$sql .= " ORDER BY data_envio DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$leads = $stmt->fetchAll();

// Paginação
$totalLeads = count($leads);
$porPagina = 10;
$paginaAtual = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
$offset = ($paginaAtual - 1) * $porPagina;

$leadsPaginados = array_slice($leads, $offset, $porPagina);
$totalPaginas = ceil($totalLeads / $porPagina);

// Contar por cidade de origem e destino com filtro
$sqlOrigem = "SELECT origem, COUNT(*) as total FROM leads_carretos WHERE 1=1";
$sqlDestino = "SELECT destino, COUNT(*) as total FROM leads_carretos WHERE 1=1";
$paramsOrigem = [];
$paramsDestino = [];

if (!empty($dataInicial)) {
    $sqlOrigem .= " AND data_envio >= ?";
    $sqlDestino .= " AND data_envio >= ?";
    $paramsOrigem[] = $dataInicial;
    $paramsDestino[] = $dataInicial;
}
if (!empty($dataFinal)) {
    $sqlOrigem .= " AND data_envio <= ?";
    $sqlDestino .= " AND data_envio <= ?";
    $paramsOrigem[] = $dataFinal;
    $paramsDestino[] = $dataFinal;
}

$sqlOrigem .= " GROUP BY origem";
$sqlDestino .= " GROUP BY destino";

$stmtOrigem = $pdo->prepare($sqlOrigem);
$stmtOrigem->execute($paramsOrigem);
$origens = $stmtOrigem->fetchAll();

$stmtDestino = $pdo->prepare($sqlDestino);
$stmtDestino->execute($paramsDestino);
$destinos = $stmtDestino->fetchAll();
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Painel Administrativo</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        body { background-color: #f8f9fa; }
        .card { border-radius: 12px; box-shadow: 0 0 15px rgba(0,0,0,0.1); }
        .chart-container { height: 300px; }
        .sidebar {
            height: 100vh;
            background-color: #343a40;
            color: white;
            padding: 20px;
        }
        .sidebar a {
            color: white;
            text-decoration: none;
            display: block;
            margin: 10px 0;
        }
        .sidebar a:hover {
            text-decoration: underline;
        }
        .content {
            margin-left: 250px;
            padding: 20px;
        }
        @media (max-width: 768px) {
            .sidebar {
                position: fixed;
                width: 100%;
                height: auto;
                z-index: 1000;
            }
            .content {
                margin-left: 0;
                margin-top: 200px;
            }
        }
    </style>
</head>
<body>

<div class="d-flex">
    <div class="sidebar">
        <h4>Painel</h4>
        <a href="#">Dashboard</a>
        <a href="adicionar_lead.php">Adicionar Lead</a>
        <a href="../login/logout.php">Sair</a>
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
            <div class="col-md-4">
                <input type="text" name="busca" class="form-control" placeholder="Buscar por nome, cidade, etc." value="<?= htmlspecialchars($busca) ?>">
            </div>
            <div class="col-md-2">
                <button class="btn btn-primary w-100" type="submit">Filtrar</button>
            </div>
            <div class="col-md-2">
                <a href="exportar_excel.php?data_inicial=<?= urlencode($dataInicial) ?>&data_final=<?= urlencode($dataFinal) ?>&busca=<?= urlencode($busca) ?>" class="btn btn-success w-100">Exportar Excel</a>
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
        </div>

        <div class="row mb-5">
            <div class="col-md-6 chart-container">
                <canvas id="origensChart"></canvas>
            </div>
            <div class="col-md-6 chart-container">
                <canvas id="destinosChart"></canvas>
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
                            <a href="editar_lead.php?id=<?= $lead['id'] ?>" class="btn btn-sm btn-warning">Editar</a>
                            <a href="excluir_lead.php?id=<?= $lead['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Tem certeza que deseja excluir este lead?')">Excluir</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <!-- Paginação -->
            <nav>
                <ul class="pagination">
                    <?php for ($i = 1; $i <= $totalPaginas; $i++): ?>
                        <li class="page-item <?= $i == $paginaAtual ? 'active' : '' ?>">
                            <a class="page-link" href="?pagina=<?= $i ?>&data_inicial=<?= urlencode($dataInicial) ?>&data_final=<?= urlencode($dataFinal) ?>&busca=<?= urlencode($busca) ?>"><?= $i ?></a>
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

const origemData = {
    labels: <?= json_encode(array_column($origens, 'origem')) ?>,
    datasets: [{
        label: 'Cidades de Origem',
        data: <?= json_encode(array_column($origens, 'total')) ?>,
        backgroundColor: 'rgba(54, 162, 235, 0.5)',
        borderColor: 'rgba(54, 162, 235, 1)',
        borderWidth: 1
    }]
};

const destinoData = {
    labels: <?= json_encode(array_column($destinos, 'destino')) ?>,
    datasets: [{
        label: 'Cidades de Destino',
        data: <?= json_encode(array_column($destinos, 'total')) ?>,
        backgroundColor: 'rgba(255, 99, 132, 0.5)',
        borderColor: 'rgba(255, 99, 132, 1)',
        borderWidth: 1
    }]
};

new Chart(ctx1, { type: 'bar', data: origemData });
new Chart(ctx2, { type: 'bar', data: destinoData });
</script>

</body>
</html>
