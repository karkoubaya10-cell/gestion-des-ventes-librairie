<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\User;
use App\Models\Vente;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class RoleAndSaleSetupTest extends TestCase
{
    use RefreshDatabase;

    public function test_demo_users_can_log_in_and_have_the_expected_roles(): void
    {
        $this->seed();

        $admin = User::where('email', 'admin@test.com')->firstOrFail();
        $cashier = User::where('email', 'caissier@test.com')->firstOrFail();

        $this->assertSame('admin', $admin->role);
        $this->assertSame('caissier', $cashier->role);
        $this->assertTrue(Hash::check('Admin123', $admin->password));
        $this->assertTrue(Hash::check('Caissier123', $cashier->password));

        $this->post('/connexion', ['email' => 'caissier@test.com', 'password' => 'Caissier123'])
            ->assertRedirect(route('sales.index', ['page' => 'vente']));
    }

    public function test_cashier_can_checkout_and_is_blocked_from_product_management(): void
    {
        $this->seed();
        $cashier = User::where('email', 'caissier@test.com')->firstOrFail();
        $product = Product::firstOrFail();

        $response = $this->actingAs($cashier)->withSession(['cart' => [$product->id => 2]])
            ->post(route('sales.checkout'))
            ->assertRedirect();

        $this->assertDatabaseHas('ventes', ['user_id' => $cashier->id, 'total' => $product->prix * 2]);
        $sale = Vente::latest('id')->firstOrFail();
        $response->assertRedirect(route('sales.show', $sale));
        $this->assertDatabaseHas('vente_details', [
            'vente_id' => $sale->id,
            'produit_id' => $product->id,
            'quantite' => 2,
        ]);
        $this->actingAs($cashier)->post(route('catalog.products.store'), [
            'nom' => 'Interdit', 'code_barres' => '99999', 'prix' => 5,
        ])->assertForbidden();
    }

    public function test_product_search_matches_name_or_barcode_and_keeps_the_search_text(): void
    {
        $this->seed();
        $admin = User::where('email', 'admin@test.com')->firstOrFail();

        $this->actingAs($admin)
            ->get(route('sales.index', ['page' => 'produits', 'search' => 'Roman']))
            ->assertOk()
            ->assertSee('Roman')
            ->assertDontSee('Crayon HB')
            ->assertSee('value="Roman"', false);

        $this->actingAs($admin)
            ->get(route('sales.index', ['page' => 'produits', 'search' => '611000000002']))
            ->assertOk()
            ->assertSee('Crayon HB')
            ->assertDontSee('Roman')
            ->assertSee('value="611000000002"', false);

        $this->actingAs($admin)
            ->get(route('sales.index', ['page' => 'produits', 'search' => 'inexistant']))
            ->assertOk()
            ->assertSee('Aucun produit trouvé')
            ->assertSee('value="inexistant"', false)
            ->assertDontSee('Crayon HB');
    }
}
