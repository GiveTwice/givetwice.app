<?php

use App\Models\Gift;
use App\Models\GiftList;
use App\Models\User;
use Illuminate\Support\Facades\Queue;

describe('Admin dashboard', function () {
    it('allows admin to access the dashboard', function () {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->get('/admin')
            ->assertSuccessful()
            ->assertSee('Dashboard');
    });

    it('returns 404 for non-admin users', function () {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get('/admin')
            ->assertNotFound();
    });

    it('redirects guests to login', function () {
        $this->get('/admin')
            ->assertRedirect();
    });

    it('contains chart data', function () {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->get('/admin')
            ->assertSuccessful()
            ->assertSee('admin-chart-data', false);
    });

    it('displays stat counts', function () {
        $admin = User::factory()->admin()->create();

        Queue::fake();
        User::factory()->count(3)->create();
        Gift::factory()->count(2)->create();

        $this->actingAs($admin)
            ->get('/admin')
            ->assertSuccessful()
            ->assertSee('Total users')
            ->assertSee('Total gifts');
    });
});

describe('Admin user management', function () {
    it('lists users with search', function () {
        $admin = User::factory()->admin()->create();
        $user = User::factory()->create(['name' => 'Findable User']);

        $this->actingAs($admin)
            ->get('/admin/users?search=Findable')
            ->assertSuccessful()
            ->assertSee('Findable User');
    });

    it('filters users by verification status', function () {
        $admin = User::factory()->admin()->create();
        User::factory()->unverified()->create(['name' => 'Unverified Joe']);

        $this->actingAs($admin)
            ->get('/admin/users?filter=unverified')
            ->assertSuccessful()
            ->assertSee('Unverified Joe');
    });

    it('shows user detail page', function () {
        $admin = User::factory()->admin()->create();
        $user = User::factory()->create(['name' => 'Detail User']);

        $this->actingAs($admin)
            ->get("/admin/users/{$user->id}")
            ->assertSuccessful()
            ->assertSee('Detail User');
    });

    it('prevents admin from toggling own admin status', function () {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->post("/admin/users/{$admin->id}/toggle-admin")
            ->assertRedirect();

        expect($admin->fresh()->is_admin)->toBeTrue();
    });

    it('toggles admin status for other users', function () {
        $admin = User::factory()->admin()->create();
        $user = User::factory()->create();

        expect($user->is_admin)->toBeFalse();

        $this->actingAs($admin)
            ->post("/admin/users/{$user->id}/toggle-admin")
            ->assertRedirect();

        expect($user->fresh()->is_admin)->toBeTrue();
    });
});

describe('Admin gift management', function () {
    it('lists gifts', function () {
        $admin = User::factory()->admin()->create();

        Queue::fake();
        Gift::factory()->create(['title' => 'Test Gift Title']);

        $this->actingAs($admin)
            ->get('/admin/gifts')
            ->assertSuccessful()
            ->assertSee('Test Gift Title');
    });

    it('filters gifts by status', function () {
        $admin = User::factory()->admin()->create();

        Queue::fake();
        Gift::factory()->failed()->create(['title' => 'Failed Gift']);
        Gift::factory()->create(['title' => 'Good Gift']);

        $this->actingAs($admin)
            ->get('/admin/gifts?status=failed')
            ->assertSuccessful()
            ->assertSee('Failed Gift')
            ->assertDontSee('Good Gift');
    });

    it('shows gift detail page', function () {
        $admin = User::factory()->admin()->create();

        Queue::fake();
        $gift = Gift::factory()->create(['title' => 'Detailed Gift']);

        $this->actingAs($admin)
            ->get("/admin/gifts/{$gift->id}")
            ->assertSuccessful()
            ->assertSee('Detailed Gift');
    });

    it('allows admin to retry a failed gift fetch for any user', function () {
        $admin = User::factory()->admin()->create();

        Queue::fake();
        $gift = Gift::factory()->failed()->create();

        $this->actingAs($admin)
            ->post("/admin/gifts/{$gift->id}/refresh")
            ->assertRedirect();

        expect($gift->fresh()->fetch_status)->toBe('pending');
    });

    it('prevents non-admin from refreshing gifts via admin route', function () {
        $user = User::factory()->create();

        Queue::fake();
        $gift = Gift::factory()->failed()->create();

        $this->actingAs($user)
            ->post("/admin/gifts/{$gift->id}/refresh")
            ->assertNotFound();

        expect($gift->fresh()->fetch_status)->toBe('failed');
    });

    it('searches gifts by title', function () {
        $admin = User::factory()->admin()->create();

        Queue::fake();
        Gift::factory()->create(['title' => 'Unique Searchable']);
        Gift::factory()->create(['title' => 'Other Item']);

        $this->actingAs($admin)
            ->get('/admin/gifts?search=Unique')
            ->assertSuccessful()
            ->assertSee('Unique Searchable')
            ->assertDontSee('Other Item');
    });
});

