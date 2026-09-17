<div class="d-flex gap-2 align-items-center">
    <img class="avatar d-none d-sm-block" src="{{ $user->avatar_url }}" alt="">
    <div>
        <div class="fw-semibold">{{ $user->name }}</div>
        <small class="text-muted text-break">{{ $user->email }}</small>
        <div class="d-md-none mt-1">
            @include('admin.users.columns.status')
        </div>
    </div>
</div>
