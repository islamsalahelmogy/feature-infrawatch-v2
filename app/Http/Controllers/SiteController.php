<?php

namespace App\Http\Controllers;

use App\Models\Site;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class SiteController extends Controller
{
    public function index(): View
    {
        return view('sites.index', ['sites' => Site::withCount('devices')->orderBy('name')->paginate(20)]);
    }

    public function create(): View
    {
        return view('sites.form', ['site' => new Site]);
    }

    public function store(Request $request): RedirectResponse
    {
        $site = Site::create($this->validated($request));

        return redirect()->route('sites.index')->with('status', "Site {$site->name} created.");
    }

    public function edit(Site $site): View
    {
        return view('sites.form', compact('site'));
    }

    public function update(Request $request, Site $site): RedirectResponse
    {
        $site->update($this->validated($request, $site));

        return redirect()->route('sites.index')->with('status', "Site {$site->name} updated.");
    }

    public function destroy(Site $site): RedirectResponse
    {
        abort_if($site->devices()->exists(), 422, 'A site with devices cannot be deleted.');
        $site->delete();

        return redirect()->route('sites.index')->with('status', 'Site deleted.');
    }

    private function validated(Request $request, ?Site $site = null): array
    {
        return $request->validate(['name' => ['required', 'string', 'max:100'], 'code' => ['required', 'alpha_dash', 'max:30', Rule::unique('sites', 'code')->ignore($site)], 'address' => ['nullable', 'string', 'max:2000'], 'timezone' => ['required', 'timezone'], 'is_active' => ['required', 'boolean']]);
    }
}
