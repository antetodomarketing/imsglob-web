<?php
// ===== Lead de la landing de Ahorro Cleanroom =====
$to   = 'info@imsglob.com';
$cc   = 'gio@disruptivamktpwrhs.com'; // copia a la agencia para seguimiento

$fromEmail = 'info@imsglob.com';
$fromName  = 'IMS Global - Landing Cleanroom';

function ims_clean($v){
    return trim(str_replace(array("\r", "\n", "%0a", "%0d"), ' ', (string)$v));
}
$empresa = isset($_POST['empresa']) ? ims_clean($_POST['empresa']) : '';
$name    = isset($_POST['name'])    ? ims_clean($_POST['name'])    : '';
$email   = isset($_POST['email'])   ? ims_clean($_POST['email'])   : '';
$phone   = isset($_POST['phone'])   ? ims_clean($_POST['phone'])   : '';
$sku     = isset($_POST['sku'])     ? ims_clean($_POST['sku'])     : '';
$consumo = isset($_POST['consumo']) ? ims_clean($_POST['consumo']) : '';
$marca   = isset($_POST['marca'])   ? ims_clean($_POST['marca'])   : '';
$variante= isset($_POST['variante'])? ims_clean($_POST['variante']): '-';
$fuente  = isset($_POST['fuente'])  ? ims_clean($_POST['fuente'])  : '';

// ===== Validación mínima =====
if ($empresa === '' || $name === '' || !filter_var($email, FILTER_VALIDATE_EMAIL) || $phone === '' || $sku === '') {
    echo 'failed';
    exit;
}

$subject = 'Nuevo lead Cleanroom (variante ' . $variante . ') - ' . $empresa;

$body  = "Nuevo lead desde la landing /ahorro-cleanroom\n";
$body .= "-------------------------------------------\n";
$body .= "Empresa:            $empresa\n";
$body .= "Nombre y puesto:    $name\n";
$body .= "Correo:             $email\n";
$body .= "Telefono/WhatsApp:  $phone\n\n";
$body .= "Producto o SKU:     $sku\n";
$body .= "Consumo mensual:    $consumo\n";
$body .= "Marca/espec.:       $marca\n\n";
$body .= "Variante landing:   $variante\n";
$body .= "Fuente/referencia:  $fuente\n";

$headers  = "MIME-Version: 1.0\r\n";
$headers .= "Content-Type: text/plain; charset=UTF-8\r\n";
$headers .= "From: $fromName <$fromEmail>\r\n";
$headers .= "Reply-To: $name <$email>\r\n";
if (!empty($cc)) { $headers .= "Cc: $cc\r\n"; }
$headers .= "X-Mailer: PHP/" . phpversion();

$encodedSubject = '=?UTF-8?B?' . base64_encode($subject) . '?=';

$ok = @mail($to, $encodedSubject, $body, $headers, '-f ' . $fromEmail);

echo $ok ? 'sent' : 'failed';
?>
