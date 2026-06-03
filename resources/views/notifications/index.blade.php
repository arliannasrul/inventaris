@extends('layouts.app')

@section('content')
<header class="page-head">
    <div>
        <p class="eyebrow">Pusat notifikasi</p>
        <h1>Notifikasi dan Tindak Lanjut</h1>
    </div>
</header>

<section class="panel">
    <ul class="activity notifications">
        @forelse (($data['notifications'] ?? []) as $notification)
            <li @class(['unread' => empty($notification['read_at'])])>
                <strong>{{ $notification['title'] }}</strong>
                <span>{{ $notification['body'] }}</span>
                @if (empty($notification['read_at']))
                    <form method="post" action="{{ route('notifications.read', $notification['id']) }}">
                        @csrf
                        <button class="button" type="submit">Tandai dibaca</button>
                    </form>
                @endif
            </li>
        @empty
            <li class="empty">Tidak ada notifikasi.</li>
        @endforelse
    </ul>
</section>
@endsection
