<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Produk;
use App\Services\OrderService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use RealRashid\SweetAlert\Facades\Alert;

class ClientCartController extends Controller
{
    protected OrderService $orderService;

    public function __construct(OrderService $orderService)
    {
        $this->orderService = $orderService;
    }

    public function add(Request $request, $id)
    {
        $this->addProductToCard($id, $request->variant_id);
        Alert::success('Hore!', 'Produk berhasil ditambahkan di keranjang');

        return redirect()->back()->with('success', 'Product added to cart.');
    }

    public function addProductToCard($id, $variantId)
    {
        $produk = Produk::with(['varianProduk', 'photos'])->findOrFail($id);
        $selectedVariant = $produk->varianProduk->find($variantId);

        if (!$selectedVariant) {
            return redirect()
                ->back()
                ->with('error', 'Invalid variant selected.');
        }

        $cart = Session::get('cart', []);
        $cartKey = $id . '_' . $variantId;

        if (isset($cart[$cartKey])) {
            if ($cart[$cartKey]['quantity'] < min(99, $selectedVariant->stok)) {
                $cart[$cartKey]['quantity']++;
            } else {
                return redirect()
                    ->back()
                    ->with('error', 'Maximum stock reached for this variant.');
            }
        } else {
            $cart[$cartKey] = [
                'product_id' => $id,
                'name' => $produk->nama_produk,
                'photo_url' => $produk->photos->first()->url_photo ?? null,
                'price' => $selectedVariant->harga,
                'quantity' => 1,
                'variant' => $selectedVariant->nama_varian,
                'variant_id' => $selectedVariant->id,
                'ukuran' => $selectedVariant->ukurann,
                'max_stock' => $selectedVariant->stok,
            ];
        }

        Session::put('cart', $cart);
    }

    public function order(Request $request)
    {
        $search = $request->input('search');
        $order = [];

        if ($search) {
            $foundOrder = $this->orderService->getOrderById($search);

            if ($foundOrder) {
                $order = [$foundOrder];
            } else {
                Alert::error('Pesanan Tidak Ditemukan', "Tidak ada pesanan dengan ID: $search");
            }
        }

        return view('client.produk.transaction', compact('order', 'search'));
    }

    public function cart(Request $req)
    {
        if ($req->id && $req->variant_id) {
            $this->addProductToCard($req->id, $req->variant_id);
        }
        $cartItems = Session::get('cart', []);
        $updatedCartItems = [];

        foreach ($cartItems as $id => $item) {
            // Ensure all necessary keys are present in the item, with default values if missing
            $item = array_merge(
                [
                    'product_id' => null,
                    'variant_id' => null,
                    'name' => '',
                    'photo_url' => null,
                    'price' => 0,
                    'quantity' => 0,
                    'variant' => '',
                    'ukuran' => '',
                    'max_stock' => 99,
                ],
                $item
            );

            $product = Produk::with(['photos', 'varianProduk'])->find(
                $item['product_id']
            );
            if ($product) {
                $variant = $product->varianProduk->find($item['variant_id']);
                $updatedCartItems[$id] = [
                    'product_id' => $item['product_id'],
                    'variant_id' => $item['variant_id'],
                    'name' => $product->nama_produk,
                    'photo_url' => $product->photos->isNotEmpty()
                        ? $product->photos->first()->url_photo
                        : null,
                    'price' => $variant ? $variant->harga : $item['price'],
                    'quantity' => $item['quantity'],
                    'variant' => $variant
                        ? $variant->nama_varian
                        : $item['variant'],
                    'ukuran' => $variant
                        ? $variant->ukuran
                        : $item['ukuran'],
                    'max_stock' => $variant
                        ? $variant->stok
                        : $item['max_stock'],
                ];
            } else {
                // If the product no longer exists, we'll keep the original cart item
                $updatedCartItems[$id] = $item;
            }
        }

        // Update the cart in the session
        Session::put('cart', $updatedCartItems);

        $subtotal = array_reduce(
            $updatedCartItems,
            function ($sum, $item) {
                return $sum + $item['price'] * $item['quantity'];
            },
            0
        );

        // Tax / pajak logic
        // $taxRate = 0.2; // Pajak murahin
        $tax = 2500;

        // Discount logic
        $discount = $this->calculateDiscount($subtotal);

        $total = $subtotal + $tax - $discount;

        return view(
            'client.produk.cart',
            compact('updatedCartItems', 'subtotal', 'tax', 'discount', 'total')
        );
    }

    private function calculateDiscount($subtotal)
    {
        return 0;
    }

    public function update(Request $request, $id)
    {
        $cart = Session::get('cart', []);
        if (isset($cart[$id])) {
            $newQuantity = max(0, intval($request->quantity));
            if ($newQuantity == 0) {
                unset($cart[$id]);
            } else {
                $cart[$id]['quantity'] = $newQuantity;
            }
        }
        Session::put('cart', $cart);

        return redirect()->route('ecommerce.cart');
    }

    public function remove($id)
    {
        $cart = Session::get('cart', []);

        if (isset($cart[$id])) {
            unset($cart[$id]);
        }

        Session::put('cart', $cart);

        return redirect()->route('ecommerce.cart');
    }
}
