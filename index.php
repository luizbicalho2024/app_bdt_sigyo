<?php
require_once 'config/db.php';

$erro = '';
$debug_msg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $senha = $_POST['senha'];

    // DEBUG: Mostra o que foi recebido (remova em produção)
    // echo "Tentando logar: $email com senha: $senha <br>";

    if ($email && $senha) {
        $stmt = $pdo->prepare("SELECT * FROM motoristas WHERE email = ? AND senha = MD5(?) AND ativo = 1");
        $stmt->execute([$email, $senha]);
        $user = $stmt->fetch();

        if ($user) {
            $_SESSION['user_id'] = $user->id;
            $_SESSION['user_nome'] = $user->nome;
            $_SESSION['user_perfil'] = $user->perfil;
            $_SESSION['empresa_id'] = $user->empresa_id;

            // Força salvamento da sessão
            session_write_close();

            if ($user->perfil === 'GESTOR') {
                header('Location: admin/dashboard.php');
            } else {
                header('Location: mobile/index.php');
            }
            exit;
        } else {
            $erro = "E-mail ou senha inválidos.";
            // DEBUG: Verifique se existe o usuário sem checar senha
            $check = $pdo->prepare("SELECT * FROM motoristas WHERE email = ?");
            $check->execute([$email]);
            if($found = $check->fetch()) {
                $erro .= " (Usuário existe, mas a senha hash MD5 não bateu. Hash no banco: " . $found->senha . ")";
            } else {
                $erro .= " (E-mail não encontrado no banco)";
            }
        }
    } else {
        $erro = "Preencha todos os campos.";
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Sigyo BDT</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        body { display: flex; align-items: center; justify-content: center; height: 100vh; background: #1A1C23; }
        .login-card { background: #fff; padding: 40px; border-radius: 8px; width: 100%; max-width: 400px; box-shadow: 0 4px 15px rgba(0,0,0,0.2); text-align: center; }
        .login-logo { font-size: 28px; font-weight: bold; color: #1A1C23; margin-bottom: 30px; letter-spacing: 2px; }
        .form-group { margin-bottom: 15px; text-align: left; }
        .form-control { width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px; }
        .alert { color: #E74C3C; background: #FADBD8; padding: 10px; border-radius: 4px; margin-bottom: 20px; font-size: 13px; word-break: break-all; }
        .btn { width: 100%; padding: 10px; background: #4834D4; color: white; border: none; border-radius: 4px; cursor: pointer; }
    </style>
</head>
<body>
    <div class="login-card">
        <div class="login-logo">SIGYO <span style="color:#FF9F43">BDT</span></div>
        
        <?php if($erro): ?>
            <div class="alert"><?= $erro ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="form-group">
                <label>E-mail</label>
                <input type="email" name="email" class="form-control" placeholder="ex: admin@sigyo.com" required>
            </div>
            <div class="form-group">
                <label>Senha</label>
                <input type="password" name="senha" class="form-control" placeholder="******" required>
            </div>
            <button type="submit" class="btn">ENTRAR</button>
        </form>
    </div>
</body>
</html>