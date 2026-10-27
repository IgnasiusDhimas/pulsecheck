<?php

namespace App\Http\Controllers;

use App\Models\MonitoredEndpoint;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MonitoredEndpointController extends Controller
{
    public function index(): View
    {
        $endpoints = MonitoredEndpoint::latest()->get();

        return view('endpoints.index', compact('endpoints'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'url' => ['required', 'url:http,https', 'max:2048'],
        ]);

        $host = parse_url($validated['url'], PHP_URL_HOST);

        if (!$host) {
            return back()
                ->withErrors(['url' => 'URL tidak valid.'])
                ->withInput();
        }

        $ip = filter_var($host, FILTER_VALIDATE_IP);

        if (
            $ip !== false &&
            filter_var(
                $ip,
                FILTER_VALIDATE_IP,
                FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE
            ) === false
        ) {
            return back()
                ->withErrors(['url' => 'Gunakan URL publik yang valid.'])
                ->withInput();
        }

        MonitoredEndpoint::create($validated);

        return redirect()
            ->route('endpoints.index')
            ->with('success', 'Endpoint berhasil ditambahkan.');
    }

    public function destroy(
        MonitoredEndpoint $endpoint
    ): RedirectResponse {
        $endpoint->delete();

        return redirect()
            ->route('endpoints.index')
            ->with('success', 'Endpoint berhasil dihapus.');
    }
}
