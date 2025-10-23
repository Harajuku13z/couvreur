<?php

namespace App\Http\Controllers;

use App\Models\SeoTemplate;
use Illuminate\Http\Request;

class SeoTemplateController extends Controller
{
    public function index()
    {
        $templates = SeoTemplate::orderByDesc('active')->orderBy('name')->get();
        return view('admin.seo.templates', compact('templates'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:100',
            'template_title' => 'required|string|max:160',
            'template_meta' => 'required|string|max:255',
            'content_blocks_json' => 'nullable',
            'config_json' => 'nullable',
            'active' => 'boolean',
        ]);

        if (isset($data['content_blocks_json']) && is_string($data['content_blocks_json'])) {
            $data['content_blocks_json'] = json_decode($data['content_blocks_json'], true);
        }
        if (isset($data['config_json']) && is_string($data['config_json'])) {
            $data['config_json'] = json_decode($data['config_json'], true);
        }

        SeoTemplate::create($data);
        return back()->with('success', 'Template enregistré');
    }
}





