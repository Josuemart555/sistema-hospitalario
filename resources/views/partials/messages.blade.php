@php($formErrors = $errors->any() ? $errors : ($errors->hasBag('updatePassword') ? $errors->getBag('updatePassword') : null))
@php($statusMessages = ['password-updated' => 'Contraseña actualizada correctamente.'])
@if(session('success'))<div class="alert alert-success d-flex gap-2" role="status"><i class="bi bi-check-circle"></i><span>{{ session('success') }}</span></div>@endif
@if(session('status'))<div class="alert alert-info" role="status">{{ $statusMessages[session('status')] ?? session('status') }}</div>@endif
@if($formErrors)<div class="alert alert-danger" role="alert"><div class="fw-bold mb-1">Revise la información:</div><ul class="mb-0">@foreach($formErrors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
