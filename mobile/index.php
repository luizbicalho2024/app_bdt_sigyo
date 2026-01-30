<?php 
require_once '../includes/header_mobile.php'; 

$id_motorista = $_SESSION['user_id'];

// 1. Verifica se já existe viagem em aberto (Para mostrar botão de Retomar)
$stmt = $pdo->prepare("SELECT v.*, ve.modelo, ve.placa FROM viagens v JOIN veiculos ve ON v.veiculo_id = ve.id WHERE v.motorista_id = ? AND v.status = 'ABERTO' LIMIT 1");
$stmt->execute([$id_motorista]);
$viagem_aberta = $stmt->fetch();

// 2. Calcula Estatísticas do Dia (HOJE)
$sqlStats = "
    SELECT 
        COUNT(*) as total_viagens,
        COALESCE(SUM(hodometro_final - hodometro_inicial), 0) as total_km,
        COALESCE(SUM(TIMESTAMPDIFF(MINUTE, data_inicio, data_fim)), 0) as total_minutos
    FROM viagens 
    WHERE motorista_id = ? 
    AND status = 'FINALIZADO' 
    AND DATE(data_inicio) = CURDATE()
";
$stmtStats = $pdo->prepare($sqlStats);
$stmtStats->execute([$id_motorista]);
$stats = $stmtStats->fetch();

// Formata horas e minutos
$horas = floor($stats->total_minutos / 60);
$minutos = $stats->total_minutos % 60;
$tempo_formatado = sprintf("%dh %02dm", $horas, $minutos);

