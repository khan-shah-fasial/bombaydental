<?php

namespace App\Http\Controllers;

use App\Mail\ContactMailManager;
use App\Mail\ProductEnquiryMailManager;
use App\Models\Contact;
use App\Models\User;
use Illuminate\Http\Request;
use Mail;

class ContactController extends Controller
{
    public function __construct()
    {
        // Staff Permission Check
        $this->middleware(['permission:view_all_contacts'])->only('index');
        $this->middleware(['permission:reply_to_contact'])->only('reply_modal');
    }

    public function product_enquiry_index(Request $request)
    {
        $contacts = Contact::query();

        // Filter by contact type if provided
        if ($request->type != null) {
            $contacts->where('type', $request->type);
        }
        
        // Search by contact name, product name, or pincode
        if ($request->search != null) {
            $sort_search = $request->search;
            $contacts->where(function ($query) use ($sort_search) {
                $query->where('name', 'like', '%' . $sort_search . '%')
                      ->orWhere('email', 'like', '%' . $sort_search . '%')
                      ->orWhere('phone', 'like', '%' . $sort_search . '%');
            });
        }
        
        // Search by date range (using the created_at field)
        if ($request->date_from != null && $request->date_to != null) {
            $contacts->whereBetween('created_at', [$request->date_from, $request->date_to]);
        } elseif ($request->date_from != null) {
            $contacts->whereDate('created_at', '>=', $request->date_from);
        } elseif ($request->date_to != null) {
            $contacts->whereDate('created_at', '<=', $request->date_to);
        }
        
        // Default sorting by newest first
        $contacts = $contacts->orderBy('created_at', 'desc')->paginate(20);

        return view('backend.support.contact.product_enquiry', compact('contacts'));
    }

    public function index(Request $request)
    {
        $contacts = Contact::query();

        // Filter by contact type if provided
        if ($request->type != null) {
            $contacts->where('type', $request->type);
        }
        
        // Search by contact name, product name, or pincode
        if ($request->search != null) {
            $sort_search = $request->search;
            $contacts->where(function ($query) use ($sort_search) {
                $query->where('name', 'like', '%' . $sort_search . '%')
                      ->orWhere('email', 'like', '%' . $sort_search . '%')
                      ->orWhere('phone', 'like', '%' . $sort_search . '%')
                      ->orWhereHas('product', function ($q) use ($sort_search) {
                          $q->where('name', 'like', '%' . $sort_search . '%');
                      });
            });
        }
        
        // Search by date range (using the created_at field)
        if ($request->date_from != null && $request->date_to != null) {
            $contacts->whereBetween('created_at', [$request->date_from, $request->date_to]);
        } elseif ($request->date_from != null) {
            $contacts->whereDate('created_at', '>=', $request->date_from);
        } elseif ($request->date_to != null) {
            $contacts->whereDate('created_at', '<=', $request->date_to);
        }
        
        // Default sorting by newest first
        $contacts = $contacts->orderBy('created_at', 'desc')->paginate(20);

        return view('backend.support.contact.contacts', compact('contacts'));
    }

    public function query_modal(Request $request)
    {
        $contact = Contact::findOrFail($request->id);
        return view('backend.support.contact.query_modal', compact('contact'));
    }

    public function reply_modal(Request $request)
    {
        $contact = Contact::findOrFail($request->id);
        return view('backend.support.contact.reply_modal', compact('contact'));
    }

    public function reply(Request $request)
    {
        $contact = Contact::findOrFail($request->contact_id);
        $admin = get_admin();

        $array['name'] = $admin->name;
        $array['email'] = $admin->email;
        $array['phone'] = $admin->phone;
        $array['content'] = str_replace("\n", "<br>", $request->reply);
        $array['subject'] = translate('Query Contact Reply');
        $array['from'] = $admin->email;

        try {
            Mail::to($contact->email)->queue(new ContactMailManager($array));
            $contact->update([
                'reply' => $request->reply,
            ]);
        } catch (\Exception $e) {
            flash(translate('Something Went wrong'))->error();
            return back();
        }
        flash(translate('Reply has been sent successfully'))->success();
        return back();
    }

    public function contact(Request $request)
    {
        $admin = get_admin();

        $array['name'] = $request->name;
        $array['email'] = $request->email;
        $array['phone'] = $request->phone;
        $array['content'] = str_replace("\n", "<br>", $request->content);
        $array['subject'] = translate('Query Contact');
        $array['from'] = $request->email;

        try {
            Mail::to($admin->email)->queue(new ContactMailManager($array));
            Contact::insert([
                'type' => $request->type,
                'name' => $request->name,
                'email' => $request->email,
                'phone' => $request->phone,
                'content' => $request->content,
            ]);
        } catch (\Exception $e) {
            flash(translate('Something Went wrong'))->error();
            return back();
        }
        flash(translate('Query has been sent successfully'))->success();
        return back();
    }

    public function product_enquiry_store(Request $request)
    {

        $pincode_data = getLocationByPostalCode($request->pincode);
        $admin = get_admin();

        $array['name'] = $request->name;
        $array['email'] = $request->email;
        $array['phone'] = $request->phone;
        $array['pincode'] = $request->pincode;
        $array['url'] = $request->current_url;
        // $array['content'] = str_replace("\n", "<br>", $request->content);
        $array['subject'] = translate('Product Enquiry');
        $array['from'] = $request->email;

        try {
            //Mail::to($admin->email)->queue(new ProductEnquiryMailManager($array));
            Contact::insert([
                'type' => $request->type,
                'name' => $request->name,
                'email' => $request->email,
                'phone' => $request->phone,
                // 'content' => $request->content,
                'product_id' => $request->product_id,
                'pincode' => $request->pincode,
                'url' => $request->current_url,
                'pincode_data'=> json_encode($pincode_data, JSON_UNESCAPED_UNICODE),
            ]);
        } catch (\Exception $e) {
            flash(translate('Something Went wrong'))->error();
            return back();
        }
        flash(translate('Product Enquiry has been sent successfully'))->success();
        return back();
    }
}
