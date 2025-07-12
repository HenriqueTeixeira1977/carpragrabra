<?php
session_start();

require_once 'config.php';

/* ----- função segura de limpeza ----- */
function sanitize($field, $filter = FILTER_SANITIZE_SPECIAL_CHARS) {
    return filter_input(INPUT_POST, $field, $filter) ?: '';
}

/* ----- capturar campos ----- */
$nome     = sanitize('nome');
$whats    = sanitize('whatsapp');
$email    = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL) ?: '';
$origem   = sanitize('origem');
$destino  = sanitize('destino');
$mensagem = sanitize('mensagem');

/* ----- validar obrigatórios ----- */
if (!$nome || !$whats || !$email || !$origem || !$destino) {
    header('Location: index.php?erro=Campos%20obrigatórios');
    exit;
}

/* ----- inserir no banco ----- */
$sql = "INSERT INTO leads_carretos
          (nome, whatsapp, email, origem, destino, mensagem)
        VALUES
          (:nome, :whats, :email, :origem, :destino, :mensagem)";

$stmt = $pdo->prepare($sql);

try {
    $stmt->execute([
        ':nome'     => $nome,
        ':whats'    => $whats,
        ':email'    => $email,
        ':origem'   => $origem,
        ':destino'  => $destino,
        ':mensagem' => $mensagem,
    ]);

    /* ------ (Opcional) notific. por e‑mail ------
       use PHPMailer ou similar, se desejar        */

    $_SESSION['nome'] = $nome;
    header('Location: ../actions/enviado.php');
    exit;       

    header('Location: ../actions/enviado.php');
} catch (Exception $e) {
    error_log('Insert erro: ' . $e->getMessage());
    header('Location: index.php?erro=Falha%20ao%20salvar');
}
?>