// 3. Busca histórico recente
$stmtHist = $pdo->prepare("
    SELECT v.id, v.data_inicio, v.data_fim, v.hodometro_inicial, v.hodometro_final, ve.modelo 
    FROM viagens v 
    JOIN veiculos ve ON v.veiculo_id = ve.id 
    WHERE v.motorista_id = ? AND v.status = 'FINALIZADO' 
    ORDER BY v.data_inicio DESC LIMIT 5
");
$stmtHist->execute([$id_motorista]);
$historico = $stmtHist->fetchAll();

// Saudação baseada na hora
$hora_atual = date('H');
if($hora_atual < 12) $saudacao = "Bom dia";
elseif($hora_atual < 18) $saudacao = "Boa tarde";
else $saudacao = "Boa noite";
?>

<div style="background: #1A1C23; color: #fff; padding: 20px 20px 60px 20px; margin: -15px -15px 20px -15px; border-radius: 0 0 20px 20px;">
    <div style="font-size: 14px; opacity: 0.8;"><?= $saudacao ?>,</div>
    <div style="font-size: 24px; font-weight: bold;"><?= explode(' ', $_SESSION['user_nome'])[0] ?></div>
    <div style="font-size: 12px; margin-top: 5px; opacity: 0.6;">
        <i class="far fa-calendar-alt"></i> <?= date('d/m/Y') ?>
    </div>
</div>

<div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 10px; margin-top: -50px; padding: 0 10px;">
    
    <div class="mobile-card" style="text-align: center; padding: 15px 5px; box-shadow: 0 4px 10px rgba(0,0,0,0.1);">
        <div style="color: #4834D4; font-size: 18px; margin-bottom: 5px;"><i class="fas fa-road"></i></div>
        <div style="font-weight: bold; font-size: 16px; color: #333;"><?= $stats->total_km ?></div>
        <div style="font-size: 10px; color: #888;">Km Hoje</div>
    </div>

    <div class="mobile-card" style="text-align: center; padding: 15px 5px; box-shadow: 0 4px 10px rgba(0,0,0,0.1);">
        <div style="color: #2ECC71; font-size: 18px; margin-bottom: 5px;"><i class="fas fa-clock"></i></div>
        <div style="font-weight: bold; font-size: 16px; color: #333;"><?= $tempo_formatado ?></div>
        <div style="font-size: 10px; color: #888;">Tempo Hoje</div>
    </div>

    <div class="mobile-card" style="text-align: center; padding: 15px 5px; box-shadow: 0 4px 10px rgba(0,0,0,0.1);">
        <div style="color: #F39C12; font-size: 18px; margin-bottom: 5px;"><i class="fas fa-check-circle"></i></div>
        <div style="font-weight: bold; font-size: 16px; color: #333;"><?= $stats->total_viagens ?></div>
        <div style="font-size: 10px; color: #888;">Viagens</div>
    </div>
</div>

<div style="padding: 10px;">

    <?php if ($viagem_aberta): ?>
        <div class="mobile-card" style="border-left: 5px solid #2ECC71; background: #E8F8F5; margin-top: 20px;">
            <div style="display: flex; justify-content: space-between; align-items: center;">
                <div>
                    <h3 style="color: #2ECC71; font-size: 14px; margin-bottom: 5px;">VIAGEM EM ANDAMENTO</h3>
                    <div style="font-weight: bold; font-size: 16px;"><?= $viagem_aberta->modelo ?></div>
                    <div style="font-size: 12px; color: #666;"><?= $viagem_aberta->placa ?></div>
                </div>
                <div style="text-align: right;">
                    <a href="viagem.php" class="btn btn-primary" style="background: #2ECC71; padding: 10px 15px; border-radius: 20px; font-size: 12px;">
                        RETOMAR <i class="fas fa-arrow-right"></i>
                    </a>
                </div>
            </div>
            <div style="margin-top: 10px; border-top: 1px solid rgba(0,0,0,0.05); padding-top: 8px; font-size: 12px; color: #555;">
                <i class="far fa-clock"></i> Iniciado às <?= date('H:i', strtotime($viagem_aberta->data_inicio)) ?>
            </div>
        </div>
    <?php else: ?>
        <div class="mobile-card" style="text-align: center; padding: 30px 20px; margin-top: 20px;">
            <div style="width: 60px; height: 60px; background: #eef; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 15px auto; color: #4834D4;">
                <i class="fas fa-car" style="font-size: 24px;"></i>
            </div>
            <h3 style="margin-bottom: 5px; color: #333;">Pronto para sair?</h3>
            <p style="color: #888; font-size: 13px; margin-bottom: 20px;">Registre sua saída e realize o checklist.</p>
            
            <a href="checkin.php" class="btn btn-primary btn-block" style="font-size: 16px; padding: 15px; border-radius: 8px; box-shadow: 0 4px 15px rgba(72, 52, 212, 0.3);">
                INICIAR NOVA VIAGEM
            </a>
        </div>
    <?php endif; ?>

    <div style="margin-top: 25px;">
        <h4 style="color: #555; margin-bottom: 15px; font-size: 14px; text-transform: uppercase; letter-spacing: 1px;">Histórico Recente</h4>
        
        <?php foreach($historico as $h): 
            $km_percorrido = $h->hodometro_final - $h->hodometro_inicial;
            $duracao = strtotime($h->data_fim) - strtotime($h->data_inicio);
            $duracao_fmt = gmdate("H:i", $duracao);
        ?>
        <div class="mobile-card" style="display: flex; justify-content: space-between; align-items: center; padding: 15px; margin-bottom: 10px;">
            <div style="display: flex; align-items: center; gap: 15px;">
                <div style="background: #f0f2f5; width: 40px; height: 40px; border-radius: 8px; display: flex; align-items: center; justify-content: center; color: #666;">
                    <i class="fas fa-history"></i>
                </div>
                <div>
                    <div style="font-weight: bold; font-size: 14px; color: #333;"><?= $h->modelo ?></div>
                    <div style="font-size: 11px; color: #888;">
                        <?= date('d/m', strtotime($h->data_inicio)) ?> • <?= date('H:i', strtotime($h->data_inicio)) ?> - <?= date('H:i', strtotime($h->data_fim)) ?>
                    </div>
                </div>
            </div>
            
            <div style="text-align: right;">
                <div style="font-weight: bold; font-size: 14px; color: #4834D4;"><?= $km_percorrido ?> km</div>
                <div style="font-size: 11px; color: #888;"><?= $duracao_fmt ?>h</div>
            </div>
        </div>
        <?php endforeach; ?>

        <?php if(count($historico) == 0): ?>
            <div style="text-align: center; padding: 20px; color: #999; font-size: 13px;">
                <i class="far fa-folder-open" style="font-size: 24px; margin-bottom: 5px;"></i><br>
                Nenhuma viagem realizada ainda.
            </div>
        <?php endif; ?>
    </div>

</div>

</body>
</html>
