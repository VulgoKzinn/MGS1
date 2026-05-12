<?php
require_once "backend/includes/funcoes.php";
session_start();
validaAcesso();

// Pegamos o ID da URL. Se não existir, redireciona.
$id = filter_input(INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT);
if (!$id) {
    header("Location: perfil-candidato.php");
    exit();
}

// Busca os dados atuais do candidato para preencher o formulário
$dados = listaCandidatoId($id);

// Caso seja clicado no botão de atualizar
if (isset($_POST['atualizar'])) {
    $nome = filter_input(INPUT_POST, 'nome');
    $cargo = filter_input(INPUT_POST, 'cargo');
    $email = filter_input(INPUT_POST, 'email');
    $telefone = filter_input(INPUT_POST, 'telefone');
    $endereco = filter_input(INPUT_POST, 'endereco');
    $sobre = filter_input(INPUT_POST, 'sobre');
    $experiencia = filter_input(INPUT_POST, 'experiencia');
    $projeto = filter_input(INPUT_POST, 'projeto');
    $certificado_atual = $dados['certificado']; // Mantém o antigo se não subir novo

    // 1. Atualiza os dados textuais
    atualizarCandidato($id, $nome, $email, $telefone, $endereco, $sobre, $cargo, $experiencia, $projeto, $certificado_atual);

    // 2. Upload da Foto de Perfil (se enviada)
    if (!empty($_FILES['perfilInput']['name'])) {
        $nomeFotoPerfil = uploadImagems($_FILES['perfilInput']);
        if ($nomeFotoPerfil) {
            cadastrarFotoPerfil($id, $nomeFotoPerfil);
        }
    }

    // 3. Upload da Foto de Capa (se enviada)
    if (!empty($_FILES['capaInput']['name'])) {
        $nomeBanner = uploadImagems($_FILES['capaInput']);
        if ($nomeBanner) {
            cadastrarFotoBanner($id, $nomeBanner);
        }
    }

    // 4. Upload do Certificado (se enviado)
    if (!empty($_FILES['certificadoInput']['name'])) {
        $nomeCert = uploadCertificado($_FILES['certificadoInput']);
        if ($nomeCert) {
            cadastrarArquivoCertificado($id, $nomeCert);
        }
    }
    
    header("Location: perfil-candidato.php");
    exit();
}
?>

<!doctype html>
<html lang="pt-br">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Editar Perfil | Matchwork</title>
    <?php require_once 'assets/templates/head.php'; ?>
    <style>
        body#login { background-color: #6f42c1 !important; display: flex !important; flex-direction: column !important; align-items: center !important; min-height: 100vh !important; padding-bottom: 50px !important; }
        #EditarPerfilLargo { width: 100% !important; max-width: 900px !important; background: white !important; border-radius: 15px !important; padding: 40px !important; box-shadow: 0 10px 25px rgba(0,0,0,0.2) !important; margin-top: 20px !important; }
        .preview-img { border: 3px solid #6f42c1; object-fit: cover; border-radius: 10px; }
        .btn-finalizar { background-color: #198754 !important; color: white !important; font-weight: bold !important; border: none !important; }
        .btn-finalizar:hover { background-color: #157347 !important; }
    </style>
</head>
<body id="login">
    <div id="ImgLogon" class="text-center mt-4 mb-2">
        <a href="index.php"><img src="assets/img/logomaior.png" alt="Logo" style="max-width: 200px;"></a>
    </div>

    <main id="EditarPerfilLargo">
        <form method="post" enctype="multipart/form-data">
            <div class="text-end mb-2">
                <a href="perfil-candidato.php" class="btn-voltar-projeto"><i class="fa-solid fa-arrow-left"></i> Voltar</a>
            </div>

            <h2 class="text-center mb-5" style="color: #6f42c1; font-weight: bold;">Editar Perfil</h2>

            <div class="row">
                <div class="col-md-5 border-end pe-md-4 text-center">
                    <div class="mb-4">
                        <label class="form-label fw-bold">Foto de Capa</label>
                        <img id="previewCapa" src="assets/img/perfil-candidato/uploads/<?php echo $dados['id_foto_banner'] ?: 'padrao-capa.png'; ?>" class="img-fluid mb-2 preview-img" style="height: 140px; width: 100%;">
                        <input type="file" name="capaInput" id="capaInput" class="form-control form-control-sm">
                    </div>
                    <div class="mb-4">
                        <label class="form-label fw-bold">Foto de Perfil</label><br>
                        <img id="previewPerfil" src="assets/img/perfil-candidato/uploads/<?php echo $dados['id_foto_perfil'] ?: 'padrao.png'; ?>" class="rounded-circle mb-2 preview-img" style="width: 150px; height: 150px;">
                        <input type="file" name="perfilInput" id="perfilInput" class="form-control form-control-sm">
                    </div>
                </div>

                <div class="col-md-7 ps-md-4">
                    <div class="mb-3">
                        <label class="form-label fw-bold text-muted">Nome Completo</label>
                        <input type="text" name="nome" class="form-control shadow-sm" value="<?php echo $dados['nome']; ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold text-muted">Cargo / Especialidade</label>
                        <input type="text" name="cargo" class="form-control shadow-sm" value="<?php echo $dados['cargo']; ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold text-muted">E-mail Profissional</label>
                        <input type="email" name="email" class="form-control shadow-sm" value="<?php echo $dados['email']; ?>" required>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold text-muted">WhatsApp</label>
                            <input type="text" name="telefone" class="form-control shadow-sm" value="<?php echo $dados['telefone']; ?>">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold text-muted">Cidade / CEP</label>
                            <input type="text" name="endereco" class="form-control shadow-sm" value="<?php echo $dados['endereco']; ?>">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold text-muted">Biografia (Sobre)</label>
                        <textarea name="sobre" class="form-control shadow-sm" style="height: 80px;" required><?php echo $dados['sobre']; ?></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold text-muted">Experiências</label>
                        <textarea name="experiencia" class="form-control shadow-sm" style="height: 80px;"><?php echo $dados['experiencia']; ?></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold text-muted">Meus Projetos</label>
                        <textarea name="projeto" class="form-control shadow-sm" style="height: 80px;"><?php echo $dados['projeto']; ?></textarea>
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-bold text-muted">Certificado Atual: <?php echo $dados['certificado']; ?></label>
                        <input type="file" name="certificadoInput" class="form-control shadow-sm" accept=".pdf, .jpg, .jpeg, .png">
                    </div>

                    <button type="submit" name="atualizar" class="btn btn-finalizar btn-lg w-100 shadow">
                        <i class="fa-solid fa-floppy-disk me-2"></i> Salvar Alterações
                    </button>
                </div>
            </div>
        </form>
    </main>

    <script>
        document.getElementById('capaInput').onchange = e => {
            if (e.target.files[0]) {
                const r = new FileReader();
                r.onload = () => document.getElementById('previewCapa').src = r.result;
                r.readAsDataURL(e.target.files[0]);
            }
        };
        document.getElementById('perfilInput').onchange = e => {
            if (e.target.files[0]) {
                const r = new FileReader();
                r.onload = () => document.getElementById('previewPerfil').src = r.result;
                r.readAsDataURL(e.target.files[0]);
            }
        };
    </script>
    <?php require_once 'assets/templates/js.php'; ?>
</body>
</html>