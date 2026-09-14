@extends('layouts.app')
@section('title', 'Log Chatbot WhatsApp')
@section('header', 'Log Percakapan Chatbot')

@section('content')
<div class="space-y-4">
    {{-- Filter --}}
    <div class="bg-white rounded-2xl border border-slate-200 p-5">
        <form method="GET" class="flex items-center gap-4">
            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1">Cari Nomor HP</label>
                <input type="search" name="no_hp" value="{{ request('no_hp') }}" placeholder="08..."
                    class="rounded-xl border-slate-200 py-2 px-3 text-sm focus:ring-2 focus:ring-brand-500 w-52">
            </div>
            <div class="self-end flex gap-2">
                <button type="submit" class="bg-brand-600 text-white px-4 py-2 rounded-xl text-sm font-semibold">Cari</button>
                <a href="{{ route('whatsapp.log-chatbot') }}" class="bg-white border border-slate-200 text-slate-600 px-4 py-2 rounded-xl text-sm font-semibold">Reset</a>
            </div>
        </form>
    </div>

    {{-- Table --}}
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <table class="w-full text-sm text-left">
            <thead class="bg-slate-50 text-slate-700 border-b border-slate-200">
                <tr>
                    <th class="px-4 py-3 font-semibold">Nomor HP</th>
                    <th class="px-4 py-3 font-semibold">Siswa</th>
                    <th class="px-4 py-3 font-semibold">Pesan Masuk</th>
                    <th class="px-4 py-3 font-semibold">Balasan Bot</th>
                    <th class="px-4 py-3 font-semibold">Intent</th>
                    <th class="px-4 py-3 font-semibold">Waktu</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($logs as $log)
                <tr class="hover:bg-slate-50 transition">
                    <td class="px-4 py-3 font-mono text-xs text-slate-700">{{ $log->no_hp }}</td>
                    <td class="px-4 py-3 text-slate-600 text-xs">{{ $log->siswa?->nama_lengkap ?? '—' }}</td>
                    <td class="px-4 py-3">
                        <span class="inline-block bg-blue-50 text-blue-800 px-2 py-1 rounded text-xs max-w-xs truncate">{{ $log->pesan_masuk }}</span>
                    </td>
                    <td class="px-4 py-3 text-xs text-slate-600 max-w-xs">
                        <div class="truncate" title="{{ $log->pesan_keluar }}">{{ mb_substr($log->pesan_keluar, 0, 80) }}{{ mb_strlen($log->pesan_keluar) > 80 ? '...' : '' }}</div>
                    </td>
                    <td class="px-4 py-3">
                        <span class="inline-flex px-2 py-1 rounded text-xs font-bold bg-slate-100 text-slate-600">{{ $log->intent ?? '—' }}</span>
                    </td>
                    <td class="px-4 py-3 text-xs text-slate-500 font-mono">{{ $log->created_at ? $log->created_at->timezone('Asia/Jakarta')->translatedFormat('d M Y, H:i') : '—' }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="px-4 py-10 text-center text-slate-500">Belum ada log percakapan.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
        <div class="px-4 py-3 border-t border-slate-100">
            {{ $logs->links() }}
        </div>
    </div>
</div>
@endsection
