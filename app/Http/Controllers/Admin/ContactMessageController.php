<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ContactMessage;
use Illuminate\Http\Request;

class ContactMessageController extends Controller
{
    public function index()
    {
        $messages = ContactMessage::latest()->paginate(20);
        return view('admin.contact.index', compact('messages'));
    }

    public function show(ContactMessage $contact)
    {
        if (!$contact->is_read) {
            $contact->update(['is_read' => true]);
        }
        return view('admin.contact.show', ['contactMessage' => $contact]);
    }
    
    public function destroy(ContactMessage $contact)
    {
        $contact->delete();
        return redirect()->route('admin.contact.index')->with('success', 'Message deleted successfully.');
    }
}
