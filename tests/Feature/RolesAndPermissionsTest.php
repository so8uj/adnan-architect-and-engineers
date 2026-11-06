<?php

namespace Tests\Feature;

use App\Models\Department;
use App\Models\Page;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\SiteSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RolesAndPermissionsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(SiteSeeder::class);
    }

    private function staff(array $permissions, string $email = 'staff@adnan.test'): User
    {
        $role = new Role(['name' => 'Role '.$email, 'permissions' => $permissions]);
        $role->slug = str($email)->slug()->toString();
        $role->save();
        $u = User::create(['name' => 'Staff', 'email' => $email, 'password' => 'password123']);
        $u->forceFill(['role_id' => $role->id])->save();

        return $u;
    }

    public function test_the_developer_is_super_and_sees_everything(): void
    {
        $dev = User::where('is_developer', true)->firstOrFail();
        $this->assertTrue($dev->isSuper());
        $this->actingAs($dev)->get('/admin/roles')->assertOk()->assertSee('Super Admin');
        $this->get('/admin/c/services')->assertOk();
    }

    public function test_a_user_without_a_role_sees_only_dashboard_and_profile(): void
    {
        $u = User::create(['name' => 'Nobody', 'email' => 'n@adnan.test', 'password' => 'password123']);
        $this->actingAs($u)->get('/admin')->assertOk()->assertDontSee('Website Management');
        $this->get('/admin/profile')->assertOk();
        foreach (['/admin/c/services', '/admin/settings/header', '/admin/messages', '/admin/users', '/admin/roles', '/admin/departments', '/admin/pages'] as $url) {
            $this->get($url)->assertForbidden();
        }
    }

    public function test_view_permission_opens_the_screen_but_does_not_allow_changes(): void
    {
        $this->actingAs($this->staff(['website.home.view']));
        $this->get('/admin/c/services')->assertOk();
        $this->get('/admin/c/services/create')->assertForbidden();
        $this->post('/admin/c/services', ['title' => ['bn' => 'x', 'en' => 'x']])->assertForbidden();
        $this->get('/admin/settings/sections')->assertOk();
        $this->put('/admin/settings/sections', [])->assertForbidden();
        // other pages are closed
        $this->get('/admin/c/projects')->assertForbidden();
        $this->get('/admin/settings/footer')->assertForbidden();
    }

    public function test_manage_includes_view_and_the_menu_shows_only_allowed_pages(): void
    {
        $this->actingAs($this->staff(['website.works.manage', 'messages.view']));
        $html = $this->get('/admin/c/projects')->assertOk()->getContent();
        $this->assertStringContainsString('Our Work', $html);
        $this->assertStringNotContainsString('Home Page', $html);
        $this->assertStringNotContainsString('User management', $html);
        $this->get('/admin/messages')->assertOk();
        $this->get('/admin/c/projects/create')->assertOk();
    }

    public function test_seo_tab_follows_the_page_permission(): void
    {
        $home = Page::where('key', 'home')->firstOrFail();
        $works = Page::where('key', 'portfolio')->firstOrFail();
        $this->actingAs($this->staff(['website.home.view']));
        $this->get("/admin/pages/{$home->id}")->assertOk();
        $this->get("/admin/pages/{$works->id}")->assertForbidden();
    }

    public function test_roles_and_departments_crud_and_assignment(): void
    {
        $this->actingAs(User::where('is_developer', true)->firstOrFail());

        $this->post('/admin/departments', ['name' => 'Design'])->assertRedirect();
        $dept = Department::firstOrFail();

        $this->post('/admin/roles', ['name' => 'Designer', 'permissions' => ['website.works.manage', 'bogus.permission']])->assertSessionHasErrors('permissions.1');
        $this->post('/admin/roles', ['name' => 'Designer', 'permissions' => ['website.works.manage']])->assertRedirect();
        $role = Role::where('slug', 'designer')->firstOrFail();
        $this->assertSame(['website.works.manage'], $role->permissions);

        $this->post('/admin/users', ['name' => 'Rahim', 'email' => 'rahim@adnan.test', 'password' => 'password123', 'password_confirmation' => 'password123',
            'department_id' => $dept->id, 'role_id' => $role->id])->assertRedirect();
        $rahim = User::where('email', 'rahim@adnan.test')->firstOrFail();
        $this->assertSame($role->id, $rahim->role_id);
        $this->assertSame($dept->id, $rahim->department_id);

        // in use -> can not delete the role; the protected role can never be changed or deleted
        $this->delete("/admin/roles/{$role->id}")->assertSessionHasErrors('role');
        $super = Role::superAdmin();
        $this->put("/admin/roles/{$super->id}", ['name' => 'x'])->assertForbidden();
        $this->delete("/admin/roles/{$super->id}")->assertSessionHasErrors('role');

        // deleting a department keeps its people
        $this->delete("/admin/departments/{$dept->id}")->assertRedirect();
        $this->assertNull($rahim->fresh()->department_id);
    }

    public function test_nobody_can_escalate(): void
    {
        $super = Role::superAdmin();
        $manager = $this->staff(['users.manage', 'roles.manage', 'website.works.manage'], 'm@adnan.test');
        $this->actingAs($manager);

        // can not hand out more than they hold, nor Super Admin
        $this->post('/admin/roles', ['name' => 'Big', 'permissions' => ['website.home.manage']])->assertForbidden();
        $this->post('/admin/users', ['name' => 'T', 'email' => 't@adnan.test', 'password' => 'password123', 'password_confirmation' => 'password123', 'role_id' => $super->id])
            ->assertSessionHasErrors('role_id');

        // can not change their own role
        $small = new Role(['name' => 'Small', 'permissions' => ['website.works.view']]);
        $small->slug = 'small';
        $small->save();
        $this->put("/admin/users/{$manager->id}", ['name' => 'M', 'email' => 'm@adnan.test', 'role_id' => $small->id]);
        $this->assertNotSame($small->id, $manager->fresh()->role_id);
    }

    public function test_the_developer_account_always_passes(): void
    {
        $dev = User::where('is_developer', true)->firstOrFail();
        $dev->forceFill(['role_id' => null])->save();
        $this->assertTrue($dev->fresh()->hasPermission('roles.manage'));
    }
}
