<?php

namespace App\Http\Controllers;

use App\Helpers\QueryHelper;
use App\Models\DocumentTemplate;
use App\Models\DocumentTypes;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use Inertia\Inertia;

class KnowledgeBaseController extends Controller
{


    public function document_templates(Request $request)
    {
        $perPage = $request->ipp ?? 10;
        $search = $request->s ?? '';
        $offset = $request->p ?? 0;
    
        $query = DocumentTemplate::with('document_type')->withTrashed();
    
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'LIKE', "%{$search}%")
                  ->orWhere('description', 'LIKE', "%{$search}%");
            });
        }
    
        $total = $query->count();
        $items = $query->skip($offset)->take($perPage)->get();
    
        return Inertia::render('files/DocumentTemplates', [
            'items' => $items,
            'item_count' => $total,
            'baseFilePath' => asset('storage/templates/'),
            'presets' => [
                'document_types' => DocumentTypes::all()
            ]
        ]);
    }
    
    public function store_document_template(Request $request)
    {
        $request->validate([
            'title' => 'required|min:2|unique:document_templates',
            'document_type_id' => 'required|exists:document_types,id',
            'file' => 'required|file|max:10240',
            'file_name' => 'nullable|string|max:255',
            'file_description' => 'nullable|string'
        ]);
    
        $path = $request->file('file')->store('templates', 'public');
    
        $template = DocumentTemplate::create([
            'title' => $request->title,
            'description' => $request->description,
            'document_type_id' => $request->document_type_id,
        ]);
    
        $template->templates()->create([
            'file_name' => $request->file_name ?: $request->file('file')->getClientOriginalName(),
            'file_path' => $path,
            'description' => $request->file_description,
            'size' => $request->file('file')->getSize(),
            'extension' => $request->file('file')->extension()
        ]);
    
        return back()->with('success', 'Template created.');
    }
    
    public function update_document_template(Request $request, $id)
    {
        $template = DocumentTemplate::withTrashed()->findOrFail($id);
    
        $request->validate([
            'title' => 'required|min:2|unique:document_templates,title,' . $id,
            'document_type_id' => 'required|exists:document_types,id',
            'file_name' => 'nullable|string',
            'file_description' => 'nullable|string'
        ]);
    
        $template->update([
            'title' => $request->title,
            'description' => $request->description,
            'document_type_id' => $request->document_type_id,
        ]);
    
        if ($template->templates()->exists()) {
            $template->templates()->update([
                'file_name' => $request->file_name,
                'description' => $request->file_description
            ]);
        }
    
        return back()->with('success', 'Template updated.');
    }

    public function activate_document_template($id)
    {
        $item = DocumentTemplate::withTrashed()->find($id);
        if ($item) {
            $item->restore();
        }
        return response()->json([
            "error" => false,
            "message" => "Document template activated successfully",
        ]);
    }
    public function deactivate_document_template($id)
    {
        $item = DocumentTemplate::withTrashed()->find($id);
        if ($item) {
            $item->delete();
        }
        return response()->json([
            "error" => false,
            "message" => "Document template deactivated successfully",
        ]);
    }
    public function delete_document_template($id)
    {
        $item = DocumentTemplate::withTrashed()->find($id);
        if ($item) {
            $item->forceDelete();
        }
        return response()->json([
            "error" => false,
            "message" => "Document template deleted successfully",
        ]);
    }
}
