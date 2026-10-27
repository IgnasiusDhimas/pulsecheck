
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
                            <th>Dibuat</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($endpoints as $endpoint)
                            <tr>
                                <td>{{ $endpoint->name }}</td>
                                <td class="url">{{ $endpoint->url }}</td>
                                <td>{{ $endpoint->created_at->format('d M Y H:i') }}</td>
                                <td>
                                    <form
                                        action="{{ route('endpoints.destroy', $endpoint) }}"
                                        method="POST"
                                        onsubmit="return confirm('Yakin ingin menghapus endpoint ini?')"
                                    >
                                        @csrf
                                        @method('DELETE')
                                        <button class="danger" type="submit">Hapus</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="empty">
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
