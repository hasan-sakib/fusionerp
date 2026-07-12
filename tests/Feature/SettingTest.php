<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Tenant;
use App\Models\User;
use App\Services\SettingService;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SettingTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $manager;
    private User $employee;
    private Tenant $tenant;
    private SettingService $settingService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesAndPermissionsSeeder::class);

        $this->tenant = Tenant::factory()->create(['slug' => 'test', 'status' => 'active']);
        app()->instance('tenant', $this->tenant);

        $this->admin = User::factory()->create();
        $this->admin->assignRole('admin');

        $this->manager = User::factory()->create();
        $this->manager->assignRole('manager');

        $this->employee = User::factory()->create();
        $this->employee->assignRole('employee');

        $this->settingService = $this->app->make(SettingService::class);
    }

    // ── Access control ─────────────────────────────────────────────────────────

    public function test_guest_cannot_access_settings(): void
    {
        $this->get(route('settings.index'))->assertRedirect(route('login'));
    }

    public function test_admin_can_view_settings(): void
    {
        $this->actingAs($this->admin)->get(route('settings.index'))->assertOk();
    }

    public function test_employee_cannot_view_settings(): void
    {
        $this->actingAs($this->employee)->get(route('settings.index'))->assertForbidden();
    }

    public function test_manager_cannot_view_settings(): void
    {
        $this->actingAs($this->manager)->get(route('settings.index'))->assertForbidden();
    }

    public function test_employee_cannot_update_settings(): void
    {
        $this->actingAs($this->employee)
            ->patch(route('settings.update'), $this->validPayload())
            ->assertForbidden();
    }

    public function test_manager_cannot_update_settings(): void
    {
        $this->actingAs($this->manager)
            ->patch(route('settings.update'), $this->validPayload())
            ->assertForbidden();
    }

    // ── Rendering ──────────────────────────────────────────────────────────────

    public function test_settings_page_renders_all_tabs(): void
    {
        $this->actingAs($this->admin)
            ->get(route('settings.index'))
            ->assertOk()
            ->assertSee('General')
            ->assertSee('Orders')
            ->assertSee('Preferences');
    }

    public function test_settings_page_shows_current_values(): void
    {
        $this->tenant->update(['settings' => ['company_name' => 'Acme Corp', 'currency_symbol' => '€']]);

        $this->actingAs($this->admin)
            ->get(route('settings.index'))
            ->assertOk()
            ->assertSee('Acme Corp')
            ->assertSee('€');
    }

    // ── Save & persist ─────────────────────────────────────────────────────────

    public function test_admin_can_save_settings(): void
    {
        $this->actingAs($this->admin)
            ->patch(route('settings.update'), $this->validPayload(['company_name' => 'New Corp']))
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertSame('New Corp', $this->settingService->get('company_name'));
    }

    public function test_settings_persist_to_tenant(): void
    {
        $this->actingAs($this->admin)
            ->patch(route('settings.update'), $this->validPayload([
                'company_name'     => 'Persist Corp',
                'currency'         => 'EUR',
                'currency_symbol'  => '€',
                'default_tax_rate' => '18',
            ]));

        $stored = $this->tenant->fresh()->settings;

        $this->assertSame('Persist Corp', $stored['company_name']);
        $this->assertSame('EUR', $stored['currency']);
        $this->assertSame('€', $stored['currency_symbol']);
        $this->assertSame('18', (string) $stored['default_tax_rate']);
    }

    public function test_partial_update_does_not_wipe_existing_settings(): void
    {
        $this->tenant->update(['settings' => ['company_name' => 'Original']]);

        $this->actingAs($this->admin)
            ->patch(route('settings.update'), $this->validPayload(['currency' => 'GBP']));

        $stored = $this->tenant->fresh()->settings;

        $this->assertSame('GBP', $stored['currency']);
    }

    // ── Default values ─────────────────────────────────────────────────────────

    public function test_defaults_returned_when_settings_not_set(): void
    {
        $settings = $this->settingService->all();

        $this->assertSame('UTC', $settings['timezone']);
        $this->assertSame('USD', $settings['currency']);
        $this->assertSame('$', $settings['currency_symbol']);
        $this->assertSame('Y-m-d', $settings['date_format']);
        $this->assertSame(15, $settings['items_per_page']);
        $this->assertSame('ORD-', $settings['order_prefix']);
        $this->assertSame(0, $settings['default_tax_rate']);
        $this->assertSame(10, $settings['low_stock_threshold']);
    }

    public function test_get_returns_default_for_missing_key(): void
    {
        $value = $this->settingService->get('nonexistent_key', 'fallback');
        $this->assertSame('fallback', $value);
    }

    // ── Validation ─────────────────────────────────────────────────────────────

    public function test_company_name_is_required(): void
    {
        $this->actingAs($this->admin)
            ->patch(route('settings.update'), $this->validPayload(['company_name' => '']))
            ->assertSessionHasErrors('company_name');
    }

    public function test_invalid_email_is_rejected(): void
    {
        $this->actingAs($this->admin)
            ->patch(route('settings.update'), $this->validPayload(['company_email' => 'not-an-email']))
            ->assertSessionHasErrors('company_email');
    }

    public function test_tax_rate_must_be_between_0_and_100(): void
    {
        $this->actingAs($this->admin)
            ->patch(route('settings.update'), $this->validPayload(['default_tax_rate' => 150]))
            ->assertSessionHasErrors('default_tax_rate');
    }

    public function test_invalid_timezone_is_rejected(): void
    {
        $this->actingAs($this->admin)
            ->patch(route('settings.update'), $this->validPayload(['timezone' => 'Not/ATimezone']))
            ->assertSessionHasErrors('timezone');
    }

    public function test_invalid_date_format_is_rejected(): void
    {
        $this->actingAs($this->admin)
            ->patch(route('settings.update'), $this->validPayload(['date_format' => 'invalid']))
            ->assertSessionHasErrors('date_format');
    }

    public function test_invalid_items_per_page_is_rejected(): void
    {
        $this->actingAs($this->admin)
            ->patch(route('settings.update'), $this->validPayload(['items_per_page' => 99]))
            ->assertSessionHasErrors('items_per_page');
    }

    public function test_negative_low_stock_threshold_is_rejected(): void
    {
        $this->actingAs($this->admin)
            ->patch(route('settings.update'), $this->validPayload(['low_stock_threshold' => -1]))
            ->assertSessionHasErrors('low_stock_threshold');
    }

    // ── Helper ─────────────────────────────────────────────────────────────────

    private function validPayload(array $overrides = []): array
    {
        return array_merge([
            'company_name'        => 'Test Company',
            'company_email'       => 'test@company.com',
            'company_phone'       => '+1-555-000-0000',
            'company_address'     => '123 Main St',
            'company_website'     => 'https://testcompany.com',
            'timezone'            => 'UTC',
            'currency'            => 'USD',
            'currency_symbol'     => '$',
            'date_format'         => 'Y-m-d',
            'items_per_page'      => 15,
            'order_prefix'        => 'ORD-',
            'default_tax_rate'    => 0,
            'low_stock_threshold' => 10,
            'fiscal_year_start'   => '01',
        ], $overrides);
    }
}
