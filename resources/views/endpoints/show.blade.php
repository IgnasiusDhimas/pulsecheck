<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Riwayat Pemeriksaan - PulseCheck</title>
    <style>
        * { box-sizing: border-box; }
        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background: #f4f6f9;
            color: #172033;
        }
        .container {
            max-width: 1100px;
            margin: 40px auto;
            padding: 0 20px;
        }
        .card {
            background: white;
            padding: 24px;
            border-radius: 12px;
            margin-bottom: 24px;
            box-shadow: 0 2px 8px #0000000a;
        }
        .muted { color: #64748b; }
        .url { overflow-wrap: anywhere; }
        .table-wrap { overflow-x: auto; }
        table {
            width: 100%;
            border-collapse: collapse;
            min-width: 650px;
        }
        th, td {
            text-align: left;
            padding: 14px 10px;
            border-bottom: 1px solid #e2e8f0;
        }
        th { color: #64748b; font-size: 13px; }
        .up { color: #16a34a; font-weight: bold; }
        .down { color: #dc2626; font-weight: bold; }
        .button {
            display: inline-block;
            padding: 10px 16px;
            border-radius: 7px;
            background: #2563eb;
            color: white;
            text-decoration: none;
            font-weight: bold;
        }
        .empty {
            text-align: center;
            padding: 28px;
            color: #64748b;
        }
        .pagination {
            display: flex;
            gap: 14px;
            flex-wrap: wrap;
            margin-top: 20px;
        }
        .pagination a {
            color: #2563eb;
            text-decoration: none;
        }
        .error {
            color: #dc2626;
            overflow-wrap: anywhere;
        }
    </style>
</head>
<body>
    <main class="container">
        <h1>Riwayat Pemeriksaan</h1>
        <p class="muted">PulseCheck — API Health &amp; Response Monitor</p>

        <section class="card">
            <h2>{{ $endpoint->name }}</h2>
            <p class="url">{{ $endpoint->url }}</p>
            <p class="muted">
                Total pemeriksaan:
                {{ $endpoint->checkResults()->count() }}
            </p>
            <a class="button" href="{{ route('endpoints.index') }}">
                Kembali ke dashboard
            </a>
        </section>

        <section class="card">
            <h2>Riwayat hasil</h2>

            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>Waktu pemeriksaan</th>
                            <th>Status</th>
                            <th>HTTP Code</th>
                            <th>Response Time</th>
                            <th>Pesan</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($checks as $check)
                            <tr>
                                <td>
                                    {{ $check->checked_at->format('d M Y H:i:s') }}
                                </td>
                                <td>
                                    <span class="{{ $check->status === 'up' ? 'up' : 'down' }}">
                                        {{ strtoupper($check->status) }}
                                    </span>
                                </td>
                                <td>
                                    {{ $check->http_status_code ?? '-' }}
                                </td>
                                <td>
                                    {{ $check->response_time_ms !== null
                                        ? $check->response_time_ms . ' ms'
                                        : '-' }}
                                </td>
                                <td>
                                    @if ($check->error_message)
                                        <span class="error">
                                            {{ $check->error_message }}
                                        </span>
                                    @else
                                        -
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="empty">
                                    Belum ada riwayat pemeriksaan.
                                    Klik Check Now pada dashboard untuk memulai.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="pagination">
                {{ $checks->links() }}
            </div>
        </section>
    </main>
</body>
</html>
