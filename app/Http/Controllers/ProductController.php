<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
    public function index(): View
    {
        $products = Product::latest()->paginate(10);
        return view('products.index', compact('products'));
    }

    public function create(): View
    {
        return view('products.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'image'         => 'required|image|mimes:jpeg,jpg,png|max:2048',
            'title'         => 'required|min:5',
            'description'   => 'required|min:10',
            'price'         => 'required|numeric',
            'stock'         => 'required|numeric'
        ]);

        if (!$request->hasFile('image')) {
            return back()->withErrors(['image' => 'File tidak ditemukan di request.']);
        }

        $file = $request->file('image');

        if (!$file->isValid()) {
            return back()->withErrors([
                'image' => 'Upload gagal. File tidak valid. Kode error: ' . $file->getError()
            ]);
        }

        $filename = time() . '-' . $file->getClientOriginalName();
        $destination = storage_path('app/public/products');

        if (!file_exists($destination)) {
            mkdir($destination, 0755, true);
        }

        $file->move($destination, $filename);

        Product::create([
            'image'         => $filename,
            'title'         => $request->title,
            'description'   => $request->description,
            'price'         => $request->price,
            'stock'         => $request->stock
        ]);

        return redirect()->route('products.index')->with(['success' => 'Data berhasil disimpan!']);
    }

    public function edit(Product $product): View
    {
        return view('products.edit', compact('product'));
    }

    public function update(Request $request, Product $product): RedirectResponse
    {
        $request->validate([
            'image'         => 'nullable|image|mimes:jpeg,jpg,png|max:2048',
            'title'         => 'required|min:5',
            'description'   => 'required|min:10',
            'price'         => 'required|numeric',
            'stock'         => 'required|numeric'
        ]);

        $filename = $product->image;

        if ($request->hasFile('image')) {
            $file = $request->file('image');

            if ($file->isValid()) {
                // Hapus gambar lama secara manual
                $oldFilePath = storage_path('app/public/products/' . $product->image);
                if (file_exists($oldFilePath)) {
                    unlink($oldFilePath);
                }

                $filename = time() . '-' . $file->getClientOriginalName();
                $destination = storage_path('app/public/products');

                if (!file_exists($destination)) {
                    mkdir($destination, 0755, true);
                }

                $file->move($destination, $filename);
            } else {
                return back()->withErrors([
                    'image' => 'Upload gambar gagal. Kode error: ' . $file->getError()
                ]);
            }
        }

        $product->update([
            'image'         => $filename,
            'title'         => $request->title,
            'description'   => $request->description,
            'price'         => $request->price,
            'stock'         => $request->stock
        ]);

        return redirect()->route('products.index')->with(['success' => 'Data berhasil diperbarui!']);
    }

    public function destroy(Product $product): RedirectResponse
    {
        $filePath = storage_path('app/public/products/' . $product->image);

        if (file_exists($filePath)) {
            unlink($filePath);
        }

        $product->delete();

        return redirect()->route('products.index')->with(['success' => 'Data berhasil dihapus!']);
    }

/**
     * show
     *
     * @param  mixed $id
     * @return View
     */
    public function show(string $id): View
    {
        //get product by ID
        $product = Product::findOrFail($id);

        //render view with product
        return view('products.show', compact('product'));
    }


	
}
