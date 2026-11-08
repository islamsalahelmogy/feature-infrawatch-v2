<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AdministratorController extends Controller
{
    public function index(Request $request): View
    {
        $search = $request->string('search')->trim()->toString();
        $administrators = User::query()->whereHas('roles', fn ($query) => $query->where('slug', 'administrator'))
            ->when($search !== '', fn ($query) => $query->where(fn ($users) => $users->where('name', 'like', "%{$search}%")->orWhere('email', 'like', "%{$search}%")))
            ->orderBy('name')->paginate(25)->withQueryString();

        return view('administrators.index', compact('administrators', 'search'));
    }

    public function create(): View
    {
        return view('administrators.form', ['administrator' => new User(['is_active' => true])]);
    }

    public function store(Request $request): RedirectResponse
    {
        $administrator = DB::transaction(function () use ($request): User {
            $administrator = User::create($this->validated($request, passwordRequired: true));
            $administrator->roles()->attach($this->administratorRole()->id);
            $this->audit($request, 'administrator.created', $administrator);

            return $administrator;
        });

        return redirect()->route('administrators.index')->with('status', "Administrator {$administrator->name} added.");
    }

    public function edit(User $administrator): View
    {
        $this->ensureAdministrator($administrator);

        return view('administrators.form', compact('administrator'));
    }

    public function update(Request $request, User $administrator): RedirectResponse
    {
        $this->ensureAdministrator($administrator);
        $data = $this->validated($request, $administrator);

        if ($request->user()->is($administrator) && ! $data['is_active']) {
            return back()->withErrors(['is_active' => 'You cannot deactivate your own account.'])->withInput();
        }

        if ($administrator->is_active && ! $data['is_active'] && $this->activeAdministratorCount() <= 1) {
            return back()->withErrors(['is_active' => 'At least one active administrator is required.'])->withInput();
        }

        DB::transaction(function () use ($request, $administrator, $data): void {
            $administrator->update($data);
            $this->audit($request, 'administrator.updated', $administrator);
        });

        return redirect()->route('administrators.index')->with('status', "Administrator {$administrator->name} updated.");
    }

    public function destroy(Request $request, User $administrator): RedirectResponse
    {
        $this->ensureAdministrator($administrator);

        if ($request->user()->is($administrator)) {
            return back()->withErrors(['administrator' => 'You cannot delete your own account.']);
        }

        if ($administrator->is_active && $this->activeAdministratorCount() <= 1) {
            return back()->withErrors(['administrator' => 'At least one active administrator is required.']);
        }

        DB::transaction(function () use ($request, $administrator): void {
            $this->audit($request, 'administrator.deleted', $administrator);
            $administrator->delete();
        });

        return redirect()->route('administrators.index')->with('status', 'Administrator removed.');
    }

    private function validated(Request $request, ?User $administrator = null, bool $passwordRequired = false): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users')->ignore($administrator)],
            'password' => $passwordRequired ? ['required', 'string', 'min:12', 'confirmed'] : ['nullable', 'string', 'min:12', 'confirmed'],
            'is_active' => ['required', 'boolean'],
        ]);

        if (! $passwordRequired && blank($data['password'])) {
            unset($data['password']);
        }

        return $data;
    }

    private function administratorRole(): Role
    {
        return Role::where('slug', 'administrator')->firstOrFail();
    }

    private function activeAdministratorCount(): int
    {
        return User::where('is_active', true)->whereHas('roles', fn ($query) => $query->where('slug', 'administrator'))->count();
    }

    private function ensureAdministrator(User $user): void
    {
        abort_unless($user->hasAnyRole(['administrator']), 404);
    }

    private function audit(Request $request, string $event, User $administrator): void
    {
        DB::table('audit_logs')->insert(['user_id' => $request->user()->id, 'event' => $event, 'auditable_type' => User::class, 'auditable_id' => $administrator->id, 'ip_address' => $request->ip(), 'context' => json_encode(['email' => $administrator->email]), 'created_at' => now(), 'updated_at' => now()]);
    }
}
