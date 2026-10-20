<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DeliveryMethod;
use Illuminate\Http\Request;

class DeliveryMethodController extends Controller
{
    public function index()
    {
        $methods = DeliveryMethod::orderBy('id')->get();

        return view('admin.delivery_methods.index', compact('methods'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'cost' => 'required|numeric|min:0',
            'is_active' => 'boolean',
        ]);

        DeliveryMethod::create([
            'name' => $request->name,
            'cost' => $request->cost,
            'is_active' => $request->has('is_active'),
        ]);

        return back()->with('success', 'Delivery Method created successfully.');
    }

    public function update(Request $request, DeliveryMethod $deliveryMethod)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'cost' => 'required|numeric|min:0',
            'is_active' => 'boolean',
        ]);

        $deliveryMethod->update([
            'name' => $request->name,
            'cost' => $request->cost,
            'is_active' => $request->has('is_active'),
        ]);

        return back()->with('success', 'Delivery Method updated successfully.');
    }

    public function destroy(DeliveryMethod $deliveryMethod)
    {
        $deliveryMethod->delete();

        return back()->with('success', 'Delivery Method deleted successfully.');
    }

    // API endpoint for frontend checkout
    public function getActiveMethods()
    {
        $methods = DeliveryMethod::where('is_active', true)->orderBy('cost')->get();

        return response()->json($methods);
    }
}
