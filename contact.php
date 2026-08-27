<?php
// ===== Destinatario(s) =====
$to = 'info@imsglob.com';
// Para enviar copia a otro correo, descomenta y ajusta:
// $cc = 'ventas@imsglob.com';

$subject = 'Nuevo mensaje de contacto - IMS Global';

// ===== Remitente: DEBE ser tu propio dominio para que no caiga en spam =====
$fromEmail = 'info@imsglob.com';
$fromName  = 'IMS Global - Sitio Web';

// ===== Recopilar y limpiar datos =====
function ims_clean($v){
    return trim(str_replace(array("\r", "\n", "%0a", "%0d"), ' ', (string)$v));
}
$name  = isset($_POST['name'])    ? ims_clean($_POST['name'])  : '';
$email = isset($_POST['email'])   ? ims_clean($_POST['email']) : '';
$phone = isset($_POST['phone'])   ? ims_clean($_POST['phone']) : '';
$msg   = isset($_POST['message']) ? trim($_POST['message'])    : '';

// ===== Validación =====
if ($name === '' || !filter_var($email, FILTER_VALIDATE_EMAIL) || $msg === '') {
    echo 'failed';
    exit;
}

// ===== Cuerpo del correo =====
$body  = "Nuevo mensaje desde el formulario de imsglob.com\n\n";
$body .= "Nombre:   $name\n";
$body .= "Correo:   $email\n";
$body .= "Telefono: $phone\n\n";
$body .= "Mensaje:\n$msg\n";

// ===== Encabezados (con saltos de linea correctos) =====
$headers  = "MIME-Version: 1.0\r\n";
$headers .= "Content-Type: text/plain; charset=UTF-8\r\n";
$headers .= "From: $fromName <$fromEmail>\r\n";
$headers .= "Reply-To: $name <$email>\r\n";
if (!empty($cc)) {
    $headers .= "Cc: $cc\r\n";
}
$headers .= "X-Mailer: PHP/" . phpversion();

// Asunto codificado para soportar acentos
$encodedSubject = '=?UTF-8?B?' . base64_encode($subject) . '?=';

// El parametro -f fija el remitente del "sobre" y ayuda a pasar SPF en Hostinger
$ok = @mail($to, $encodedSubject, $body, $headers, '-f ' . $fromEmail);

echo $ok ? 'sent' : 'failed';
?>
