<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Nuevo mensaje de contacto</title>
</head>
<body style="margin:0; padding:0; background:#f0f4f8; font-family: Arial, sans-serif;">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#f0f4f8; padding:24px 0;">
        <tr>
            <td align="center">
                <table role="presentation" width="480" cellpadding="0" cellspacing="0" style="background:#ffffff; border-radius:12px; overflow:hidden;">
                    <tr>
                        <td style="background:#0a3d6b; padding:20px 28px;">
                            <span style="color:#fff; font-size:18px; font-weight:bold;">Horizonte S.A.S Pre Exequiales</span>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:24px 28px;">
                            <p style="margin:0 0 16px; font-size:15px; color:#1e293b;">
                                Nuevo mensaje recibido desde el formulario de contacto de la página web.
                            </p>
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="font-size:14px; color:#334155;">
                                <tr>
                                    <td style="padding:6px 0; width:90px; color:#64748b;">Nombre</td>
                                    <td style="padding:6px 0;">{{ $nombre }}</td>
                                </tr>
                                <tr>
                                    <td style="padding:6px 0; color:#64748b;">Correo</td>
                                    <td style="padding:6px 0;">{{ $correo }}</td>
                                </tr>
                                <tr>
                                    <td style="padding:6px 0; color:#64748b;">Teléfono</td>
                                    <td style="padding:6px 0;">{{ $telefono ?: 'No indicado' }}</td>
                                </tr>
                            </table>
                            <p style="margin:20px 0 6px; font-size:13px; font-weight:bold; color:#64748b; text-transform:uppercase;">Mensaje</p>
                            <p style="margin:0; font-size:14px; color:#1e293b; line-height:1.6; white-space:pre-line;">{{ $mensaje }}</p>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:16px 28px; background:#f8fafc; font-size:12px; color:#94a3b8;">
                            Puedes responder directamente a este correo para contactar a {{ $nombre }}.
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
