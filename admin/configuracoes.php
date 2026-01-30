<?php 
require_once '../includes/header_admin.php'; 

$msg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['logo'])) {
    $file = $_FILES['logo'];
    
    // Validação Básica
    if ($file['type'] !== 'image/png') {
        $msg = "<span style='color:red'>Erro: Apenas arquivos PNG são permitidos.</span>";
    } else {
        $ext = 'png';
        $nome_arquivo = "logo_empresa_" . $_SESSION['empresa_id'] . "." . $ext;
        $destino = "../assets/img/" . $nome_arquivo;
        
        // Cria pasta se não existir
        if (!is_dir('../assets/img/')) mkdir('../assets/img/', 0777, true);
        
        if (move_uploaded_file($file['tmp_name'], $destino)) {
            // Atualiza caminho no banco
            $path_db = "assets/img/" . $nome_arquivo;
            $pdo->prepare("UPDATE empresas SET logo_url = ? WHERE id = ?")->execute([$path_db, $_SESSION['empresa_id']]);
            $msg = "<span style='color:green'>Logo atualizado com sucesso! Atualize a página.</span>";
            
            // Força refresh para carregar a nova logo no header
            echo "<meta http-equiv='refresh' content='1'>";
        } else {
            $msg = "<span style='color:red'>Erro ao salvar arquivo.</span>";
        }
    }
}
?>

<div class="page-title">
    <h2>Configurações do Sistema</h2>
    <div class="breadcrumb">Home / <span>Configurações</span></div>
</div>

<div class="card" style="max-width: 500px;">
    <h3 style="margin-bottom: 20px;">Identidade Visual</h3>
    
    <?php if($msg): ?><div style="margin-bottom:15px;"><?= $msg ?></div><?php endif; ?>

    <form method="POST" enctype="multipart/form-data">
        <label style="display:block; font-weight:bold; margin-bottom:10px;">Upload de Logo (PNG)</label>
        <p style="font-size:12px; color:#666; margin-bottom:10px;">A imagem substituirá o texto "SIGYO" no topo. Fundo transparente recomendado.</p>
        
        <input type="file" name="logo" accept="image/png" required style="margin-bottom: 20px;">
        
        <button type="submit" class="btn btn-primary">SALVAR LOGO</button>
    </form>
</div>

</body>
</html>
