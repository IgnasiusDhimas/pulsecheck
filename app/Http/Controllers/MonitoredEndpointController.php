<?php

namespace App\Http\Controllers;

use App\Models\MonitoredEndpoint;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use App\Services\EndpointChecker;
use App\Models\CheckResult;

class MonitoredEndpointController extends Controller
{
    public function index(): View
    {
        $endpoints = MonitoredEndpoint::with([
            'checkResults' => function ($query) {
                $query->latest('checked_at')->limit(1);
            }
        ])->latest()->get();

        $totalChecks = CheckResult::count();

        $upChecks = CheckResult::where('status', 'up')->count();

        $downChecks = CheckResult::where('status', 'down')->count();

        $avgResponseTime = CheckResult::whereNotNull('response_time_ms')
            ->avg('response_time_ms');

        return view('endpoints.index', compact(
            'endpoints',
            'totalChecks',
            'upChecks',
            'downChecks',
            'avgResponseTime'
        ));
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

    public function check(
        MonitoredEndpoint $endpoint,
        EndpointChecker $checker
    ): RedirectResponse {
        $result = $checker->check($endpoint);

        $message = $result->status === 'up'
            ? 'Pemeriksaan berhasil. Endpoint merespons.'
            : 'Pemeriksaan selesai. Endpoint mengalami masalah.';

        return redirect()
            ->route('endpoints.index')
            ->with('success', $message);
    }
    public function show(
        MonitoredEndpoint $endpoint
    ): View {
        $checks = $endpoint->checkResults()
            ->latest('checked_at')
            ->paginate(10);

        return view('endpoints.show', compact(
            'endpoint',
            'checks'
        ));
    }
}
