<?php

namespace App\Http\Controllers;

use App\Models\clients;
use Illuminate\Http\Request;

class ClientController extends Controller
{
    public function index()
    {
        $clients = clients::latest()->get();
        return view('clients.index', compact('clients'));
    }

    public function create()
    {
        return view('clients.create');
    }

  public function store(Request $request)
{
    $data = $request->validate([
        'company_name' => 'required|string|max:255',
        'phone' => 'nullable|string|max:50',
        'address' => 'nullable|string',
        'status' => 'required|in:active,warning,critical',
    ]);

    $data['user_id'] = auth()->id();

    clients::create($data);

    return redirect()->route('clients.index')
        ->with('success', 'Client created successfully');
}
}
