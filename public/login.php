<?php
require dirname(__DIR__) . '/includes/bootstrap.php';
$error=null;
if ($_SERVER['REQUEST_METHOD']==='POST') {
  assertCsrf();
  $email=trim((string)($_POST['email']??''));
  $senha=(string)($_POST['senha']??'');
  try {
    $stmt=db()->prepare("SELECT id,senha_hash,perfil FROM usuarios WHERE email=? AND ativo=1 LIMIT 1");
    $stmt->execute([$email]); $user=$stmt->fetch();
    if ($user && password_verify($senha,$user['senha_hash']) && $user['perfil']==='admin') {
      session_regenerate_id(true); $_SESSION['user_id']=$user['id']; $_SESSION['perfil']='admin';
      header('Location: admin/produtos.php'); exit;
    }
  } catch(Throwable $ex) { error_log($ex->getMessage()); }
  $error='Credenciais inválidas ou acesso indisponível.';
}
?><!doctype html><html lang="pt-BR"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Admin — VittaPet</title><link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"><link href="assets/css/tokens.css" rel="stylesheet"></head><body><main class="container" style="max-width:460px"><div class="card mt-5 p-4"><h1 class="h3 fw-bold">Acesso administrativo</h1><p class="text-muted">Área restrita da VittaPet Store</p><?php if($error):?><div class="alert alert-danger"><?=e($error)?></div><?php endif;?><form method="post"><input type="hidden" name="csrf" value="<?=e(csrf())?>"><label class="form-label" for="email">E-mail</label><input required type="email" id="email" name="email" class="form-control mb-3"><label for="senha" class="form-label">Senha</label><input required type="password" id="senha" name="senha" class="form-control mb-3"><button class="btn btn-primary w-100" type="submit">Entrar</button></form><a href="index.php" class="mt-3">Voltar à loja</a></div></main></body></html>