describe('Admin list management', function () {
    it('lists all gift lists', function () {
        $admin = User::factory()->admin()->create();
        GiftList::factory()->create(['name' => 'Christmas Wishlist']);

        $this->actingAs($admin)
            ->get('/admin/lists')
            ->assertSuccessful()
            ->assertSee('Christmas Wishlist');
    });

    it('searches lists by name', function () {
        $admin = User::factory()->admin()->create();
        GiftList::factory()->create(['name' => 'Birthday List']);
        GiftList::factory()->create(['name' => 'Holiday List']);

        $this->actingAs($admin)
            ->get('/admin/lists?search=Birthday')
            ->assertSuccessful()
            ->assertSee('Birthday List')
            ->assertDontSee('Holiday List');
    });
});

describe('Admin health checks', function () {
    it('displays health check page', function () {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->get('/admin/health')
            ->assertSuccessful()
            ->assertSee('System health');
    });
});

describe('Impersonation', function () {
    it('allows admin to impersonate a non-admin user', function () {
        $admin = User::factory()->admin()->create();
        $user = User::factory()->create();

        $this->actingAs($admin)
            ->get(route('impersonate', $user->id))
            ->assertRedirect('/dashboard');

        expect(auth()->id())->toBe($user->id);
    });

    it('prevents admin from impersonating another admin', function () {
        $admin = User::factory()->admin()->create();
        $otherAdmin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->get(route('impersonate', $otherAdmin->id))
            ->assertRedirect();

        expect(auth()->id())->toBe($admin->id);
    });

    it('allows leaving impersonation', function () {
        $admin = User::factory()->admin()->create();
        $user = User::factory()->create();

        $this->actingAs($admin)
            ->get(route('impersonate', $user->id));

        $this->get(route('impersonate.leave'))
            ->assertRedirect('/admin/users');

        expect(auth()->id())->toBe($admin->id);
    });

    it('shows impersonate button only for non-admin non-self users', function () {
        $admin = User::factory()->admin()->create();
        $user = User::factory()->create();

        $this->actingAs($admin)
            ->get("/admin/users/{$user->id}")
            ->assertSuccessful()
            ->assertSee('Impersonate');
    });

    it('does not show impersonate button for admin users', function () {
        $admin = User::factory()->admin()->create();
        $otherAdmin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->get("/admin/users/{$otherAdmin->id}")
            ->assertSuccessful()
            ->assertDontSee('Impersonate');
    });

    it('does not show impersonate button for self', function () {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->get("/admin/users/{$admin->id}")
            ->assertSuccessful()
            ->assertDontSee('Impersonate');
    });

    it('allows admin impersonation via admin route with redirect', function () {
        $admin = User::factory()->admin()->create();
        $user = User::factory()->create(['locale_preference' => 'nl']);

        Queue::fake();
        $gift = Gift::factory()->for($user)->create();

        $this->actingAs($admin)
            ->post(route('admin.impersonate', $user), [
                'redirect_to' => "/nl/gifts/{$gift->id}/edit",
            ])
            ->assertRedirect("/nl/gifts/{$gift->id}/edit");

        expect(auth()->id())->toBe($user->id);
    });

    it('prevents admin impersonation of another admin via admin route', function () {
        $admin = User::factory()->admin()->create();
        $otherAdmin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->post(route('admin.impersonate', $otherAdmin))
            ->assertRedirect();

        expect(auth()->id())->toBe($admin->id);
    });

    it('blocks open redirect via admin impersonate route', function () {
        $admin = User::factory()->admin()->create();
        $user = User::factory()->create();

        $this->actingAs($admin)
            ->post(route('admin.impersonate', $user), [
                'redirect_to' => '//evil.com',
            ])
            ->assertRedirect('/en/dashboard');
    });

    it('shows impersonation banner when impersonating', function () {
        $admin = User::factory()->admin()->create();
        $user = User::factory()->create(['name' => 'Impersonated Person']);

        $this->actingAs($admin)
            ->get(route('impersonate', $user->id));

        $this->get('/en')
            ->assertSee('You are impersonating')
            ->assertSee('Impersonated Person')
            ->assertSee('Stop impersonating');
    });
});
