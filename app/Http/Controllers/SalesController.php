<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Vente;
use App\Models\VenteDetail;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class SalesController extends Controller
{
    public function index(Request $request): View
    {
        $page = $request->query('page', $request->user()->role === 'caissier' ? 'vente' : 'dashboard');
        if ($request->user()->role === 'caissier' && ! in_array($page, ['vente', 'historique'], true)) {
            return redirect()->route('sales.index', ['page' => 'vente']);
        }

        $cart = $request->session()->get('cart', []);
        $productsInCart = Product::whereIn('id', array_keys($cart))->get()->keyBy('id');
        $search = trim((string) $request->query('search', ''));
        $productsQuery = Product::query();

        if ($page === 'produits' && $search !== '') {
            $productsQuery->where(fn ($query) => $query
                ->where('nom', 'like', '%'.$search.'%')
                ->orWhere('code_barres', 'like', '%'.$search.'%'));
        }

        return view('dashboard', [
            'page' => $page,
            'products' => $productsQuery->orderBy('nom')->get(),
            'cart' => $cart,
            'productsInCart' => $productsInCart,
            'history' => Vente::with('user')->latest('date_vente')->get(),
            'productCount' => Product::count(),
            'saleCount' => Vente::count(),
            'salesTotal' => Vente::sum('total'),
            'todaySales' => Vente::whereDate('date_vente', now()->toDateString())->count(),
            'recentSales' => Vente::with('user')->latest('date_vente')->take(5)->get(),
            'search' => $search,
        ]);
    }

    public function storeCatalogProduct(Request $request): RedirectResponse
    {
        $data = $request->validate(['nom' => ['required', 'string', 'max:255'], 'code_barres' => ['required', 'string', 'max:255', 'unique:produits,code_barres'], 'prix' => ['required', 'numeric', 'min:0.01', 'max:99999999.99']]);
        Product::create($data);
        return redirect()->route('sales.index', ['page' => 'produits'])->with('success', 'Produit ajoutÃ©.');
    }

    public function updateCatalogProduct(Request $request, Product $product): RedirectResponse
    {
        $data = $request->validate(['nom' => ['required', 'string', 'max:255'], 'code_barres' => ['required', 'string', 'max:255', Rule::unique('produits', 'code_barres')->ignore($product->id)], 'prix' => ['required', 'numeric', 'min:0.01', 'max:99999999.99']]);
        $product->update($data);
        return redirect()->route('sales.index', ['page' => 'produits'])->with('success', 'Produit modifiÃ©.');
    }

    public function deleteCatalogProduct(Product $product): RedirectResponse
    {
        if ($product->venteDetails()->exists()) {
            return redirect()->route('sales.index', ['page' => 'produits'])->withErrors(['produit' => 'Ce produit est liÃ© Ã  une vente et ne peut pas Ãªtre supprimÃ©.']);
        }
        $product->delete();
        return redirect()->route('sales.index', ['page' => 'produits'])->with('success', 'Produit supprimÃ©.');
    }

    public function scan(Request $request): RedirectResponse
    {
        $data = $request->validate(['code_barres' => ['required', 'string', 'max:255']]);
        $product = Product::where('code_barres', $data['code_barres'])->first();

        if (! $product) {
            return back()->with('barcode_non_trouve', $data['code_barres']);
        }

        $cart = $request->session()->get('cart', []);
        $cart[$product->id] = ($cart[$product->id] ?? 0) + 1;
        $request->session()->put('cart', $cart);

        return back()->with('success', $product->nom.' ajoutÃ© au panier.');
    }

    public function storeProduct(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'nom' => ['required', 'string', 'max:255'],
            'code_barres' => ['required', 'string', 'max:255', 'unique:produits,code_barres'],
            'prix' => ['required', 'numeric', 'min:0.01', 'max:99999999.99'],
        ], [
            'code_barres.unique' => 'Ce code-barres existe dÃ©jÃ . Cherchez le produit ou choisissez un autre code.',
            'nom.required' => 'Le nom du produit est obligatoire.',
            'prix.required' => 'Le prix est obligatoire.',
            'prix.min' => 'Le prix doit Ãªtre supÃ©rieur Ã  zÃ©ro.',
        ]);

        $product = Product::create($data);
        $cart = $request->session()->get('cart', []);
        $cart[$product->id] = ($cart[$product->id] ?? 0) + 1;
        $request->session()->put('cart', $cart);

        return redirect('/')->with('success', $product->nom.' a Ã©tÃ© crÃ©Ã© et ajoutÃ© au panier.');
    }

    public function updateCart(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'produit_id' => ['required', 'integer', 'exists:produits,id'],
            'action' => ['required', Rule::in(['plus', 'moins', 'supprimer'])],
        ]);
        $cart = $request->session()->get('cart', []);
        $id = (int) $data['produit_id'];
        $quantity = $cart[$id] ?? 0;

        if ($data['action'] === 'supprimer' || ($data['action'] === 'moins' && $quantity <= 1)) {
            unset($cart[$id]);
        } elseif ($data['action'] === 'plus') {
            $cart[$id] = $quantity + 1;
        } elseif ($quantity > 1) {
            $cart[$id] = $quantity - 1;
        }

        $request->session()->put('cart', $cart);

        return back();
    }

    public function clearCart(Request $request): RedirectResponse
    {
        $request->session()->forget('cart');

        return back();
    }

    public function checkout(Request $request): RedirectResponse
    {
        $cart = $request->session()->get('cart', []);
        if (empty($cart)) {
            return back()->withErrors(['panier' => 'Ajoutez au moins un produit avant de valider la vente.']);
        }

        $sale = DB::transaction(function () use ($cart) {
            $products = Product::whereIn('id', array_keys($cart))->get()->keyBy('id');
            if ($products->count() !== count($cart)) {
                throw new \RuntimeException('Un produit du panier nâ€™existe plus.');
            }

            $total = 0;
            foreach ($cart as $id => $quantity) {
                $total += round((float) $products[$id]->prix * (int) $quantity, 2);
            }

            // La vente est attribuée à l'utilisateur connecté.
            $employee = auth()->user();
            $vente = Vente::create([
                'numero_vente' => 'TEMP-'.Str::uuid(),
                'date_vente' => now(),
                'total' => $total,
                'user_id' => $employee->id,
            ]);
            $vente->update(['numero_vente' => 'V-'.str_pad((string) $vente->id, 6, '0', STR_PAD_LEFT)]);

            foreach ($cart as $id => $quantity) {
                $price = $products[$id]->prix;
                VenteDetail::create([
                    'vente_id' => $vente->id,
                    'produit_id' => $id,
                    'quantite' => $quantity,
                    'prix' => $price,
                    'sous_total' => round((float) $price * (int) $quantity, 2),
                ]);
            }

            return $vente;
        });

        $request->session()->forget('cart');

        return redirect()->route('sales.show', $sale)->with('success', 'Vente validÃ©e avec succÃ¨s.');
    }

    public function show(Vente $vente): View
    {
        $vente->load(['user', 'details.product']);

        return view('dashboard', [
            'page' => 'ticket',
            'vente' => $vente,
        ]);
    }
}

