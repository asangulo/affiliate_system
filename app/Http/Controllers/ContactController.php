<?php

namespace App\Http\Controllers;

use App\Mail\ContactMessage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class ContactController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nombre'   => ['required', 'string', 'max:150'],
            'telefono' => ['nullable', 'string', 'max:30'],
            'correo'   => ['required', 'email', 'max:150'],
            'mensaje'  => ['required', 'string', 'max:2000'],
        ], [
            'nombre.required'  => 'Indica tu nombre.',
            'correo.required'  => 'Indica tu correo electrónico.',
            'correo.email'     => 'El correo no tiene un formato válido.',
            'mensaje.required' => 'Escribe tu mensaje.',
        ]);

        try {
            Mail::to('horizontepreexequialessas@gmail.com')
                ->send(new ContactMessage(
                    nombre: $validated['nombre'],
                    telefono: $validated['telefono'] ?? null,
                    correo: $validated['correo'],
                    mensaje: $validated['mensaje'],
                ));
        } catch (\Throwable $e) {
            Log::error('Error al enviar mensaje de contacto: ' . $e->getMessage());

            return back()->withInput()->with('error',
                'No se pudo enviar el mensaje. Intenta de nuevo o escríbenos directamente a horizontepreexequialessas@gmail.com.');
        }

        return back()->with('message', 'Tu mensaje fue enviado. Te contactaremos pronto.');
    }
}
