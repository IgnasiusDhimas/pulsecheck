
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PulseCheck - Endpoint Manager</title>
    <style>
        * { box-sizing: border-box; }
        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background: #f4f6f9;
            color: #172033;
        }
        .container {
            max-width: 1000px;
            margin: 40px auto;
            padding: 0 20px;
        }
        .header { margin-bottom: 28px; }
        .header h1 { margin-bottom: 8px; }
        .muted { color: #64748b; }
        .card {
            background: white;
            padding: 24px;
            border-radius: 12px;
            margin-bottom: 24px;
            box-shadow: 0 2px 8px #0000000a;
        }
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 16px;
            margin-bottom: 24px;
        }

        .stat-card {
            background: white;
            padding: 20px;
            border-radius: 12px;
            box-shadow: 0 2px 8px #0000000a;
        }

        .stat-label {
            color: #64748b;
            font-size: 14px;
            margin-bottom: 10px;
        }

        .stat-value {
            font-size: 28px;
            font-weight: bold;
            overflow-wrap: anywhere;
        }

        @media (min-width: 800px) {
            .stats-grid {
                grid-template-columns: repeat(4, minmax(0, 1fr));
            }
        }
        label {
            display: block;
            font-weight: bold;
            margin-bottom: 8px;
        }
        input {
            width: 100%;
            padding: 12px;
            border: 1px solid #cbd5e1;
            border-radius: 7px;
            margin-bottom: 16px;
        }
        button {
            padding: 10px 16px;
            border: 0;
            border-radius: 7px;
            cursor: pointer;
            font-weight: bold;
        }
        .primary { background: #2563eb; color: white; }
        .danger { background: #fee2e2; color: #b91c1c; }
        .message {
            padding: 12px;
            border-radius: 7px;
            margin-bottom: 18px;
            background: #dcfce7;
            color: #166534;
        }
        .error {
            color: #b91c1c;
            margin: -10px 0 14px;
            font-size: 14px;
        }
        .table-wrap { overflow-x: auto; }
        table {
            width: 100%;
            border-collapse: collapse;
            min-width: 550px;
        }
        th, td {
            text-align: left;
            padding: 14px 10px;
            border-bottom: 1px solid #e2e8f0;
        }
        th { color: #64748b; font-size: 13px; }
        .url { overflow-wrap: anywhere; }
        .empty { text-align: center; padding: 28px; color: #64748b; }
        @media (min-width: 700px) {
            .form-grid {
                display: grid;
                grid-template-columns: 1fr 2fr;
                gap: 16px;
            }
        }
    </style>
</head>
<body>
    <main class="container">
        <header class="header">
            <h1>PulseCheck</h1>
            <p class="muted">API Health &amp; Response Monitor</p>
        </header>
        <section class="stats-grid">
            <div class="stat-card">
                <div class="stat-label">Total Pemeriksaan</div>
                <div class="stat-value">{{ $totalChecks }}</div>
            </div>

            <div class="stat-card">
                <div class="stat-label">Pemeriksaan UP</div>
                <div class="stat-value" style="color: #16a34a;">
                    {{ $upChecks }}
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-label">Pemeriksaan DOWN</div>
                <div class="stat-value" style="color: #dc2626;">
                    {{ $downChecks }}
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-label">Rata-rata Response Time</div>
                <div class="stat-value">
                    @if ($avgResponseTime !== null)
                        {{ number_format($avgResponseTime, 2) }}
                        <span style="font-size: 14px;">ms</span>
                    @else
                        -
                    @endif
                </div>
            </div>
        </section>
        @if (session('success'))
            <div class="message">{{ session('success') }}</div>
        @endif

        @if ($errors->any())
            <div class="card">
                <strong>Periksa kembali input kamu:</strong>
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <section class="card">
            <h2>Tambah endpoint</h2>
            <p class="muted">Masukkan nama dan URL API publik yang ingin dipantau.</p>

            <form action="{{ route('endpoints.store') }}" method="POST">
                @csrf

                <div class="form-grid">
                    <div>
                        <label for="name">Nama endpoint</label>
                        <input
                            id="name"
                            name="name"
                            type="text"
                            maxlength="100"
                            placeholder="Contoh: JSONPlaceholder"
                            value="{{ old('name') }}"
                            required
                        >
                    </div>

                    <div>
                        <label for="url">URL API</label>
                        <input
                            id="url"
                            name="url"
                            type="url"
                            maxlength="2048"
                            placeholder="https://jsonplaceholder.typicode.com/posts/1"
                            value="{{ old('url') }}"
                            required
                        >
                    </div>
                </div>

                <button class="primary" type="submit">Tambah endpoint</button>
            </form>
        </section>

        <section class="card">
            <h2>Daftar endpoint</h2>
            <p class="muted">Total endpoint: {{ $endpoints->count() }}</p>

            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>Nama</th>
                            <th>URL</th>
                            <th>Status</th>
                            <th>Response Time</th>
                            <th>Dibuat</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($endpoints as $endpoint)
                            @php
                                $latestCheck = $endpoint->checkResults->first();
                            @endphp

                            <tr>
                                <td>{{ $endpoint->name }}</td>

                                <td class="url">{{ $endpoint->url }}</td>

                                <td>
                                    @if (!$latestCheck)
                                        <span class="muted">Belum diperiksa</span>
                                    @elseif ($latestCheck->status === 'up')
                                        <span style="color: #16a34a; font-weight: bold;">UP</span>
                                    @else
                                        <span style="color: #dc2626; font-weight: bold;">DOWN</span>
                                    @endif

                                    @if ($latestCheck && $latestCheck->http_status_code)
                                        <br>
                                        <small>HTTP {{ $latestCheck->http_status_code }}</small>
                                    @endif
                                </td>

                                <td>
                                    @if ($latestCheck && $latestCheck->response_time_ms !== null)
                                        {{ $latestCheck->response_time_ms }} ms
                                    @else
                                        -
                                    @endif
                                </td>

                                <td>{{ $endpoint->created_at->format('d M Y H:i') }}</td>

                                <td>
                                <div style="display: flex; gap: 8px; flex-wrap: wrap;">
                                    <form
                                        action="{{ route('endpoints.check', $endpoint) }}"
                                        method="POST"
                                    >
                                        @csrf
                                        <button class="primary" type="submit">Check Now</button>
                                    </form>

                                    <a
                                        href="{{ route('endpoints.show', $endpoint) }}"
                                        style="display: inline-block; padding: 10px 16px; border-radius: 7px; background: #e0e7ff; color: #3730a3; text-decoration: none; font-weight: bold;"
                                    >
                                        Riwayat
                                    </a>

                                    <form
                                        action="{{ route('endpoints.destroy', $endpoint) }}"
                                        method="POST"
                                        onsubmit="return confirm('Yakin ingin menghapus endpoint ini?')"
                                    >
                                        @csrf
                                        @method('DELETE')
                                        <button class="danger" type="submit">Hapus</button>
                                    </form>
                                </div>


                                    @if ($latestCheck)
                                        <small class="muted">
                                            Diperiksa:
                                            {{ $latestCheck->checked_at->format('d M Y H:i:s') }}
                                        </small>

                                        @if ($latestCheck->error_message)
                                            <p style="color: #dc2626; font-size: 12px;">
                                                {{ $latestCheck->error_message }}
                                            </p>
                                        @endif
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="empty">
                                    Belum ada endpoint. Tambahkan endpoint pertama kamu!
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    </main>
</body>
</html>
