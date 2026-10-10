<x-mail::message>
# Hola, {{ $name }}

Use este código para terminar de iniciar sesión en **{{ config('app.name') }}**:

<x-mail::panel>
<span class="code">{{ $code }}</span>
</x-mail::panel>

El código vence en {{ $minutes }} minutos y solo puede usarse una vez. Si usted no intentó iniciar sesión, ignore este mensaje y cambie su contraseña.

Saludos,<br>
{{ config('app.name') }}
</x-mail::message>
